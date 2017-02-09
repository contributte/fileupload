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
		"renderer" => '\Zet\FileUpload\Template\Renderer\Html5Renderer'
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