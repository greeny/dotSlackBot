<?php

namespace greeny\SlackBot\PublicModule;

use greeny\Api\Core;
use Nette\Utils\Strings;
use Tracy\Debugger;

class DashboardPresenter extends BasePublicPresenter
{
	/** @var Core @inject */
	public $api;

	public function renderDefault()
	{
		Debugger::$maxLen = 1e6;
		$search = 'htmlspecialchars_decode';
		$phpPage = $this->api->createUrlRequest("http://cz2.php.net/$search")->send();
		if($phpPage === '') {
			$var = 'Not found';
		} else {
			$method = Strings::match($phpPage, '~<h1 class="refname">(.*?)</h1>~')[1];
			$version = Strings::match($phpPage, '~<p class="verinfo">(.*?)</p>~')[1];
			$start = strpos($phpPage, '<p class="refpurpose">');
			$end = strpos(substr($phpPage, $start), '</p>');
			$description = trim(strip_tags(substr($phpPage, $start, $end)));
			$description = trim(substr($description, strpos($description, '&mdash;') + strlen('&mdash;')));
			$start = strpos($phpPage, '<div class="methodsynopsis dc-description">');
			$end = strpos(substr($phpPage, $start), '</div>');
			$signature = trim(strip_tags(substr($phpPage, $start, $end)));
			$signature = str_replace("\n", '', $signature);
			$start = strpos($phpPage, '<div class="refsect1 parameters"');
			$end = strpos(substr($phpPage, $start), '</div>') + 6;
			$doc = str_replace("\n", '', substr($phpPage, $start, $end));
			$params = Strings::matchAll($doc, '~<dt>.*?<code class="parameter">(.*?)</code>.*?</dt>.*?<dd>.*?<p class="para">(.*?)</p>.*?</dd>~');
			$return = "*$method* _{$version}_\n$description.\n\n*$signature*";
			foreach($params as $param) {
				$return .= "\n    _\${$param[1]}_ - " . trim($param[2]);
			}
			$var = $return;
		}

		$this->template->dump = $var;
	}

	private function findAndReplaceLinks($text, $baseLinkHref = '')
	{
		return Strings::replace($text, '~<a.*?href="([^"]*?)".*?>(.*?)</a>~', function($text) use($baseLinkHref) {
			return !Strings::match($text[2], '~\[[0-9]+\]~') ? "<$baseLinkHref{$text[1]}|{$text[2]}>" : '';
		});
	}
}
