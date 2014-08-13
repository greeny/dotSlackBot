<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use Nette\Http\IRequest;

class HelpAction implements IAction
{
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
	 * @param Bot      $bot
	 * @return string
	 */
	function run(Command $command, IRequest $request, Bot $bot)
	{
		$return = '';
		foreach($bot->getActions() as $action) {
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
