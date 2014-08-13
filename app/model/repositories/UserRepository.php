<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot\Model;

use Nette\Utils\ArrayHash;
use Nette\Security\AuthenticationException;
use Nette\Utils\Strings;
use greeny\SlackBot\Security\PasswordHasher;

class UserRepository extends BaseRepository {

	public function findByNick($nick)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('nick = %s', $nick)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}

	public function register(ArrayHash $data)
	{
		if($this->findByNick($data->nick)) {
			throw new AuthenticationException("Uživatel '$data->nick' již existuje.");
		}

		/** @var User $user */
		$user = User::from($data);
		$user->salt = Strings::random(5, 'A-Za-z0-9');
		$user->password = PasswordHasher::hashPassword($user->nick, $user->password, $user->salt);
		$this->persist($user);
	}

}
