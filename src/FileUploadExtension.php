<?php

namespace Zet\FileUpload;

/**
 * Class FileUploadExtension
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload
 */
class FileUploadExtension extends \Nette\DI\CompilerExtension {
	
	/**
	 * Výchozí konfigurační hodnoty.
	 * @var array
	 */
	private $defaults = [
		"maxFiles" => 25,
		"maxFileSize" => NULL,
		"uploadModel" => NULL,
		"fileFilter" => NULL,
		"uiMode" => FileUploadControl::UI_FULL
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
		if(is_string($this->configuration["uiMode"])) {
			$value = $this->configuration["uiMode"];
			
			switch($value) {
				case "full":
					$this->configuration["uiMode"] = FileUploadControl::UI_FULL;
					break;
				case "minimal":
					$this->configuration["uiMode"] = FileUploadControl::UI_MINIMAL;
					break;
				default:
					$this->configuration["uiMode"] = FileUploadControl::UI_FULL;
					break;
			}
		}
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