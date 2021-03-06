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
		$text = str_replace(['&lt;', '&gt;'], ['<', '>'], $text);
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
			$search = trim(substr($text, $pos - 1));
			$before = trim(substr($text, 0, $pos));
			$limit = (strpos($before, 'full') !== FALSE) ? 50 : 5;
			if((strpos($search, $ch = '::') !== FALSE) || (strpos($search, $ch = '->') !== FALSE)) {
				$parts = explode($ch, $search, 2);
				$className = trim($this->fixSpaces($parts[0]));
				$className = ltrim(str_replace(array(' ', '*', '\\'), array('.*?', '.*?', '\\\\'), $className), '\\$');
				$searchMethod = trim(Strings::replace(strtolower(str_replace(' ', '', $parts[1])), '~\(.*?\).*?;?~', ''));
				$tree = $this->api->createUrlRequest('http://api.nette.org/2.2.2/index.html')->send();
				$start = strpos($tree, '<div id="elements">');
				$end = strpos(substr($tree, $start), '</div>');
				$elementsPage = substr($tree, $start, $end);
				$elements = Strings::matchAll($elementsPage, '~<li><a href="(.*?)">.*?' . $className . '(.*?)</a></li>~i');
				$directMatches = [];
				$otherMatches = [];
				$first = TRUE;
				foreach($elements as $element) {
					if(trim($element[2]) === '') {
						$directMatches[] = $element;
					} else {
						$otherMatches[] = $element;
					}
				}
				foreach($otherMatches as $match) {
					$directMatches[] = $match;
				}
				$return = "";
				foreach($directMatches as $match) {
					$class = Strings::match($match[0], '~">(.+?)</a>~')[1];
					if($first) {
						$classPage = $this->api->createUrlRequest("http://api.nette.org/2.2.2/{$match[1]}")->send();
						if(($start = strpos($classPage, '<table class="summary" id="methods">')) !== NULL) {
							$end = strpos(substr($classPage, $start), '</table>');
							$methodsPage = str_replace("\n", '', trim(substr($classPage, $start, $end)));
							$methods = Strings::matchAll($methodsPage, '~<td class="attributes">(.*?)</td>.*?<a href="(.*?)".*?>(.*?)</a>\((.*?)\).*?<div class="description detailed hidden">(.*?)</div></td>~');
						} else {
							$methods = array();
						}
						foreach($methods as $method) {
							$methodName = $this->fixSpaces(trim(strip_tags($method[3])));
							if(strtolower($methodName) === $searchMethod) {
								$first = FALSE;
								$returnValue = $this->fixSpaces(trim(strip_tags($method[1])));
								$href = $this->fixSpaces(trim(strip_tags($method[2])));
								$methodParams = $this->fixSpaces(trim(strip_tags($method[4])));
								$returns = Strings::match($method[5], '~<h4>Returns</h4>.*?<div class="list">(.*?)</div>~');
								if($returns) {
									$returns = "*Returns*\n    " . trim($this->findAndReplaceLinks(strip_tags($returns[1], '<a>'), 'http://api.nette.org/2.2.2/'));
								} else {
									$returns = "*No return value*";
								}

								$params = Strings::match($method[5], '~<h4>Parameters</h4>.*?<dl>(.*?)</dl>~');
								if($params) {
									$paramsText = "*Parameters*";
									foreach(Strings::matchAll($params[1], '~<var>(.*?)</var>.*?<dd><code>(.*?)</code>(.*?)</dd>~') as $p) {
										$paramsText .= "\n    _" . $this->findAndReplaceLinks(strip_tags($p[2], '<a>'), 'http://api.nette.org/2.2.2/') . "_ $p[1]" . (trim(strip_tags($p[3])) !== '' ? " - ".strip_tags($p[3]) : '');
									}
								} else {
									$paramsText = "*No parameters*";
								}

								$other = '';

								$overrides = Strings::match($method[5], '~<h4>Overrides</h4>.*?<div class="list">(.*?)</div>~');
								if($overrides) {
									$other .= "\n*Overrides*\n    " . trim($this->findAndReplaceLinks(strip_tags($overrides[1], '<a>'), 'http://api.nette.org/2.2.2/'));
								}

								$implements = Strings::match($method[5], '~<h4>Implementation of</h4>.*?<div class="list">(.*?)</div>~');
								if($implements) {
									$other .= "\n*Implementation of*\n    " . trim($this->findAndReplaceLinks(strip_tags($implements[1], '<a>'), 'http://api.nette.org/2.2.2/'));
								}

								$return .= "_{$returnValue}_ <http://api.nette.org/2.2.2/{$match[1]}|$class>::";
								$return .= "<http://api.nette.org/2.2.2/$href|$methodName> ($methodParams)\n$returns\n$paramsText$other";
							}
						}
					}
				}
				if(trim($return) === '') {
					$return = 'Sorry, I couldn\'t find ' . $className . '::' . $searchMethod .'() in Nette 2.2.2 API. Try searching <http://api.nette.org/2.2.2/index.html|Nette API> yourself.';
				}
				return $return;
			} else if((strpos($search, $ch = '\\') !== FALSE) || (strpos($before, 'nette') !== FALSE)) {
				$search = ltrim(str_replace(array(' ', '*', '\\'), array('.*?', '.*?', '\\\\'), $this->fixSpaces($search)), '\\');
				$tree = $this->api->createUrlRequest('http://api.nette.org/2.2.2/index.html')->send();
				$start = strpos($tree, '<div id="elements">');
				$end = strpos(substr($tree, $start), '</div>');
				$elementsPage = substr($tree, $start, $end);
				$elements = Strings::matchAll($elementsPage, '~<li><a href="(.*?)">.*?' . $search . '(.*?)</a></li>~i');
				$directMatches = [];
				$otherMatches = [];
				$first = TRUE;
				foreach($elements as $element) {
					if(trim($element[2]) === '') {
						$directMatches[] = $element;
					} else {
						$otherMatches[] = $element;
					}
				}
				foreach($otherMatches as $match) {
					$directMatches[] = $match;
				}
				$return = "";
				$counter = 0;
				foreach($directMatches as $match) {
					$class = Strings::match($match[0], '~">(.+?)</a>~')[1];
					if($first) {
						$first = FALSE;
						$classPage = $this->api->createUrlRequest("http://api.nette.org/2.2.2/{$match[1]}")->send();
						if(($start = strpos($classPage, '<table class="summary" id="methods">')) !== NULL) {
							$end = strpos(substr($classPage, $start), '</table>');
							$methodsPage = str_replace("\n", '', trim(substr($classPage, $start, $end)));
							$methods = Strings::matchAll($methodsPage, '~<td class="attributes">(.*?)</td>.*?<a href="(.*?)".*?>(.*?)</a>\((.*?)\).*?<div class="description short">(.*?)</div>~');
						} else {
							$methods = array();
						}
						$return .= "<http://api.nette.org/2.2.2/{$match[1]}|$class>:";
						$counter = 0;
						foreach($methods as $method) {
							if($counter++ >= $limit) break;
							$returnValue = $this->fixSpaces(trim(strip_tags($method[1])));
							$href = $this->fixSpaces(trim(strip_tags($method[2])));
							$methodName = $this->fixSpaces(trim(strip_tags($method[3])));
							$methodParams = $this->fixSpaces(trim(strip_tags($method[4])));
							$methodDescription = $this->fixSpaces(trim(strip_tags($method[5])));
							$return .= "\n    _{$returnValue}_ <http://api.nette.org/2.2.2/$href|$methodName> ($methodParams) - $methodDescription";
						}
						if($counter < count($methods)) $return .= "\n    ...";
						$counter = 1;
						if(count($directMatches) > 1) $return .= "\n\n*You might also look for:*";
					} else {
						if($counter++ >= $limit) break;
						$return .= "\n - <http://api.nette.org/2.2.2/{$match[1]}|$class>";
					}
				}
				if($counter < count($directMatches)) $return .= "\n ...";
				if(trim($return) === '') {
					$return = 'Sorry, I couldn\'t find ' . $search . ' in Nette 2.2.2 API. Try searching <http://api.nette.org/2.2.2/index.html|Nette API> yourself.';
				}
				return $return;
			} else {
				$search = str_replace(' ', '_', $search);
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
					$return = "<http://cz2.php.net/$method|$method> _{$version}_\n$description.\n\n*$signature*";
					foreach($params as $param) {
						$return .= "\n    _\${$param[1]}_ - " . Strings::truncate(strip_tags(trim($param[2])), 50);
					}
					return $return;
				}
			}
		} else if(($pos = WordFinder::findWords($text, 'you', 'are', 'awesome')) || ($pos = WordFinder::findWords($text, 'you', 'are', 'great')) ||
			($pos = WordFinder::findWords($text, 'you', 'are', 'cool')) || ($pos = WordFinder::findWords($text, 'you', 'are', 'good')) ||
			($pos = WordFinder::findWords($text, 'you', 'are', 'smart')) || ($pos = WordFinder::findWords($text, 'you', 'are', 'clever'))) {
			return 'Thank you, good to hear!';
		} else if(($pos = WordFinder::findWords($text, 'thank', 'you')) || ($pos = WordFinder::findWords($text, 'thanks')) ||
			($pos = WordFinder::findWords($text, 'thx')) || ($pos = WordFinder::findWords($text, 'ty'))) {
			return "No problem, I am glad i could help!";
		} else if($text === 'cool' || $text === 'nice' || $text === 'great') {
			return "I did my best.";
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

	private function fixSpaces($text)
	{
		return Strings::replace($text, '~[\s]+~', ' ');
	}
}
