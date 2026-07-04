<?php declare(strict_types = 1);

namespace AppTests;

use Nette\Bootstrap\Configurator;
use Nette\DI\Container as DIContainer;
use Webnazakazku\MangoTester\DatabaseCreator\DatabaseCreator;
use Webnazakazku\MangoTester\Infrastructure\Container\IAppConfiguratorFactory;

class AppConfiguratorFactory implements IAppConfiguratorFactory
{

	private DatabaseCreator $databaseCreator;

	public function __construct(DatabaseCreator $databaseCreator)
	{
		$this->databaseCreator = $databaseCreator;
	}

	public function create(DIContainer $testContainer): Configurator
	{
		$testDatabaseName = $this->databaseCreator->getDatabaseName();
		$this->databaseCreator->createTestDatabase();

		$testContainerParameters = $testContainer->getParameters();

		$configurator = new Configurator();
		$configurator->setDebugMode(true);
		$configurator->setTempDirectory($testContainerParameters['tempDir']);

		$configurator->addConfig($testContainerParameters['appDir'] . '/config/common.neon');
		$configurator->addConfig($testContainerParameters['appDir'] . '/config/local.neon');
		$configurator->addConfig(__DIR__ . '/../config/app.neon');
		$testDatabaseHost = $testContainerParameters['dbHost'] . ':' . $testContainerParameters['dbPort'];
		$configurator->addConfig([
			'console' => [
				'url' => null,
			],
			'database' => [
				'dsn' => sprintf('mysql:host=%s;dbname=%s', $testDatabaseHost, $testDatabaseName),
			],
		]);

		$configurator->addStaticParameters(
			[
				'appDir' => __DIR__ . '/../../app',
			]
		);

		return $configurator;
	}

}
