<?php declare(strict_types = 1);

namespace App\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Bridges\ApplicationLatte\Template;

/**
 * @property-read Template $template
 * @property-read Request $request
 */
final class Error4xxPresenter extends BasePresenter
{

	public function startup(): void
	{
		parent::startup();

		if (!$this->request->isMethod(Request::FORWARD)) {
			$this->error();
		}
	}

	public function renderDefault(BadRequestException $exception): void
	{
		// load template 403.latte or 404.latte or ... 4xx.latte
		$file = __DIR__ . '/templates/Error/' . $exception->getCode() . '.latte';
		$file = is_file($file)
			? $file
			: __DIR__ . '/templates/Error/4xx.latte';
		$this->template->setFile($file);
	}

}
