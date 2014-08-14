<?php

namespace greeny\SlackBot\PublicModule;

use greeny\SlackBot\TextParser\TextParser;
use greeny\SlackBot\TextParser\WordFinder;
use Nette\Utils\Strings;

class DashboardPresenter extends BasePublicPresenter
{
	public function renderDefault()
	{
		$wikiPage = "<html><head></head><body><p>First</p><p>Second</p></body></html>";
		$var = Strings::match($wikiPage, '~<p>(.*?)</p>~');
		$this->template->dump = $var;
	}
}
