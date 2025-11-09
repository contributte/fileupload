<?php declare(strict_types = 1);

namespace Contributte\FileUpload\Template;

use Contributte\FileUpload\Model\UploadController;
use Contributte\FileUpload\Template\Renderer\BaseRenderer;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Localization\ITranslator;
use Nette\SmartObject;
use Nette\Utils\Html;
use Tracy\Debugger;

/**
 * Class JavascriptBuilder
 */
class JavascriptBuilder
{

	use SmartObject;

	private Template $template;

	private BaseRenderer $renderer;

	private UploadController $controller;

	public function __construct(
		BaseRenderer $renderer,
		UploadController $controller
	)
	{
		$this->renderer = $renderer;
		$this->controller = $controller;

		$this->template = $controller->template;
		$this->template->setFile(__DIR__ . '/js.latte');
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
		$this->template->uploadUrl = $this->controller->link('upload');
		$this->template->renameLink = $this->controller->link('rename');
		$this->template->removeLink = $this->controller->link('//remove');
		/** @var Html $input */
		$input = $this->renderer->getElements()['input'];
		$this->template->inputId = $input->attrs['id'];

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
			$components[$type] = $element instanceof Html ? $element->getAttribute('data-upload-component') : null;
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
				$translated = $translator->translate($value);
				$upload->setMessage($key, is_string($translated) ? $translated : (string) $translated);
			}
		}
	}

}
