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
		if($text = $this->bot->run($this->getHttpRequest(), $type)) {
			$this->sendResponse(new JsonResponse([
				'text' => $text,
				'parse' => 'full',
			]));
		} else {
			die;
		}
	}
}
