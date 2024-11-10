<?php declare(strict_types = 1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;

final class FormFactory
{

	use SmartObject;

	public function create(): Form
	{
		return new Form();
	}

}
