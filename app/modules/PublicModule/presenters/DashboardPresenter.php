<?php

namespace greeny\SlackBot\PublicModule;

use greeny\SlackBot\TextParser\TextParser;
use greeny\SlackBot\TextParser\WordFinder;
use Nette\Utils\Strings;

class DashboardPresenter extends BasePublicPresenter
{
	public function renderDefault()
	{
		$text = "what is tree?";
		$var = NULL;
		$pos = dump(WordFinder::findWords($text, 'what', 'is'));
		if($pos) {
			dump($pos);
			$search = str_replace(' ', '_', trim(Strings::replace(substr($text, $pos - 1), '~\s([a-z]{1,1})~', function($match) {
				return ' '.trim(strtoupper($match[0]));
			})));
			$var = rtrim($search, '.!?,');
		}
		$this->template->dump = $var;
	}
}
