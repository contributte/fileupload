<?php

namespace Zet\FileUpload\Template;

use Nette\Object;
use Tracy\Debugger;
use Zet\FileUpload\Model\UploadController;
use Zet\FileUpload\Template\Renderer\BaseRenderer;

/**
 * Class JavascriptBuilder
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload
 */
class JavascriptBuilder extends Object {
	
	/**
	 * @var \Nette\Application\UI\ITemplate
	 */
	private $template;
	
	/**
	 * @var \Nette\Caching\Cache
	 */
	private $cache;
	
	/**
	 * @var string
	 */
	private $uploadUrl;
	
	/**
	 * @var string
	 */
	private $renameLink;
	
	/**
	 * @var BaseRenderer
	 */
	private $renderer;
	
	/**
	 * @var \Zet\FileUpload\Model\UploadController
	 */
	private $controller;
	
	/**
	 * JavascriptBuilder constructor.
	 *
	 * @param \Zet\FileUpload\Template\Renderer\BaseRenderer $renderer
	 * @param \Zet\FileUpload\Model\UploadController         $controller
	 */
	public function __construct(
		BaseRenderer $renderer,
		UploadController $controller
	) {
		$this->renderer = $renderer;
		$this->controller = $controller;
		$this->cache = $controller->getUploadControl()->getCache();
		
		$this->template = $controller->template;
		$this->template->setFile(__DIR__ . "/js.latte");
	}
	
	/**
	 * @return string
	 */
	public function getJsTemplate() {
		return $this->buildTemplate();
	}
	
	/**
	 * @return string
	 */
	private function buildTemplate() {
		$this->setSettings();
		$this->setRendererSettings();
		$this->buildTemplates();
		
		return (string)$this->template;
	}
	
	/**
	 * Základní nastavení.
	 */
	private function setSettings() {
		$this->template->uploadUrl = $this->controller->link("upload");
		$this->template->renameLink = $this->controller->link("rename");
		$this->template->removeLink = $this->controller->link("//remove");
		$this->template->inputId = $this->renderer->getElements()["input"]->attrs["id"];
		
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->maxFiles = $this->controller->getUploadControl()->getMaxFiles();
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->maxFileSize = $this->controller->getUploadControl()->getMaxFileSize();
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->fileSizeString = $this->controller->getUploadControl()->getFileSizeString();
		$this->template->productionMode = \Tracy\Debugger::$productionMode;
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->token = $this->controller->getUploadControl()->getToken();
		$this->template->params = json_encode($this->controller->getUploadControl()->getParams());
		
		$default = $this->controller->getUploadControl()->getDefaulltFiles();
		
		$defaultFiles = [];
		foreach($default as $file) {
			$defaultFiles[] = $file->toArray();
		}
		
		$this->template->defaultFiles = $defaultFiles;
	}
	
	/**
	 *
	 */
	private function buildTemplates() {
		$this->template->fileContainerTemplate = $this->renderer->buildFileContainerTemplate();
		$this->template->fileErrorTemplate = $this->renderer->buildFileError();
	}
	
	/**
	 *
	 */
	private function setRendererSettings() {
		$elements = $this->renderer->getElements();
		
		$components = [];
		foreach($elements as $type => $element) {
			if($element !== null) {
				$components[$type] = $element->getAttribute("data-upload-component");
			} else {
				$components[$type] = null;
			}
		}
		
		$this->template->components = $components;
	}
}