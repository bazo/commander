<?php

use Nette\Utils\Strings;

// Load Nette Framework or autoloader generated by Composer
define('VENDORS_DIR', __DIR__ . '/../vendor');
require VENDORS_DIR . '/autoload.php';

$configurator = new Nette\Configurator;

$debugMode = false;
$environmentFile = __DIR__ . '/config/environment';
if (file_exists($environmentFile)) {
	$environment = file_get_contents($environmentFile);
	$debugMode = $environment === 'production' ? false : true;
} else {
	$environment = 'production';
}
$environment = Strings::trim(Strings::lower($environment));

$debugSwitchFile = __DIR__ . '/config/debug';

if (file_exists($debugSwitchFile)) {
	$debugMode = Strings::trim(mb_strtolower(file_get_contents($debugSwitchFile))) === 'true' ? true : false;
}

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode($debugMode);
$configurator->enableDebugger(__DIR__ . '/../log');

// Specify folder for cache
$configurator->setTempDirectory(__DIR__ . '/../temp');

// Enable RobotLoader - this will load all classes automatically
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../libs')
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/app.neon', $configurator::NONE);

$configurator->onCompile[] = function (\Nette\Configurator $configurator, \Nette\DI\Compiler $compiler) {
	$compiler->addExtension('database', new \Bazo\MongoDb\DI\DocumentManagerExtension());
	$compiler->addExtension('odmCommands', new \Bazo\MongoDb\DI\DoctrineODMCommandsExtension());
	$compiler->addExtension('console', new \Extensions\ConsoleExtension);
	$compiler->addExtension('mediator', new \Extensions\MediatorExtension);
};

$localConfig = __DIR__ . '/config/config.neon';
if (file_exists($localConfig)) {
	$configurator->addConfig($localConfig);
}
$container = $configurator->createContainer();

return $container;
