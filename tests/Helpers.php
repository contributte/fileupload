<?php declare(strict_types = 1);

namespace Tests;

use Nette\Configurator;
use Nette\DI\Container;
use Zet\FileUpload\FileUploadExtension;

final class Helpers
{

	/**
	 * @param string $tempDir
	 * @param array<mixed> $config
	 * @return Container
	 */
	public static function createContainerFromConfigurator(
		string $tempDir,
		array $config = []
	): Container
	{
		$config = array_merge_recursive($config, [
			'extensions' => [
				'fileUpload' => FileUploadExtension::class,
			],
		]);

		$configurator = new Configurator();

		$configurator->setTempDirectory($tempDir)
			->addConfig($config);

		return $configurator->createContainer();
	}

}
