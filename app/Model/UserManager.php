<?php declare(strict_types = 1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;
use Nette\SmartObject;
use Nette\Utils\Validators;

/**
 * Users management.
 */
final class UserManager implements Authenticator
{

	use SmartObject;

	private const TABLE_NAME = 'users';
	private const COLUMN_ID = 'id';
	private const COLUMN_NAME = 'username';
	private const COLUMN_PASSWORD_HASH = 'password';
	private const COLUMN_EMAIL = 'email';
	private const COLUMN_ROLE = 'role';

	private Explorer $database;

	private Passwords $passwords;

	public function __construct(Explorer $database, Passwords $passwords)
	{
		$this->database = $database;
		$this->passwords = $passwords;
	}

	/**
	 * Performs an authentication.
	 *
	 * @throws AuthenticationException
	 */
	public function authenticate(string $username, string $password): IIdentity
	{
		$row = $this->database->table(self::TABLE_NAME)
			->where(self::COLUMN_NAME, $username)
			->fetch();

		if (!$row instanceof ActiveRow) {
			throw new AuthenticationException('The username is incorrect.', self::IdentityNotFound);
		} elseif (!$this->passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new AuthenticationException('The password is incorrect.', self::InvalidCredential);
		} elseif ($this->passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update([
				self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
			]);
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);

		return new SimpleIdentity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
	}

	/**
	 * Adds new user.
	 *
	 * @throws DuplicateNameException
	 */
	public function add(string $username, string $email, string $password): void
	{
		Validators::assert($email, 'email');
		try {
			$this->database->table(self::TABLE_NAME)->insert([
				self::COLUMN_NAME => $username,
				self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
				self::COLUMN_EMAIL => $email,
			]);
		} catch (UniqueConstraintViolationException $e) {
			throw new DuplicateNameException();
		}
	}

}
