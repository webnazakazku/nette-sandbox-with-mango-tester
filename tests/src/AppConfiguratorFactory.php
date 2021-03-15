<?php declare(strict_types = 1);

namespace AppTests;

use Mangoweb\Tester\DatabaseCreator\DatabaseCreator;
use Mangoweb\Tester\Infrastructure\Container\IAppConfiguratorFactory;
use Nette\Configurator;
use Nette\DI\Container;


class AppConfiguratorFactory implements IAppConfiguratorFactory
{
	/** @var DatabaseCreator */
	private $databaseCreator;


	public function __construct(DatabaseCreator $databaseCreator)
	{
		$this->databaseCreator = $databaseCreator;
	}


	public function create(Container $testContainer): Configurator
	{
		$testDatabaseName = $this->databaseCreator->getDatabaseName();
		$testContainerParameters = $testContainer->getParameters();

		$configurator = new Configurator;
		$configurator->setDebugMode(TRUE);
		$configurator->setTempDirectory($testContainerParameters['tempDir']);

		$configurator->addConfig("$testContainerParameters[appDir]/config/common.neon");
		$configurator->addConfig("$testContainerParameters[appDir]/config/local.neon");
		$testDatabaseHost = $testContainerParameters['dbHost'] . ':' . $testContainerParameters['dbPort'];
		$configurator->addConfig([
			'console' => [
				'url' => null,
			],
			'database' => [
				'dsn' => sprintf('mysql:host=%s;dbname=%s', $testDatabaseHost, $testDatabaseName),
			],
			'services' => [
				'database.default.connection' => [
					'setup' => [
						new \Nette\DI\Statement('@databaseCreator::createTestDatabase')
					],
				],
			],
		]);

		return $configurator;
	}

}
