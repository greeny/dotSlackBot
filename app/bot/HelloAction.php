<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use Nette\Http\IRequest;

class HelloAction implements IAction
{

	/**
	 * @param Command $command
	 * @return bool
	 */
	public function match(Command $command)
	{
		return $command->getCommand() === 'hello';
	}

	/**
	 * @param Command  $command
	 * @param IRequest $request
	 * @param Bot      $bot
	 * @return string
	 */
	public function run(Command $command, IRequest $request, Bot $bot)
	{
		return 'Hello @' . $request->getPost('user_name') . '!';
	}

	/**
	 * @return int
	 */
	public function getPriority()
	{
		return self::PRIORITY_NONE;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return "Hello";
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return "dotBot hello - greets dotBot";
	}
}
