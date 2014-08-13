<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot\ApiModule;

use greeny\SlackBot\BasePresenter;
use greeny\SlackBot\Bot;
use Nette\Application\Responses\JsonResponse;


class HookPresenter extends BasePresenter
{
	/** @var Bot @inject */
	public $bot;

	public function actionDefault($type = NULL)
	{
		$data = [
			'text' => $this->bot->run($this->getHttpRequest(), $type),
			'parse' => 'full',
		];
		$this->sendResponse(new JsonResponse($data));
	}
}
