<?php

namespace greeny\SlackBot\PublicModule;

use greeny\SlackBot\TextParser\TextParser;
use greeny\SlackBot\TextParser\WordFinder;
use Nette\Utils\Strings;

class DashboardPresenter extends BasePublicPresenter
{
	public function renderDefault()
	{
		$text = '<a href="/wiki/blah" title="blah">Text</a>';
		$var = Strings::replace($text, '~<a.*?href="(.*?)".*?>(.*?)</a>~', function($text) {dump($text);
			return "<{$text[1]}|{$text[2]}>";
		});
		$this->template->dump = $var;
	}
}
