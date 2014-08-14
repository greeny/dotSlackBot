<?php

namespace greeny\SlackBot\PublicModule;

use greeny\Api\Core;
use Nette\Utils\Strings;

class DashboardPresenter extends BasePublicPresenter
{
	/** @var Core @inject */
	public $api;

	public function renderDefault()
	{
		$search = 'Tree';
		$wikiPage = $this->api->createUrlRequest("https://en.wikipedia.org/wiki/$search")->send();
		$matches = Strings::match($wikiPage, '~<p>(.*?)</p>~');
		if($text = $matches[1]) {
			if(strpos($text, 'may refer to:')) { // we need to find <ul>s in <div

			}
			$text = Strings::replace(strip_tags($text, '<a><b><i>'), '~<a.*?href="(.*?)".*?>(.*?)</a>~', function($text) {
				return !Strings::match($text[2], '~\[[0-9]+\]~') ? "<https://en.wikipedia.org{$text[1]}|{$text[2]}>" : '';
			});
			$text = Strings::replace($text, '~<b>(.*?)</b>~', function($text) {
				return '*' . $text[1] . '*';
			});
			$text = Strings::replace($text, '~<i>(.*?)</i>~', function($text) {
				return '_' . $text[1] . '_';
			});
			$var = $text . "\n" .
			str_repeat(' ', 40)."-- from <https://en.wikipedia.org/|Wikipedia, free encyclopedia> ( <https://en.wikipedia.org/wiki/$search|full article> )";
		}

		$this->template->dump = $var;
	}
}
