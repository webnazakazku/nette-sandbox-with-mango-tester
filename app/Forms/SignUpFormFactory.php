<?php declare(strict_types = 1);

namespace App\Forms;

use App\Model\DuplicateNameException;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use stdClass;

final class SignUpFormFactory
{

	use SmartObject;

	private const PASSWORD_MIN_LENGTH = 7;

	private FormFactory $factory;

	private UserManager $userManager;

	public function __construct(FormFactory $factory, UserManager $userManager)
	{
		$this->factory = $factory;
		$this->userManager = $userManager;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->addText('username', 'Pick a username:')
			->setRequired('Please pick a username.');

		$form->addEmail('email', 'Your e-mail:')
			->setRequired('Please enter your e-mail.');

		$form->addPassword('password', 'Create a password:')
			->setOption('description', sprintf('at least %d characters', self::PASSWORD_MIN_LENGTH))
			->setRequired('Please create a password.')
			->addRule($form::MinLength, null, self::PASSWORD_MIN_LENGTH);

		$form->addSubmit('send', 'Sign up');

		$form->onSuccess[] = function (Form $form, stdClass $values) use ($onSuccess): void {
			try {
				$this->userManager->add($values->username, $values->email, $values->password);
			} catch (DuplicateNameException $e) {
				$form->addError('Username is already taken.');

				return;
			}

			$onSuccess();
		};

		return $form;
	}

}
