<?php

namespace Zet\FileUpload;

/**
 * Class FileUploadExtension
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload
 */
final class FileUploadExtension extends \Nette\DI\CompilerExtension {
	
	/**
	 * Výchozí konfigurační hodnoty.
	 * @var array
	 */
	private $defaults = [
		"maxFiles" => 25,
		"maxFileSize" => NULL,
		"uploadModel" => NULL,
		"fileFilter" => NULL,
		"renderer" => '\Zet\FileUpload\Template\Renderer\Html5Renderer',
		"translator" => NULL,
		"autoTranslate" => false,
		"messages" => [
			"maxFiles" => "Maximální počet souborů je {maxFiles}.",
			"maxSize" => "Maximální velikost souboru je {maxSize}.",
			"fileTypes" => "Povolené typy souborů jsou {fileTypes}.",
			
			// PHP Errors
			"fileSize" => "Soubor je příliš veliký.",
			"partialUpload" => "Soubor byl nahrán pouze částěčně.",
			"noFile" => "Nebyl nahrán žádný soubor.",
			"tmpFolder" => "Chybí dočasná složka.",
			"cannotWrite" => "Nepodařilo se zapsat soubor na disk.",
			"stopped" => "Nahrávání souboru bylo přerušeno."
		]
	];
	
	/**
	 * Konfigurace nastavená uživatelem.
	 * @var array
	 */
	private $configuration = [];
	
	/**
	 *
	 */
	public function loadConfiguration() {
		$this->configuration = $this->getConfig($this->defaults);
	}
	
	/**
	 * @param \Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(\Nette\PhpGenerator\ClassType $class) {
		$init = $class->methods["initialize"];
		
		$init->addBody('\Zet\FileUpload\FileUploadControl::register($this->getService(?), ?);', [
			$this->getContainerBuilder()->getByType('\Nette\DI\Container'), $this->configuration
		]);
	}
}