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
				return Strings::replace($text, '~<a.*?>(.*?)</a>~', function($text) {
					return $text[1];
				});
			} else {
				return "I don't know, try https://en.wikipedia.org/wiki/$search.";
			}
		}
		return NULL;
	}
}
