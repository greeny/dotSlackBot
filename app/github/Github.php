<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot\Github;

use greeny\Api\Core;
use greeny\Api\Drivers\IDriver;
use greeny\Api\Request;

class Github extends Core
{
	private $accessToken;

	public function __construct($accessToken, IDriver $driver)
	{
		parent::__construct($driver);
		$this->accessToken = $accessToken;
		$this->setBasePath('https://api.github.com')
			->setDefaultHeaders([
				'Accept' => 'application/vnd.github.v3+json',
				'User-Agent' => 'greeny',
			])
			->setDefaultParameters([
				'access_token' => $accessToken,
			]);
	}

	public function getLastCommit()
	{
		return json_decode($this->createRequest('/repos/dotblue/booklidays/commits')
			->setMethod(Request::METHOD_GET)
			->addParameters('per_page', 1)
			->send())[0];
	}
}
