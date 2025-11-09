<?php declare(strict_types = 1);

namespace Contributte\FileUpload\Template\Renderer;

use Nette\Utils\Html;

/**
 * Class Html5Renderer
 */
class Html5Renderer extends BaseRenderer
{

	public function init(): void
	{
		parent::init();

		$this->elements['globalProgressValue'] = '';
		$this->elements['fileProgressValue'] = '';

		/** @var Html $container */
		$container = $this->elements['container'];
		$container->setName('table')->addAttributes([
			'style' => 'width: 100%',
			'border' => '0',
		]);

		/** @var Html $globalProgress */
		$globalProgress = $this->elements['globalProgress'];
		$globalProgress->setName('progress')
			->addAttributes([
				'value' => 0,
				'max' => 100,
				'style' => 'width: 100%',
			]);

		/** @var Html $fileProgress */
		$fileProgress = $this->elements['fileProgress'];
		$fileProgress->setName('progress')
			->addAttributes([
				'value' => 0,
				'max' => 100,
				'style' => 'width: 100%',
			]);

		/** @var Html $imagePreview */
		$imagePreview = $this->elements['imagePreview'];
		$imagePreview->addAttributes([
			'class' => 'fileupload-image-preview',
		]);

		/** @var Html $filePreview */
		$filePreview = $this->elements['filePreview'];
		$filePreview->addAttributes([
			'class' => 'fileupload-file-extension',
		]);

		/** @var Html $delete */
		$delete = $this->elements['delete'];
		$delete->addAttributes([
			'class' => 'fileupload-delete-button',
		])->setHtml('&times;');
	}

	/**
	 * Sestavení výchozí šablony uploaderu.
	 */
	public function buildDefaultTemplate(): Html
	{
		/** @var Html $table */
		$table = $this->elements['container'];
		$table->setAttribute('cellpadding', '5px');

		// Header
		$tr = Html::el('tr');
		$th = Html::el("th colspan='2' style='border-right: none'");
		$th->setText('Nahrávání souborů');
		$tr->addHtml($th);
		/** @var Html $th2 */
		$th2 = Html::el("th colspan='2' style='text-align: right; border-left: none'");
		/** @var Html $input */
		$input = $this->elements['input'];
		$input->setAttribute('style', 'display: none');
		$th2->addHtml($input);
		$button = Html::el("button type='button'");
		$button->setText('Nahrát soubor');
		/** @var Html $input */
		$input = $this->elements['input'];
		$id = $input->getAttribute('id');
		$button->setAttribute('onclick', sprintf("document.getElementById('%s').click(); return false;", $id));
		$th2->addHtml($button);

		$tr->addHtml($th2);
		$table->addHtml($tr);

		// Global Progress
		$tr = Html::el('tr');
		$td = Html::el("td colspan='4'");
		$td->addHtml($this->elements['globalProgress']);
		$tr->addHtml($td);
		$table->addHtml($tr);

		return $table;
	}

	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 */
	public function buildFileContainerTemplate(): Html
	{
		$tr = Html::el('tr');

		$preview = Html::el('td')->addAttributes([
			'style' => 'width: 15%',
		]);
		/** @var Html $imagePreview */
		$imagePreview = $this->elements['imagePreview'];
		$preview->addHtml($imagePreview->setAttribute('width', '100%'));
		$preview->addHtml($this->elements['filePreview']);
		$tr->addHtml($preview);

		$name = Html::el('td');
		$name->addHtml($this->elements['filename']);
		$tr->addHtml($name);

		$progress = Html::el('td');
		$progress->addHtml($this->elements['fileProgress'])->addAttributes([
			'style' => 'width: 20%',
		]);
		$tr->addHtml($progress);

		$delete = Html::el('td')->addAttributes([
			'style' => 'width: 50px; text-align: center',
		]);
		$delete->addHtml($this->elements['delete']);
		$tr->addHtml($delete);

		return $tr;
	}

	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 */
	public function buildFileError(): Html
	{
		/** @var Html $tr */
		$tr = Html::el("tr style='background-color: #ffb6c1'");
		/** @var Html $errorMessage */
		$errorMessage = $this->elements['errorMessage'];
		$tr->addHtml($errorMessage->setName('td')->addAttributes([
			'colspan' => 4,
		]));

		return $tr;
	}

}
