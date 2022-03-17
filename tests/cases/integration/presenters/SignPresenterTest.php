<?php declare(strict_types = 1);

namespace AppTests\Presenters;

use App\Model\UserManager;
use Mockery\MockInterface;
use Nette;
use Webnazakazku\MangoTester\Infrastructure\TestCase;
use Webnazakazku\MangoTester\PresenterTester\PresenterTester;

$testContainerFactory = require __DIR__ . '/../../../bootstrap.php';


/**
 * @testCase
 */
class SignPresenterTest extends TestCase
{

	/** @var PresenterTester */
	private $presenterTester;

	public function __construct(PresenterTester $presenterTester)
	{
		$this->presenterTester = $presenterTester;
	}

	public function testSignInActionRenders(): void
	{
		$testRequest = $this->presenterTester->createRequest('Sign')
			->withParameters(['action' => 'in']);

		$testResponse = $this->presenterTester->execute($testRequest);
		$testResponse->assertRenders([
			'Sign In',
			'<form class=form-horizontal action="%S%" method="post" id="frm-signInForm">',
		]);
	}

	public function testSignInFormSentOk(Nette\Database\Context $ntb): void
	{
		$ntb->table('users')->insert([
			'username' => 'dave',
			'password' => password_hash('correct horse battery staple', PASSWORD_BCRYPT),
			'email' => 'dave@example.com',
		]);

		$testRequest = $this->presenterTester->createRequest('Sign')
			->withParameters(['action' => 'in'])
			->withForm('signInForm', [
				'username' => 'dave',
				'password' => 'correct horse battery staple',
			]);

		$testResponse = $this->presenterTester->execute($testRequest);
		$testResponse->assertFormValid('signInForm');
		$testResponse->assertRedirects('Homepage');
	}

	public function testSignInFormSentWithWrongPassword(Nette\Database\Context $ntb): void
	{
		$ntb->table('users')->insert([
			'username' => 'dave',
			'password' => password_hash('correct horse battery staple', PASSWORD_BCRYPT),
			'email' => 'dave@example.com',
		]);

		$testRequest = $this->presenterTester->createRequest('Sign')
			->withParameters(['action' => 'in'])
			->withForm('signInForm', [
				'username' => 'dave',
				'password' => 'wrong password',
			]);

		$testResponse = $this->presenterTester->execute($testRequest);
		$testResponse->assertFormHasErrors('signInForm', ['The username or password you entered is incorrect.']);
		$testResponse->assertRenders();
	}

	public function testSignInFormSentWithEmptyPassword(): void
	{
		$testRequest = $this->presenterTester->createRequest('Sign')
			->withParameters(['action' => 'in'])
			->withForm('signInForm', [
				'username' => 'dave',
				'password' => '',
			]);

		$testResponse = $this->presenterTester->execute($testRequest);
		$testResponse->assertFormHasErrors('signInForm', ['Please enter your password.']);
		$testResponse->assertRenders();
	}

	public function testSignUpActionRenders(): void
	{
		$testRequest = $this->presenterTester->createRequest('Sign')
			->withParameters(['action' => 'up']);

		$testResponse = $this->presenterTester->execute($testRequest);
		$testResponse->assertRenders([
			'Sign Up',
			'<form class=form-horizontal action="%S%" method="post" id="frm-signUpForm">',
		]);
	}

	public function testSignUpFormSentOk(): void
	{
		$testRequest = $this->presenterTester->createRequest('Sign')
			->withParameters(['action' => 'up'])
			->withForm('signUpForm', [
				'username' => 'dave',
				'password' => 'correct horse battery staple',
				'email' => 'dave@example.com',
			]);

		$testResponse = $this->presenterTester->execute($testRequest);
		$testResponse->assertFormValid('signUpForm');
		$testResponse->assertRedirects('Homepage');
	}

	public function testSignUpFormSentWithDuplicateUsername(Nette\Database\Context $ntb): void
	{
		$ntb->table('users')->insert([
			'username' => 'dave',
			'password' => password_hash('does not matter', PASSWORD_BCRYPT),
			'email' => 'also-does-not-matter@example.com',
		]);

		$testRequest = $this->presenterTester->createRequest('Sign')
			->withParameters(['action' => 'up'])
			->withForm('signUpForm', [
				'username' => 'dave',
				'password' => 'correct horse battery staple',
				'email' => 'dave@example.com',
			]);

		$testResponse = $this->presenterTester->execute($testRequest);
		$testResponse->assertFormHasErrors('signUpForm', ['Username is already taken.']);
		$testResponse->assertRenders();
	}

	public function testSignUpFormSentWithShortPassword(): void
	{
		$testRequest = $this->presenterTester->createRequest('Sign')
			->withParameters(['action' => 'up'])
			->withForm('signUpForm', [
				'username' => 'dave',
				'password' => 'short',
				'email' => 'johny@example.com',
			]);

		$testResponse = $this->presenterTester->execute($testRequest);
		$testResponse->assertFormHasErrors('signUpForm', ['Please enter at least 7 characters.']);
		$testResponse->assertRenders();
	}

	/**
	 * @param UserManager|MockInterface $userManager
	 */
	public function testSignUpFormSentOkWithMockedUserManager(UserManager $userManager): void
	{
		$userManager->shouldReceive('add')
			->withArgs(['dave', 'dave@example.com', 'lorem ipsum']);

		$testRequest = $this->presenterTester->createRequest('Sign')
			->withParameters(['action' => 'up'])
			->withForm('signUpForm', [
				'username' => 'dave',
				'password' => 'lorem ipsum',
				'email' => 'dave@example.com',
			]);

		$testResponse = $this->presenterTester->execute($testRequest);
		$testResponse->assertFormValid('signUpForm');
		$testResponse->assertRedirects('Homepage');
	}

}

SignPresenterTest::run($testContainerFactory);
