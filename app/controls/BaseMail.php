<?php
/**
 * @author Tomáš Blatný
 */
namespace greeny\SlackBot\Controls;

abstract class BaseMail extends MailControl {
	protected $subject;

	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	public function getSubject()
	{
		return $this->subject;
	}
}
