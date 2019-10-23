<?php

namespace App\Forms;

use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;

class SignInFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Translator */
	private $translator;

	/** @var User */
	private $user;

	public function __construct(FormFactory $factory, Translator $translator, User $user)
	{
		$this->factory = $factory;
		$this->translator = $translator;
		$this->user = $user;
	}

	/**
	 * @return Form
	 */
	public function create(callable $onSuccess)
	{
		$form = $this->factory->create();
		$form->addText('username', 'Username:')
			->setRequired('Please enter your username.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
			try {
				$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
				$this->user->login($values->username, $values->password);
			} catch (Nette\Security\AuthenticationException $e) {
				$form->addError($this->translator->translate('forms.signInForm.incorrect_credentials'));
				return;
			}
			$onSuccess();
		};

		return $form;
	}
}
