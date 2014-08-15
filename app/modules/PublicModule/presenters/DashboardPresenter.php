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
		Debugger::$maxLen = 1e6;
		$search = '$presenter->run($a, $b)';
		$parts = array();
		if((strpos($search, $ch = '::') !== FALSE) || (strpos($search, $ch = '->') !== FALSE)) {
			$parts = explode($ch, $search, 2);
		}
		//
		$className = trim($this->fixSpaces($parts[0]));
		$className = ltrim(str_replace(array(' ', '*', '\\'), array('.*?', '.*?', '\\\\'), $className), '\\$');
		$searchMethod = trim(Strings::replace(strtolower(str_replace(' ', '', $parts[1])), '~\(.*?\)~', ''));
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
		$var = $return;

		$this->template->dump = $var;
	}

	private function findAndReplaceLinks($text, $baseLinkHref = '')
	{
		return Strings::replace($text, '~<a.*?href="([^"]*?)".*?>(.*?)</a>~', function($text) use($baseLinkHref) {
			return !Strings::match($text[2], '~\[[0-9]+\]~') ? "<$baseLinkHref{$text[1]}|{$text[2]}>" : '';
		});
	}

	private function fixSpaces($text) {
		return Strings::replace($text, '~[\s]+~', ' ');
	}
}
