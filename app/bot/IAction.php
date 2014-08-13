<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use Nette\Http\IRequest;

interface IAction
{
	const PRIORITY_NONE = 0;
	const PRIORITY_LOW = 1;
	const PRIORITY_MEDIUM = 2;
	const PRIORITY_HIGH = 3;
	const PRIORITY_URGENT = 4;

	/**
	 * @param Command $command
	 * @return bool
	 */
	function match(Command $command);

	/**
	 * @param Command  $command
	 * @param IRequest $request
	 * @param Bot      $bot
	 * @return string
	 */
	function run(Command $command, IRequest $request, Bot $bot);

	/**
	 * @return int
	 */
	function getPriority();

	/**
	 * @return string
	 */
	function getName();

	/**
	 * @return string
	 */
	function getDescription();
} 
