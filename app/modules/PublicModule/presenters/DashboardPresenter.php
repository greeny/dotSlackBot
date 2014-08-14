<?php

namespace greeny\SlackBot\PublicModule;

use greeny\SlackBot\TextParser\TextParser;
use greeny\SlackBot\TextParser\WordFinder;

class DashboardPresenter extends BasePublicPresenter
{
	public function renderDefault()
	{
		$str = "what is van canto";
		$var = WordFinder::findWords($str, 'what', 'is');
		$var = substr($str, $var);
		//$var = TextParser::parseText($str);
		$this->template->dump = $var;
	}
}
