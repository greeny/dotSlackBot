<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot\TextParser;

use greeny\Api\Core;
use greeny\SlackBot\Bot;
use greeny\SlackBot\Command;
use Latte\Object;
use Nette\Http\Request;
use Nette\Utils\Strings;

class TextParser extends Object
{
	/** @var string|NULL */
	private $userName = NULL;

	/** @var Request|NULL */
	private $request = NULL;

	/** @var Bot|NULL */
	private $bot = NULL;

	/** @var \greeny\Api\Core */
	private $api;

	public function __construct(Core $core)
	{
		$this->api = $core;
	}

	public function parse(Command $command, Request $request, Bot $bot)
	{
		$this->userName = '@' . $request->getPost('user_name');
		$this->request = $request;
		$this->bot = $bot;
		$return = $this->parseText(strtolower($command->getRaw()));
		if($return === NULL) {
			$return = "Sorry, $this->userName, I don't understand you. Try different question please.";
		}
		$this->bot = NULL;
		$this->request = NULL;
		$this->userName = NULL;
		return $return;
	}

	private function parseText($text)
	{
		$pos = NULL;
		if(($pos = WordFinder::findWords($text, 'what', 'is')) || ($pos = WordFinder::findWords($text, 'what', 'could', 'be'))) {
			$search = str_replace(' ', '_', trim(Strings::replace(substr($text, $pos - 1), '~\s([a-z]{1,1})~', function($match) {
				return ' '.trim(strtoupper($match[0]));
			})));
			$search = rtrim($search, '.!?,');
			
			$wikiPage = $this->api->createUrlRequest("https://en.wikipedia.org/wiki/$search")->send();
			$matches = Strings::match($wikiPage, '~<p>(.*?)</p>~');
			if($text = $matches[1]) {
				if(strpos($text, 'may refer to:')) { // we need to find <ul>s in <div

				}
				$text = Strings::replace($text, '~<sup.*?>(.*?)</sup>~', function() {
					return '';
				});
				$text = $this->formatResult($text, 'https://en.wikipedia.org');
				return $text . "\n" .
					str_repeat(' ', 40)."-- from <https://en.wikipedia.org/|Wikipedia, free encyclopedia> ( <https://en.wikipedia.org/wiki/$search|full article> )";
			} else {
				return "I don't know, try <http://lmgtfy.com/?q=$search|this bot>.";
			}
		} else if(($pos = WordFinder::findWords($text, 'how', 'to')) || ($pos = WordFinder::findWords($text, 'how', 'can', 'i')) || ($pos = WordFinder::findWords($text, 'how', 'can'))) {
			$search = str_replace(' ', '+', trim(substr($text, $pos - 1)));
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
			return $return;
		} else if(($pos = WordFinder::findWords($text, 'show', 'docs', 'for')) || ($pos = WordFinder::findWords($text, 'show', 'doc', 'for')) || ($pos = WordFinder::findWords($text, 'show', 'documentation', 'for')) ||
			($pos = WordFinder::findWords($text, 'show', 'docs')) || ($pos = WordFinder::findWords($text, 'show', 'doc')) || ($pos = WordFinder::findWords($text, 'show', 'documentation'))) {
			$search = str_replace(' ', '_', trim(substr($text, $pos - 1)));
			if((strpos($search, $ch = '::') !== FALSE) || (strpos($search, $ch = '->') !== FALSE)) {
				// find in Nette documentation
			} else {
				$phpPage = $this->api->createUrlRequest("http://cz2.php.net/$search")->send();
				if($phpPage === '') {
					return 'Sorry, I couldn\'t find such a function. Try searching <http://php.net/search.php?pattern=' . $search . '|php.net> yourself.';
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
					return $return;
				}
			}
		}
		return NULL;
	}

	private function formatResult($text, $baseLinkHref = '')
	{
		$text = strip_tags($text, '<a><b><i>');
		$text = $this->findAndReplaceLinks($text, $baseLinkHref);
		$text = Strings::replace($text, '~<b>(.*?)</b>~', function($text) {
			return '*' . $text[1] . '*';
		});
		$text = Strings::replace($text, '~<i>(.*?)</i>~', function($text) {
			return '_' . $text[1] . '_';
		});
		$text = Strings::replace($text, '~\[[0-9]\]~', function($text) {
			return '_' . $text[1] . '_';
		});
		return $text;
	}

	private function findAndReplaceLinks($text, $baseLinkHref = '')
	{
		return Strings::replace($text, '~<a.*?href="(.*?)".*?>(.*?)</a>~', function($text) use($baseLinkHref) {
			return !Strings::match($text[2], '~\[[0-9]+\]~') ? "<$baseLinkHref{$text[1]}|{$text[2]}>" : '';
		});
	}
}
