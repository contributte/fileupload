<?php

namespace Zet\FileUpload\Template;

use Nette\Object;
use Tracy\Debugger;
use Zet\FileUpload\Model\UploadController;
use Zet\FileUpload\Template\Renderer\BaseRenderer;

/**
 * Class JavascriptBuilder
 * @author Zechy <email@zechy.cz>
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
	private $deleteLink;
	
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
	 * @param \Zet\FileUpload\Template\Renderer\BaseRenderer $renderer
	 * @param \Zet\FileUpload\Model\UploadController $controller
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
		if(Debugger::$productionMode) {
			$cacheIdentifier = $this->controller->getUploadControl()->getHtmlId(). "-js";
			if(is_null($cacheTemplate = $this->cache->load($cacheIdentifier))) {
				$template = $this->buildTemplate();
				$this->cache->save($cacheIdentifier, $template);
			} else {
				return $cacheTemplate;
			}
		} else {
			return $this->buildTemplate();
		}
	}
	
	/**
	 * @return string
	 */
	private function buildTemplate() {
		$this->setSettings();
		$this->setRendererSettings();
		$this->buildTemplates();
		
		return (string) $this->template;
	}
	
	/**
	 * Základní nastavení.
	 */
	private function setSettings() {
		$this->template->uploadUrl = $this->controller->link("upload");
		$this->template->renameLink = $this->controller->link("rename");
		$this->template->deleteLink = $this->controller->link("remove");
		$this->template->inputId = $this->renderer->getElements()["input"]->attrs["id"];
		
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->maxFiles = $this->controller->getUploadControl()->getMaxFiles();/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->maxFileSize = $this->controller->getUploadControl()->getMaxFileSize();/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->fileSizeString = $this->controller->getUploadControl()->getFileSizeString();
		$this->template->productionMode = \Tracy\Debugger::$productionMode;/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->token = $this->controller->getUploadControl()->getToken();
		$this->template->params = json_encode($this->controller->getUploadControl()->getParams());
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
		$name = str_replace("-", "", $this->controller->getUploadControl()->getHtmlId());
		$this->template->rendererName = $name . "Renderer";
		
		$elements = $this->renderer->getElements();
		
		foreach($elements as $type => $element) {
			$selector = $type . "Selector";
			
			if(!is_null($element)) {
				$this->template->$type = true;
				$this->template->$selector = $element->attrs["data-upload-component"];
			} else {
				$this->template->$type = false;
				$this->template->$selector = "";
			}
		}
	}
}