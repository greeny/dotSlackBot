<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use greeny\SlackBot\Github\Github;
use Nette\Http\IRequest;
use Nette\Utils\Strings;

class GithubAction implements IAction
{
	/** @var \greeny\SlackBot\Github\Github */
	private $github;

	public function __construct(Github $github)
	{
		$this->github = $github;
	}

	/**
	 * @param Command $command
	 * @return bool
	 */
	function match(Command $command)
	{
		return $command->getCommand() === 'github';
	}

	/**
	 * @param Command  $command
	 * @param IRequest $request
	 * @param Bot      $bot
	 * @return string
	 */
	function run(Command $command, IRequest $request, Bot $bot)
	{
		return serialize($command->getArgs());
		if($command->getArg(0) === 'last') {
			if($command->getArg(1) === 'commit') {
				$commit = $this->github->getLastCommit();
				return '*dotblue/booklidays*: <' . $commit->commit->html_url . '|' . Strings::truncate($commit->commit->message, 50) . '> by <' .
					$commit->commiter->html_url . '|' . $commit->commiter->login . '>';
			}
		}
		return NULL;
	}

	/**
	 * @return int
	 */
	function getPriority()
	{
		return self::PRIORITY_HIGH;
	}

	/**
	 * @return string
	 */
	function getName()
	{
		return "GitHub";
	}

	/**
	 * @return string
	 */
	function getDescription()
	{
		return "TODO";
	}
}
