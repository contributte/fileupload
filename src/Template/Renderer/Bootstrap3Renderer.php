<?php

declare(strict_types=1);

namespace Zet\FileUpload\Template\Renderer;

use Nette\Utils\Html;

/**
 * Class Bootstrap3Renderer
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Template\Renderer
 */
class Bootstrap3Renderer extends BaseRenderer
{


	public function init()
	{
		parent::init();

		$this->elements['globalProgressValue'] = null;
		$this->elements['fileProgressValue'] = null;
	}


	/**
	 * Sestavení výchozí šablony uploaderu.
	 *
	 * @return Html
	 */
	public function buildDefaultTemplate()
	{
		$customContainer = Html::el('div');

		$this->elements['input']->setAttribute('style', 'display: none');
		$id = $this->elements['input']->getAttribute('id');
		$button = Html::el("button type='button' class='btn btn-primary'");
		$button->setAttribute('onclick', "document.getElementById('$id').click(); return false;")
						->setAttribute('style', 'margin-bottom: 10px');
		$button->setText('Nahrát soubor');

		$customContainer->addHtml($this->elements['input']);
		$customContainer->addHtml($button);

		$globalProgress = $this->elements['globalProgress']
						->setAttribute('class', 'progress-bar');
		$progressContainer = Html::el("div class='progress'");
		$progressContainer->addHtml($globalProgress);
		$customContainer->addHtml($progressContainer);

		$container = Html::el('table');
		$container->setAttribute('class', 'table table-striped');

		$thead = Html::el('thead');
		$tr = Html::el('tr');
		$preview = Html::el("th style='width: 15%;'");
		$tr->addHtml($preview);
		$filename = Html::el('th')->setText('Soubor');
		$tr->addHtml($filename);
		$status = Html::el("th style='width: 20%'")->setText('Stav');
		$tr->addHtml($status);
		$actions = Html::el("th style='width: 50px'");
		$tr->addHtml($actions);
		$thead->addHtml($tr);

		$container->addHtml($thead);

		$fileUploadContainer = $this->elements['container'];
		$fileUploadContainer->setName('tbody');
		$container->addHtml($fileUploadContainer);
		$customContainer->addHtml($container);

		return $customContainer;
	}


	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 *
	 * @return Html
	 */
	public function buildFileContainerTemplate()
	{
		$tr = Html::el('tr');

		$preview = Html::el("td style='vertical-align: middle'");
		$preview->addHtml($this->elements['imagePreview']->setAttribute('width', '100%')->setAttribute('class', 'img-rounded'));
		$preview->addHtml($this->elements['filePreview']->setName('span')->setAttribute('class', 'label label-info'));
		$tr->addHtml($preview);

		$name = Html::el("td style='vertical-align: middle'");
		$name->addHtml($this->elements['filename']);
		$tr->addHtml($name);

		$progressTd = Html::el("td style='vertical-align: middle'");
		$progressContainer = Html::el("div class='progress' style='margin-bottom: 0px'");
		$progress = $this->elements['fileProgress']->setAttribute('class', 'progress-bar');
		$progressContainer->addHtml($progress);
		$progressTd->addHtml($progressContainer);
		$tr->addHtml($progressTd);

		$delete = Html::el("td style='vertical-align: middle' class='text-center'");
		$delete->addHtml(
						$this->elements['delete']
										->setAttribute('class', 'btn btn-danger')
										->setHtml('&times;')
		);
		$tr->addHtml($delete);

		return $tr;
	}


	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 *
	 * @return Html
	 */
	public function buildFileError()
	{
		$tr = Html::el("tr class='danger'");
		$tr->addHtml($this->elements['errorMessage']->setName('td')->addAttributes([
								'colspan' => 4,
		]));

		return $tr;
	}
}
