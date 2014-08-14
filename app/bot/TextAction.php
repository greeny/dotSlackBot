<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use greeny\SlackBot\TextParser\TextParser;
use Nette\Http\IRequest;

class TextAction implements IAction
{
	/** @var \greeny\SlackBot\TextParser\TextParser */
	private $parser;

	public function __construct(TextParser $parser)
	{
		$this->parser = $parser;
	}

	/**
	 * @param Command $command
	 * @return bool
	 */
	function match(Command $command)
	{
		return TRUE;
	}

	/**
	 * @param Command  $command
	 * @param IRequest $request
	 * @param Bot      $bot
	 * @return string
	 */
	function run(Command $command, IRequest $request, Bot $bot)
	{
		return $this->parser->parse($command, $request, $bot);
	}

	/**
	 * @return int
	 */
	function getPriority()
	{
		return self::PRIORITY_NONE;
	}

	/**
	 * @return string
	 */
	function getName()
	{
		return "Text";
	}

	/**
	 * @return string
	 */
	function getDescription()
	{
		return "Write any text and see what does dotBot respond.";
	}
}
