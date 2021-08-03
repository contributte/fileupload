<?php declare(strict_types = 1);

namespace Zet\FileUpload;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

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
	 * @var array<mixed>
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'maxFiles' => Expect::int(25),
			'maxFileSize' => Expect::string()->nullable(),
			'uploadModel' => Expect::string()->nullable(),
			'fileFilter' => Expect::string()->nullable(),
			'renderer' => Expect::string('\Zet\FileUpload\Template\Renderer\Html5Renderer'),
			'translator' => Expect::string()->nullable(),
			'autoTranslate' => Expect::bool(false),
			'messages' => Expect::structure([
				'maxFiles' => Expect::string('Maximální počet souborů je {maxFiles}.'),
				'maxSize' => Expect::string('Maximální velikost souboru je {maxSize}.'),
				'fileTypes' => Expect::string('Povolené typy souborů jsou {fileTypes}.'),

				// PHP Errors
				'fileSize' => Expect::string('Soubor je příliš veliký.'),
				'partialUpload' => Expect::string('Soubor byl nahrán pouze částěčně.'),
				'noFile' => Expect::string('Nebyl nahrán žádný soubor.'),
				'tmpFolder' => Expect::string('Chybí dočasná složka.'),
				'cannotWrite' => Expect::string('Nepodařilo se zapsat soubor na disk.'),
				'stopped' => Expect::string('Nahrávání souboru bylo přerušeno.'),
			]),
			'uploadSettings' => Expect::array(),
		]);
	}

	public function afterCompile(ClassType $class): void
	{
		$init = $class->methods['initialize'];

		$init->addBody('\Zet\FileUpload\FileUploadControl::register($this->getService(?), ?);', [
			$this->getContainerBuilder()->getByType('\Nette\DI\Container'),
		$this->getConfig(),
		]);
	}

}
