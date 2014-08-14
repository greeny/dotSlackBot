<?php

namespace greeny\SlackBot\PublicModule;

use greeny\SlackBot\TextParser\TextParser;
use greeny\SlackBot\TextParser\WordFinder;
use Nette\Utils\Strings;

class DashboardPresenter extends BasePublicPresenter
{
	public function renderDefault()
	{
		$text = '<a href="link">Text</a>';
		$var = Strings::replace($text, '~<a.*?(href="(.*?)")>(.*?)</a>~', function($text) {dump($text);
			return "<{$text[2]}|{$text[3]}>";
		});
		$this->template->dump = $var;
	}
}
