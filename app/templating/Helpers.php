<?php
/**
 * @author Tomáš Blatný
 */
namespace Sandbox\Templating;

use Nette\Bridges\ApplicationLatte\Template;
use Nette\Object;
use Nette\Utils\Html;

class Helpers extends Object {
	public static function prepareTemplate(Template $template)
	{
		$texy = new \Texy();
		$latte = $template->getLatte();
		$latte->addFilter('texy', function($text) use($texy) {
			return Html::el('')->setHtml($texy->process($text));
		});

		$latte->addFilter('time', function($text) {
			return date('j.n.Y G:i:s', $text);
		});
	}
}