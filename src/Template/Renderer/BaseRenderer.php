<?php

declare(strict_types=1);

namespace Zet\FileUpload\Template\Renderer;

use Nette\Localization\ITranslator;
use Nette\SmartObject;
use Nette\Utils\Html;
use Zet\FileUpload\FileUploadControl;

/**
 * Class BaseRenderer
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Template\Renderer
 */
abstract class BaseRenderer implements IUploadRenderer
{

	use SmartObject;

	/**
	 * Seznam všech základních komponent uploaderu:
	 * <ul>
	 * <li><b>container</b>: Obalový prvek uploaderu.</li>
	 * <li><b>input</b>: File input, na kterém je registrovaný uploaderu.</li>
	 * <li><b>globalProgres</b>: Element sloužící jako globální progress bar.</li>
	 * <li><b>globalProgressValue</b>: Element sloužící pro vypsání aktuální hodnoty progress baru.</li>
	 * <li><b>fileProgress</b>: Element sloužící jako progress bar pro soubory.</li>
	 * <li><b>fileProgressValue</b>: Element sloužící pro vypsání aktuální hodnoty progress baru.</li>
	 * <li><b>imagePreview</b>: Element pro zobrazení náhledu obrázku.</li>
	 * <li><b>filePreview</b>: Element pro zobrazení koncovky/ikony souboru.</li>
	 * <li><b>filename</b>: Element pro zobrazení názvu souboru.</li>
	 * <li><b>delete</b>: Element pro smazání souboru.</li>
	 * <li><b>errorMessage</b>: Element pro zobrazení chybové zprávy.</li>
	 * </ul>
	 *
	 * @var Html[]
	 */
	protected $elements = [
			'container' => null,
			'input' => null,
			'globalProgress' => null,
			'globalProgressValue' => null,
			'fileProgress' => null,
			'fileProgressValue' => null,
			'imagePreview' => null,
			'filePreview' => null,
			'filename' => null,
			'delete' => null,
			'errorMessage' => null,
	];

	/** @var FileUploadControl */
	protected $fileUploadControl;

	/** @var ITranslator|NULL */
	protected $translator;

	/**
	 * ID template ve tvaru: HtmlId-ElementType
	 *
	 * @var string
	 */
	private $idTemplate = '%s-%s';


	/**
	 * BaseRenderer constructor.
	 *
	 * @param FileUploadControl    $fileUploadControl
	 * @param ITranslator|NULL $translator
	 */
	public function __construct(
					FileUploadControl $fileUploadControl,
					ITranslator $translator = null
	)
	{
		$this->fileUploadControl = $fileUploadControl;

		$this->init();
		$this->translator = $translator;
	}


	/**
	 * Inicializace elementů.
	 */
	public function init()
	{
		$htmlId = $this->fileUploadControl->getHtmlId();

		foreach ($this->elements as $type => $value) {
			if ($type == 'input') {
				$element = Html::el("input type='file' multiple='multiple'")->addAttributes([
						'id' => $htmlId,
						'name' => $this->fileUploadControl->getHtmlName(),
						'data-upload-component' => $htmlId,
				]);
			} elseif ($type == 'delete') {
				$element = Html::el("button type='button'")->addAttributes([
						'data-upload-component' => sprintf($this->idTemplate, $htmlId, $type),
				]);
			} elseif ($type == 'imagePreview') {
				$element = Html::el('img')->addAttributes([
						'data-upload-component' => sprintf($this->idTemplate, $htmlId, $type),
				]);
			} else {
				$element = Html::el('div')->addAttributes([
						'data-upload-component' => sprintf($this->idTemplate, $htmlId, $type),
				]);
			}

			$this->elements[$type] = $element;
		}
	}


	/**
	 * @return Html[]
	 */
	public function getElements()
	{
		return $this->elements;
	}


	/**
	 * Sestavení výchozí šablony uploaderu.
	 *
	 * @return Html
	 */
	abstract public function buildDefaultTemplate();


	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 *
	 * @return Html
	 */
	abstract public function buildFileContainerTemplate();


	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 *
	 * @return Html
	 */
	abstract public function buildFileError();
}
