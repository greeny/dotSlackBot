<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use Nette\Http\IRequest;

class HelpAction implements IAction
{
	/** @var \greeny\SlackBot\Bot */
	private $bot;

	public function __construct(Bot $bot)
	{
		$this->bot = $bot;
	}

	/**
	 * @param Command $command
	 * @return bool
	 */
	function match(Command $command)
	{
		return $command->getCommand() === 'help';
	}

	/**
	 * @param Command  $command
	 * @param IRequest $request
	 * @return string
	 */
	function run(Command $command, IRequest $request)
	{
		$return = '';
		foreach($this->bot->getActions() as $action) {
			$return .= '*' . $action->getName() . '*\n' . $action->getDescription() . '\n-----\n';
		}
		return $return;
	}

	/**
	 * @return int
	 */
	function getPriority()
	{
		return self::PRIORITY_URGENT;
	}

	/**
	 * @return string
	 */
	function getName()
	{
		return "Help";
	}

	/**
	 * @return string
	 */
	function getDescription()
	{
		return "dotBot help - shows help for each command";
	}
}
