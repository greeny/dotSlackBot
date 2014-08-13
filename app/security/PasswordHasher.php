<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot\Security;

class PasswordHasher {
	public static function hashPassword($nick, $password, $salt)
	{
		return self::multiHash($nick . '@' . $password, 5, $salt);
	}

	protected static function multiHash($string, $count = 1, $salt = '', $method = 'sha512')
	{
		$string = self::hash($string, $salt, $method);
		return $count > 1 ? self::multiHash($string, --$count, $salt, $method) : $string;
	}

	protected static function hash($string, $salt = '', $method = 'sha512')
	{
		return hash($method, $salt . '$' . $string . '$' . $salt);
	}
}
