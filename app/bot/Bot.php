<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use Nette\Http\IRequest;
use Nette\Object;

class Bot extends Object
{
	const ID = 'USLACKBOT';

	/** @var IAction[] */
	private $actions;

	public function __construct($actions)
	{
		$this->actions = $actions;
	}

	public function run(IRequest $request, $type)
	{
		if($request->getPost('user_id') === self::ID) {
			die;
		}

		$command = strtolower($request->getPost('text'));
		if($type === NULL) { // with prefix
			$command = str_replace($request->getPost('trigger_word'), '', $command);
		}

		$data = array_values(array_filter(explode(' ', $command)));
		if($count = count($data)) {
			$command = new Command($data);
			$c = $command->getCommand();
			if($c === 'bot' || $c === 'dotBot' || $c === 'dotbot') {
				return NULL;
			}

			$chosenAction = NULL;
			$priority = -1;

			foreach($this->actions as $action) {
				if($action->match($command) && $action->getPriority() > $priority) {
					$chosenAction = $action;
				}
			}
			if($chosenAction) {
				return $chosenAction->run($command, $request);
			}
		}

		return 'Invalid command!';
	}
}
