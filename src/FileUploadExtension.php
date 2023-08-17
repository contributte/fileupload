<?php

declare(strict_types=1);

namespace Zet\FileUpload;

use Nette\DI\Container;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * Class FileUploadExtension
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload
 */
final class FileUploadExtension extends \Nette\DI\CompilerExtension
{


//	/**
//	 * Výchozí konfigurační hodnoty.
//	 * @var array
//	 */
//	private $defaults = [
//		"maxFiles" => 25,
//		"maxFileSize" => NULL,
//		"uploadModel" => NULL,
//		"fileFilter" => NULL,
//		"renderer" => '\Zet\FileUpload\Template\Renderer\Html5Renderer',
//		"translator" => NULL,
//		"autoTranslate" => false,
//		"messages" => [
//			"maxFiles" => "Maximální počet souborů je {maxFiles}.",
//			"maxSize" => "Maximální velikost souboru je {maxSize}.",
//			"fileTypes" => "Povolené typy souborů jsou {fileTypes}.",
//
//			// PHP Errors
//			"fileSize" => "Soubor je příliš veliký.",
//			"partialUpload" => "Soubor byl nahrán pouze částěčně.",
//			"noFile" => "Nebyl nahrán žádný soubor.",
//			"tmpFolder" => "Chybí dočasná složka.",
//			"cannotWrite" => "Nepodařilo se zapsat soubor na disk.",
//			"stopped" => "Nahrávání souboru bylo přerušeno."
//		],
//		"uploadSettings" => []
//	];

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
								'maxFiles' => Expect::int(25),
								'maxFileSize' => Expect::string(),
								'uploadModel' => Expect::string(),
								'fileFilter' => Expect::string(),
								'renderer' => Expect::string('\Zet\FileUpload\Template\Renderer\Html5Renderer'),
								'translator' => Expect::string(),
								'autoTranslate' => Expect::bool(false),
								'messages' => Expect::structure([
										'maxFiles' => Expect::string('Maximální počet souborů je {maxFiles}.'),
										'maxSize' => Expect::string('Maximální velikost souboru je {maxSize}.'),
										'fileTypes' => Expect::string('Povolené typy souborů jsou {fileTypes}.'),
										'fileSize' => Expect::string('Soubor je příliš veliký.'),
										'partialUpload' => Expect::string('Soubor byl nahrán pouze částěčně.'),
										'noFile' => Expect::string('Nebyl nahrán žádný soubor.'),
										'tmpFolder' => Expect::string('Chybí dočasná složka.'),
										'cannotWrite' => Expect::string('Nepodařilo se zapsat soubor na disk.'),
										'stopped' => Expect::string('Nahrávání souboru bylo přerušeno.'),
								]),
								'uploadSettings' => Expect::array(),
		]);
	}
//	/**
//	 * Konfigurace nastavená uživatelem.
//	 * @var array
//	 */
//	private $configuration = [];
//	/**
//	 *
//	 */
//	public function loadConfiguration() {
//		$this->configuration = $this->getConfig($this->defaults);
//	}


	/**
	 * @param \Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(\Nette\PhpGenerator\ClassType $class): void
	{
		$init = $class->getMethods()['initialize'];

		$init->addBody('\Zet\FileUpload\FileUploadControl::register($this->getService(?), ?);', [
//		$this->getContainerBuilder()->getByType('\Nette\DI\Container'), $this->configuration,
				$this->getContainerBuilder()->getByType(Container::class), $this->getConfig(),
		]);
	}
}
