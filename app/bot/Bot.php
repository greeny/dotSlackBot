<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Object;

class Bot extends Object
{
	const ID = 'USLACKBOT';

	/** @var IAction[] */
	private $actions = [];

	public function __construct(Container $container)
	{
		foreach($container->findByType('greeny\SlackBot\IAction') as $string) {
			$this->actions[] = $container->getService($string);
		}
	}

	/**
	 * @return IAction[]
	 */
	public function getActions()
	{
		return $this->actions;
	}

	/**
	 * @param IRequest $request
	 * @param string   $type
	 * @return null|string
	 */
	public function run(IRequest $request, $type)
	{
		if($request->getPost('user_id') === self::ID) {
			die;
		}

		$command = new Command($request->getPost('text'), $type, $request->getPost('trigger_word'));
		$c = $command->getCommand();
		if($c === 'bot' || $c === 'dotBot' || $c === 'dotbot') {
			return NULL;
		}

		$chosenAction = NULL;
		$priority = -1;

		foreach($this->getActions() as $action) {
			if(($action->match($command)) && ($action->getPriority() > $priority)) {
				$chosenAction = $action;
			}
		}
		if($chosenAction) {
			return $chosenAction->run($command, $request, $this);
		}

		return NULL;
	}
}
