<?php declare(strict_types=1);

namespace Zet\FileUpload\Template\Renderer;

use Nette\Utils\Html;

/**
 * Class Bootstrap3Renderer
 *
 * @author  Zechy <email@zechy.cz>
 */
class Bootstrap3Renderer extends BaseRenderer
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
		$button = Html::el("button type='button' class='btn btn-primary'");
		$button->setAttribute("onclick", "document.getElementById('$id').click(); return false;")
			->setAttribute("style", "margin-bottom: 10px");
		$button->setText("Nahrát soubor");

		$customContainer->addHtml($this->elements["input"]);
		$customContainer->addHtml($button);

		/** @var Html $globalProgress */
		$globalProgress = $this->elements["globalProgress"];
		$globalProgress->setAttribute("class", "progress-bar");
		$progressContainer = Html::el("div class='progress'");
		$progressContainer->addHtml($globalProgress);
		$customContainer->addHtml($progressContainer);

		$container = Html::el("table");
		$container->setAttribute("class", "table table-striped");

		$thead = Html::el("thead");
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

		/** @var Html $fileUploadContainer */
		$fileUploadContainer = $this->elements["container"];
		$fileUploadContainer->setName("tbody");
		$container->addHtml($fileUploadContainer);
		$customContainer->addHtml($container);

		return $customContainer;
	}

	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 */
	public function buildFileContainerTemplate(): Html
	{
		$tr = Html::el("tr");

		$preview = Html::el("td style='vertical-align: middle'");
		/** @var Html $imagePreview */
		$imagePreview = $this->elements["imagePreview"];
		$preview->addHtml($imagePreview->setAttribute("width", "100%")->setAttribute("class", "img-rounded"));
		/** @var Html $filePreview */
		$filePreview = $this->elements["filePreview"];
		$preview->addHtml($filePreview->setName("span")->setAttribute("class", "label label-info"));
		$tr->addHtml($preview);

		$name = Html::el("td style='vertical-align: middle'");
		$name->addHtml($this->elements["filename"]);
		$tr->addHtml($name);

		$progressTd = Html::el("td style='vertical-align: middle'");
		$progressContainer = Html::el("div class='progress' style='margin-bottom: 0px'");
		/** @var Html $fileProgress */
		$fileProgress = $this->elements["fileProgress"];
		$progress = $fileProgress->setAttribute("class", "progress-bar");
		$progressContainer->addHtml($progress);
		$progressTd->addHtml($progressContainer);
		$tr->addHtml($progressTd);

		$delete = Html::el("td style='vertical-align: middle' class='text-center'");
		/** @var Html $deleteElement */
		$deleteElement = $this->elements["delete"];
		$deleteElement->setAttribute("class", "btn btn-danger")
			->setHtml("&times;");
		$delete->addHtml($deleteElement);
		$tr->addHtml($delete);

		return $tr;
	}

	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 */
	public function buildFileError(): Html
	{
		$tr = Html::el("tr class='danger'");
		/** @var Html $errorMessage */
		$errorMessage = $this->elements["errorMessage"];
		$tr->addHtml($errorMessage->setName("td")->addAttributes([
			"colspan" => 4,
		]));

		return $tr;
	}
}
