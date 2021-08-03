<?php declare(strict_types=1);

namespace Zet\FileUpload\Template\Renderer;

use Nette\HtmlStringable;
use Nette\Localization\Translator;
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
	 * ID template ve tvaru: HtmlId-ElementType
	 *
	 * @var string
	 */
	private $idTemplate = "%s-%s";

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
	 * @var array<string, Html|string>
	 */
	protected $elements = [
		"container" => '',
		"input" => '',
		"globalProgress" => '',
		"globalProgressValue" => '',
		"fileProgress" => '',
		"fileProgressValue" => '',
		"imagePreview" => '',
		"filePreview" => '',
		"filename" => '',
		"delete" => '',
		"errorMessage" => '',
	];

	/**
	 * @var FileUploadControl
	 */
	protected $fileUploadControl;

	/**
	 * @var Translator|NULL
	 */
	protected $translator;

	/**
	 * BaseRenderer constructor.
	 *
	 * @param FileUploadControl $fileUploadControl
	 * @param Translator|NULL  $translator
	 */
	public function __construct(
		FileUploadControl $fileUploadControl,
		Translator $translator = null
	)
	{
		$this->fileUploadControl = $fileUploadControl;

		$this->init();
		$this->translator = $translator;
	}

	/**
	 * Inicializace elementů.
	 */
	public function init(): void
	{
		$htmlId = $this->fileUploadControl->getHtmlId();

		foreach ($this->elements as $type => $value) {
			if ($type == "input") {
				$element = Html::el("input type='file' multiple='multiple'")->addAttributes([
					"id" => $htmlId,
					"name" => $this->fileUploadControl->getHtmlName(),
					"data-upload-component" => $htmlId,
				]);
			} elseif ($type == "delete") {
				$element = Html::el("button type='button'")->addAttributes([
					"data-upload-component" => sprintf($this->idTemplate, $htmlId, $type),
				]);
			} elseif ($type == "imagePreview") {
				$element = Html::el("img")->addAttributes([
					"data-upload-component" => sprintf($this->idTemplate, $htmlId, $type),
				]);
			} else {
				$element = Html::el("div")->addAttributes([
					"data-upload-component" => sprintf($this->idTemplate, $htmlId, $type),
				]);
			}

			$this->elements[$type] = $element;
		}
	}

	/**
	 * @return array<string, Html|string>
	 */
	public function getElements()
	{
		return $this->elements;
	}

	/**
	 * Sestavení výchozí šablony uploaderu.
	 */
	abstract public function buildDefaultTemplate(): Html;

	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 */
	abstract public function buildFileContainerTemplate(): Html;

	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 */
	abstract public function buildFileError(): Html;
}
