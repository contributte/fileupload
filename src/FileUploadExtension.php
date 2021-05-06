<?php declare(strict_types = 1);

namespace Zet\FileUpload;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

/**
 * Class FileUploadExtension
 *
 * @author Zechy <email@zechy.cz>
 */
final class FileUploadExtension extends CompilerExtension
{

	/**
	 * Výchozí konfigurační hodnoty.
	 *
	 * @var array
	 */
	private $defaults = [
		'maxFiles' => 25,
		'maxFileSize' => null,
		'uploadModel' => null,
		'fileFilter' => null,
		'renderer' => '\Zet\FileUpload\Template\Renderer\Html5Renderer',
		'translator' => null,
		'autoTranslate' => false,
		'messages' => [
			'maxFiles' => 'Maximální počet souborů je {maxFiles}.',
			'maxSize' => 'Maximální velikost souboru je {maxSize}.',
			'fileTypes' => 'Povolené typy souborů jsou {fileTypes}.',

			// PHP Errors
			'fileSize' => 'Soubor je příliš veliký.',
			'partialUpload' => 'Soubor byl nahrán pouze částěčně.',
			'noFile' => 'Nebyl nahrán žádný soubor.',
			'tmpFolder' => 'Chybí dočasná složka.',
			'cannotWrite' => 'Nepodařilo se zapsat soubor na disk.',
			'stopped' => 'Nahrávání souboru bylo přerušeno.',
		],
		'uploadSettings' => [],
	];

	/**
	 * Konfigurace nastavená uživatelem.
	 *
	 * @var array
	 */
	private $configuration = [];

	public function loadConfiguration()
	{
		$this->configuration = $this->getConfig($this->defaults);
	}

	/**
	 * @param ClassType $class
	 */
	public function afterCompile(ClassType $class)
	{
		$init = $class->methods['initialize'];

		$init->addBody('\Zet\FileUpload\FileUploadControl::register($this->getService(?), ?);', [
			$this->getContainerBuilder()->getByType('\Nette\DI\Container'),
		$this->configuration,
		]);
	}

}
