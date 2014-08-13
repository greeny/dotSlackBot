<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot\Security;

use Nette\Security\Permission;

class Authorizator extends Permission {


	public function __construct()
	{
		$this->addRole('guest');
		$this->addRole('member', 'guest');
		$this->addRole('admin', 'member');
		$this->addRole('owner', 'admin');

		$this->addResource('post');
		$this->addResource('user');
		$this->addResource('comment');

		$this->allow('guest', 'post', array('view'));
		$this->allow('member', 'post', array('compose', 'edit', 'delete'));
		$this->allow('admin', 'post', array('f-edit', 'f-delete'));

		$this->allow('owner');
	}
}
