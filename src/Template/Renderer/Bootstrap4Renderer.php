<?php declare(strict_types = 1);

namespace Contributte\FileUpload\Template\Renderer;

use Nette\Utils\Html;

/**
 * Class Bootstrap4Renderer
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Template\Renderer
 */
class Bootstrap4Renderer extends BaseRenderer
{

	public function init(): void
	{
		parent::init();

		$this->elements["globalProgressValue"] = '';
		$this->elements["fileProgressValue"] = '';
	}

	/**
	 * Sestavení výchozí šablony uploaderu.
	 */
	public function buildDefaultTemplate(): Html
	{
		$customContainer = Html::el("div");

		/** @var Html $input */
		$input = $this->elements["input"];
		$input->setAttribute("style", "display: none");
		/** @var Html $input */
		$input = $this->elements["input"];
		$id = $input->getAttribute("id");
		$button = Html::el("button type='button' class='btn btn-primary mb-2'");
		$button->setAttribute("onclick", "document.getElementById('$id').click(); return false;");
		$button->setText("Nahrát soubor");

		$customContainer->addHtml($this->elements["input"]);
		$customContainer->addHtml($button);

		/** @var Html $globalProgress */
		$globalProgress = $this->elements["globalProgress"];
		$globalProgress->setAttribute("class", "progress-bar")
			->setAttribute("style", "height: 20px");
		$progressContainer = Html::el("div class='progress mb-2'");
		$progressContainer->addHtml($globalProgress);
		$customContainer->addHtml($progressContainer);

		/** @var Html $container */
		$container = $this->elements["container"];
		$container->setName("table");
		$container->setAttribute("class", "table");

		$thead = Html::el("thead class='thead-inverse'");
		$tr = Html::el("tr");
		$preview = Html::el("th style='width: 15%;'");
		$tr->addHtml($preview);
		$filename = Html::el("th")->setText("Soubor");
		$tr->addHtml($filename);
		$status = Html::el("th style='width: 20%'")->setText("Stav");
		$tr->addHtml($status);
		$actions = Html::el("th style='width: 50px'");
		$tr->addHtml($actions);
		$thead->addHtml($tr);

		$container->addHtml($thead);
		$customContainer->addHtml($container);

		return $customContainer;
	}

	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 */
	public function buildFileContainerTemplate(): Html
	{
		$tr = Html::el("tr");

		$preview = Html::el("td class='align-middle'");
		/** @var Html $imagePreview */
		$imagePreview = $this->elements["imagePreview"];
		$preview->addHtml($imagePreview->setAttribute("width", "100%")->setAttribute("class", "rounded"));
		/** @var Html $filePreview */
		$filePreview = $this->elements["filePreview"];
		$preview->addHtml($filePreview->setName("span")->setAttribute("class", "badge badge-pill badge-info"));
		$tr->addHtml($preview);

		$name = Html::el("td class='align-middle'");
		$name->addHtml($this->elements["filename"]);
		$tr->addHtml($name);

		$progressTd = Html::el("td class='align-middle'");
		$progressContainer = Html::el("div class='progress'");
		/** @var Html $fileProgress */
		$fileProgress = $this->elements["fileProgress"];
		$progress = $fileProgress->setAttribute("class", "progress-bar")
			->setAttribute("style", "height: 10px");
		$progressContainer->addHtml($progress);
		$progressTd->addHtml($progressContainer);
		$tr->addHtml($progressTd);

		$delete = Html::el("td class='align-middle text-center'");
		/** @var Html $delEl */
		$delEl = $this->elements["delete"];
		$delEl->setAttribute("class", "btn btn-outline-danger")
			->setHtml("&times;");
		$delete->addHtml($delEl);
		$tr->addHtml($delete);

		return $tr;
	}

	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 */
	public function buildFileError(): Html
	{
		$tr = Html::el("tr class='bg-danger text-light'");
		/** @var Html $errorMessage */
		$errorMessage = $this->elements["errorMessage"];
		$tr->addHtml($errorMessage->setName("td")->addAttributes([
			"colspan" => 4,
		]));

		return $tr;
	}
}
