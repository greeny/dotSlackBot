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
		$search = 'Tree';Debugger::$maxLen = 1e6;
		//$wikiPage = $this->api->createUrlRequest("https://en.wikipedia.org/wiki/$search")->send();
		$wikiPage = '<p><b>PHP</b> is a <a href="/wiki/Server-side_scripting" title="Server-side scripting">server-side scripting</a> language designed for <a href="/wiki/Web_development" title="Web development">web development</a> but also used as a <a href="/wiki/General-purpose_programming_language" title="General-purpose programming language">general-purpose programming language</a>. As of January 2013<sup class="plainlinks noprint asof-tag update" style="display:none;"><a class="external text" href="//en.wikipedia.org/w/index.php?title=PHP&amp;action=edit">[update]</a></sup>, PHP was installed on more than 240 million <a href="/wiki/Website" title="Website">websites</a> (39% of those sampled) and 2.1 million <a href="/wiki/Web_server" title="Web server">web servers</a>.<sup id="cite_ref-4" class="reference"><a href="#cite_note-4"><span>[</span>4<span>]</span></a></sup> Originally created by <a href="/wiki/Rasmus_Lerdorf" title="Rasmus Lerdorf">Rasmus Lerdorf</a> in 1994,<sup id="cite_ref-History_of_PHP_5-0" class="reference"><a href="#cite_note-History_of_PHP-5"><span>[</span>5<span>]</span></a></sup> the <a href="/wiki/Reference_implementation" title="Reference implementation">reference implementation</a> of PHP (powered by the <a href="/wiki/Zend_Engine" title="Zend Engine">Zend Engine</a>) is now produced by The PHP Group.<sup id="cite_ref-about_PHP_6-0" class="reference"><a href="#cite_note-about_PHP-6"><span>[</span>6<span>]</span></a></sup> While PHP originally stood for <i>Personal Home Page</i>,<sup id="cite_ref-History_of_PHP_5-1" class="reference"><a href="#cite_note-History_of_PHP-5"><span>[</span>5<span>]</span></a></sup> it now stands for <i>PHP: Hypertext Preprocessor</i>, which is a <a href="/wiki/Recursive_acronym" title="Recursive acronym">recursive acronym</a>.<sup id="cite_ref-7" class="reference"><a href="#cite_note-7"><span>[</span>7<span>]</span></a></sup></p>';
		$matches = Strings::match($wikiPage, '~<p>(.*?)</p>~');
		if($text = $matches[1]) {
			if(strpos($text, 'may refer to:')) { // we need to find <ul>s in <div

			}
			$text = Strings::replace(strip_tags($text, '<a><b><i>'), '~<sup.*?>(.*?)</sup>~', function($text) {
				return '';
			});
			$text = Strings::replace($text, '~<a.*?href="(.*?)".*?>(.*?)</a>~', function($text) {
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
