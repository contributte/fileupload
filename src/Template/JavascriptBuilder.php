<?php declare(strict_types=1);

namespace Zet\FileUpload\Template;

use Nette\Application\UI\ITemplate;
use Nette\Caching\Cache;
use Nette\Localization\ITranslator;
use Nette\SmartObject;
use Tracy\Debugger;
use Zet\FileUpload\Model\UploadController;
use Zet\FileUpload\Template\Renderer\BaseRenderer;

/**
 * Class JavascriptBuilder
 *
 * @author  Zechy <email@zechy.cz>
 */
class JavascriptBuilder
{

	use SmartObject;

	/**
	 * @var ITemplate
	 */
	private $template;

	/**
	 * @var Cache
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
	 * @var UploadController
	 */
	private $controller;

	/**
	 * JavascriptBuilder constructor.
	 *
	 * @param BaseRenderer     $renderer
	 * @param UploadController $controller
	 */
	public function __construct(
		BaseRenderer $renderer,
		UploadController $controller
	)
	{
		$this->renderer = $renderer;
		$this->controller = $controller;
		$this->cache = $controller->getUploadControl()->getCache();

		$this->template = $controller->template;
		$this->template->setFile(__DIR__ . "/js.latte");
	}

	public function getJsTemplate(): string
	{
		return $this->buildTemplate();
	}

	private function buildTemplate(): string
	{
		$this->setSettings();
		$this->setRendererSettings();
		$this->buildTemplates();

		return (string) $this->template;
	}

	/**
	 * Základní nastavení.
	 */
	private function setSettings(): void
	{
		$this->template->uploadUrl = $this->controller->link("upload");
		$this->template->renameLink = $this->controller->link("rename");
		$this->template->removeLink = $this->controller->link("//remove");
		$this->template->inputId = $this->renderer->getElements()["input"]  !== null? $this->renderer->getElements()["input"]->attrs["id"]: null;

		$this->needTranslate();
		$this->template->messages = $this->controller->getUploadControl()->getMessages();

		/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->maxFiles = $this->controller->getUploadControl()->getMaxFiles();
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->maxFileSize = $this->controller->getUploadControl()->getMaxFileSize();
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->fileSizeString = $this->controller->getUploadControl()->getFileSizeString();
		$this->template->productionMode = Debugger::$productionMode;
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->template->token = $this->controller->getUploadControl()->getToken();
		$this->template->params = json_encode($this->controller->getUploadControl()->getParams());
		$this->template->settings = $this->controller->getUploadControl()->getUploadSettings();

		$default = $this->controller->getUploadControl()->getDefaultFiles();

		$defaultFiles = [];
		foreach ($default as $file) {
			$defaultFiles[] = $file->toArray();
		}

		$this->template->defaultFiles = $defaultFiles;
	}

	private function buildTemplates(): void
	{
		$this->template->fileContainerTemplate = $this->renderer->buildFileContainerTemplate();
		$this->template->fileErrorTemplate = $this->renderer->buildFileError();
	}

	private function setRendererSettings(): void
	{
		$elements = $this->renderer->getElements();

		$components = [];
		foreach ($elements as $type => $element) {
			if ($element !== null) {
				$components[$type] = $element->getAttribute("data-upload-component");
			} else {
				$components[$type] = null;
			}
		}

		$this->template->components = $components;
	}

	private function needTranslate(): void
	{
		$upload = $this->controller->getUploadControl();
		if ($upload->isAutoTranslate()) {
			foreach ($upload->getMessages() as $key => $value) {
				/** @var ITranslator $translator */
				$translator = $upload->getTranslator();
				$upload->setMessage($key, $translator->translate($value));
			}
		}
	}
}
