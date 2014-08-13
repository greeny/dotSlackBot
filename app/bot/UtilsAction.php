<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use Nette\Http\IRequest;

class UtilsAction implements IAction
{

	/**
	 * @param Command $command
	 * @return bool
	 */
	function match(Command $command)
	{
		return in_array($command->getCommand(), [
			'time', 'date'
		]);
	}

	/**
	 * @param Command  $command
	 * @param IRequest $request
	 * @param Bot      $bot
	 * @return string
	 */
	function run(Command $command, IRequest $request, Bot $bot)
	{
		$date = $command->getCommand() === 'date';
		return 'Current ' . ($date ? 'date' : 'time') . ' is ' . date($date ? 'j.n.Y' : 'G:i:s');
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
		return 'Utils';
	}

	/**
	 * @return string
	 */
	function getDescription()
	{
		return '_dotBot time_ - prints current time
	_dotBot date_ - prints current date';
	}
}
