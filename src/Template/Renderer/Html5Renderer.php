<?php

declare(strict_types=1);

namespace Zet\FileUpload\Template\Renderer;

use Nette\Utils\Html;

/**
 * Class Html5Renderer
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Template\Renderer
 */
class Html5Renderer extends BaseRenderer
{


	public function init()
	{
		parent::init();

		$this->elements['globalProgressValue'] = null;
		$this->elements['fileProgressValue'] = null;

		$this->elements['container']->setName('table')->addAttributes([
				'style' => 'width: 100%',
				'border' => '0',
		]);

		$this->elements['globalProgress']->setName('progress')
						->addAttributes([
								'value' => 0,
								'max' => 100,
								'style' => 'width: 100%',
		]);
		$this->elements['fileProgress']->setName('progress')
						->addAttributes([
								'value' => 0,
								'max' => 100,
								'style' => 'width: 100%',
		]);

		$this->elements['imagePreview']->addAttributes([
				'class' => 'fileupload-image-preview',
		]);
		$this->elements['filePreview']->addAttributes([
				'class' => 'fileupload-file-extension',
		]);

		$this->elements['delete']->addAttributes([
				'class' => 'fileupload-delete-button',
		])->setHtml('&times;');
	}


	/**
	 * Sestavení výchozí šablony uploaderu.
	 * @return Html
	 */
	public function buildDefaultTemplate()
	{
		$table = $this->elements['container'];
		$table->setAttribute('cellpadding', '5px');

		// Header
		$tr = Html::el('tr');
		$th = Html::el("th colspan='2' style='border-right: none'");
		$th->setText('Nahrávání souborů');
		$tr->addHtml($th);
		$th2 = Html::el("th colspan='2' style='text-align: right; border-left: none'");
		$th2->addHtml($this->elements['input']->setAttribute('style', 'display: none'));
		$button = Html::el("button type='button'");
		$button->setText('Nahrát soubor');
		$id = $this->elements['input']->getAttribute('id');
		$button->setAttribute('onclick', "document.getElementById('$id').click(); return false;");
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
	 * @return Html
	 */
	public function buildFileContainerTemplate()
	{
		$tr = Html::el('tr');

		$preview = Html::el('td')->addAttributes([
				'style' => 'width: 15%',
		]);
		$preview->addHtml($this->elements['imagePreview']->setAttribute('width', '100%'));
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
	 * @return Html
	 */
	public function buildFileError()
	{
		$tr = Html::el("tr style='background-color: #ffb6c1'");
		$tr->addHtml($this->elements['errorMessage']->setName('td')->addAttributes([
								'colspan' => 4,
		]));

		return $tr;
	}
}
