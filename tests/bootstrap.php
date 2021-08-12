<?php declare(strict_types = 1);

use Nette\Bootstrap\Configurator;
use Ninjify\Nunjuck\Environment;

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}

// Configure environment
Environment::setupTester();
Environment::setupTimezone();
Environment::setupVariables(__DIR__);

$configurator = new Configurator();

$configurator->setTempDirectory(__DIR__ . '/tmp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../src')
	->addDirectory(__DIR__ . '/filters')
	->addDirectory(__DIR__ . '/models')
	->addDirectory(__DIR__ . '/renderers')
	->register();

return $configurator->createContainer();
