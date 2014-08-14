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
		$search = 'how+to+convert+integer+to+string+php';
		Debugger::$maxLen = 1e6;
		$soPage = $this->api->createUrlRequest("http://stackoverflow.com/search?q=$search")->send();
		$matches = Strings::matchAll($soPage, '~<a.href="([^"]*?)".title="(.*?)".*?>~');
		$return = 'Try one of these topics:';
		$i = -1;
		foreach($matches as $match) {
			$i++;
			if($i <= 2) continue;
			if($i >= 8) break;
			$return .= "\n - <http://stackoverflow.com$match[1]|$match[2]>";
		}
		dump($var = $return);
		dump($soPage);

		$this->template->dump = $var;
	}

	private function findAndReplaceLinks($text, $baseLinkHref = '')
	{
		return Strings::replace($text, '~<a.*?href="([^"]*?)".*?>(.*?)</a>~', function($text) use($baseLinkHref) {
			return !Strings::match($text[2], '~\[[0-9]+\]~') ? "<$baseLinkHref{$text[1]}|{$text[2]}>" : '';
		});
	}
}
