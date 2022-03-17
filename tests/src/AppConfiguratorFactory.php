<?php declare(strict_types = 1);

namespace AppTests;

use Nette\Configurator;
use Nette\DI\Container;
use Nette\DI\Definitions\Statement;
use Webnazakazku\MangoTester\DatabaseCreator\DatabaseCreator;
use Webnazakazku\MangoTester\Infrastructure\Container\IAppConfiguratorFactory;

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

		$configurator = new Configurator();
		$configurator->setDebugMode(true);
		$configurator->setTempDirectory($testContainerParameters['tempDir']);

		$configurator->addConfig($testContainerParameters['appDir'] . '/config/common.neon');
		$configurator->addConfig($testContainerParameters['appDir'] . '/config/local.neon');
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
						new Statement('@databaseCreator::createTestDatabase'),
					],
				],
			],
		]);

		return $configurator;
	}

}
