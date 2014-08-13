<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot;

use Nette\Http\IRequest;
use Nette\Object;

class Bot extends Object
{
	public function __construct(/*$commands*/)
	{

	}

	public function run(IRequest $request, $type)
	{
		return serialize($request->getPost());//'dotSlackBot is working!';
	}
}
