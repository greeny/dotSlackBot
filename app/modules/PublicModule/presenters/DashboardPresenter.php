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
		$search = 'request';

		$search = ltrim(str_replace(array(' ', '*', '\\'), array('.*?', '.*?', '\\\\'), $search), '\\');
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
					if($counter++ >= 5) break;
					$returnValue = $this->fixSpaces(trim(strip_tags($method[1])));
					$href = $this->fixSpaces(trim(strip_tags($method[2])));
					$methodName = $this->fixSpaces(trim(strip_tags($method[3])));
					$methodParams = $this->fixSpaces(trim(strip_tags($method[4])));
					$methodDescription = $this->fixSpaces(trim(strip_tags($method[5])));
					$return .= "\n    _{$returnValue}_ <http://api.nette.org/2.2.2/$href|$methodName> ($methodParams) - $methodDescription";
				}
				$counter = 0;
				$return .= "\n    ...\n\n*You might also look for:*";
			} else {
				if($counter++ >= 5) break;
				$return .= "\n - <{$match[1]}|$class>";
			}
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
