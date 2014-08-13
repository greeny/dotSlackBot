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

	public function __construct(/*$commands*/)
	{

	}

	public function run(IRequest $request, $type)
	{
		if($request->getPost('user_id') === self::ID) {
			die;
		}
		return serialize($request->getPost());//'dotSlackBot is working!';
	}
}
