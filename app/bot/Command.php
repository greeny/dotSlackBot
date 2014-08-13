<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use Nette\Object;

class Command extends Object
{
	private $params = [];

	private $switches = [];

	private $args = [];

	private $command;

	public function __construct(array $data)
	{
		$this->command = $data[0];
		unset($data[0]);
		$data = array_values($data);
		$paramKey = NULL;
		foreach($data as $string) {
			if(substr($string, 0, 2) === '--') {
				$paramKey = substr($string, 2);
			} else if(substr($string, 0, 1) === '-') {
				foreach(str_split(substr($string, 1)) as $char) {
					$this->switches[$char] = TRUE;
				}
				if($paramKey) {
					$this->params[$paramKey] = TRUE;
				}
			} else if($paramKey) {
				$this->params[$paramKey] = $string;
			} else {
				$this->args[] = $string;
			}
		}
	}

	public function getParams()
	{
		return $this->params;
	}

	public function getParam($name, $default = NULL)
	{
		return isset($this->params[$name]) ? $this->params[$name] : $default;
	}

	public function getSwitches()
	{
		return $this->switches;
	}

	public function hasSwitch($s)
	{
		return isset($this->switches[$s]);
	}

	public function getArgs()
	{
		return $this->args;
	}

	public function getArg($order, $default = NULL)
	{
		return isset($this->args[$order]) ? $this->args[$order] : $default;
	}

	public function getCommand()
	{
		return $this->command;
	}
}
