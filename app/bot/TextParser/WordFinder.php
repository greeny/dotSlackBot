<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot\TextParser;

class WordFinder
{
	/**
	 * Finds words in string, words has to be in given order. Returns true if found, otherwise false
	 */
	public static function findWords($string)
	{
		$string = " $string ";
		$words = func_get_args();
		unset($words[0]);
		$start = 0;
		if(!count($words)) {
			return FALSE;
		}
		foreach($words as $word) {
			$pos = strpos($string, " $word ", $start);
			if($pos === FALSE) {
				return FALSE;
			} else {
				dump($start = $pos + strlen($word) + 1);
			}
		}
		return dump($start);
	}
}
