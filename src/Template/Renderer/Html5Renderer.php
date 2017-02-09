<?php

namespace Zet\FileUpload\Template\Renderer;

use Nette\Utils\Html;

/**
 * Class Html5Renderer
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Template\Renderer
 */
class Html5Renderer extends BaseRenderer {
	
	/**
	 *
	 */
	public function init() {
		parent::init();
		
		$this->elements["globalProgressValue"] = null;
		$this->elements["fileProgressValue"] = null;
		
		$this->elements["container"]->setName("table");
		
		$this->elements["globalProgress"]->setName("progress")
			->addAttributes([
				"value" => 0,
				"max" => 100
			]);
		$this->elements["fileProgress"]->setName("progress")
			->addAttributes([
				"value" => 0,
				"max" => 100
			]);
		
		$this->elements["imagePreview"]->addAttributes([
			"class" => "fileupload-image-preview"
		]);
		$this->elements["filePreview"]->addAttributes([
			"class" => "fileupload-file-extension"
		]);
		
		$this->elements["delete"]->addAttributes([
			"class" => "fileupload-delete-button"
		])->setHtml("&times;");
	}
	
	/**
	 * Sestavení výchozí šablony uploaderu.
	 * @return Html
	 */
	public function buildDefaultTemplate() {
		$table = $this->elements["container"];
		
		// Header
		$tr = Html::el("tr");
		$th = Html::el("th colspan='2'");
		$th->setText("Nahrávání souborů");
		$tr->addHtml($th);
		$th2 = Html::el("th colspan='2'");
		$th2->addHtml($this->elements["input"]);
		$tr->addHtml($th2);
		$table->addHtml($tr);
		
		// Global Progress
		$tr = Html::el("tr");
		$td = Html::el("td colspan='4'");
		$td->addHtml($this->elements["globalProgress"]);
		$tr->addHtml($td);
		$table->addHtml($tr);
		
		return $table;
	}
	
	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 * @return Html
	 */
	public function buildFileContainerTemplate() {
		$tr = Html::el("tr");
		
		$preview = Html::el("td");
		$preview->addHtml($this->elements["imagePreview"]);
		$preview->addHtml($this->elements["filePreview"]);
		$tr->addHtml($preview);
		
		$name = Html::el("td");
		$name->addHtml($this->elements["filename"]);
		$tr->addHtml($name);
		
		$progress = Html::el("td");
		$progress->addHtml($this->elements["fileProgress"]);
		$tr->addHtml($progress);
		
		$delete = Html::el("td");
		$delete->addHtml($this->elements["delete"]);
		$tr->addHtml($delete);
		
		return $tr;
	}
	
	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 * @return \Nette\Utils\Html
	 */
	public function buildFileError() {
		$tr = Html::el("tr style='background-color: #ffb6c1'");
		$tr->addHtml($this->elements["errorMessage"]->setName("td")->addAttributes([
			"colspan" => 4
		]));
		
		return $tr;
	}
}