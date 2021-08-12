<?php declare(strict_types = 1);

namespace Zet\FileUpload;

use InvalidArgumentException;
use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\UnexpectedValueException;
use Zet\FileUpload\Filter\IMimeTypeFilter;
use Zet\FileUpload\Model\BaseUploadModel;
use Zet\FileUpload\Model\IUploadModel;
use Zet\FileUpload\Template\Renderer\Html5Renderer;
use Zet\FileUpload\Template\Renderer\IUploadRenderer;

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
	 * @return Schema
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'maxFiles' => Expect::int(25),
			'maxFileSize' => Expect::string()->nullable(),
			'uploadModel' => Expect::string(BaseUploadModel::class)->nullable()->before(function ($v): string {
				if (!class_exists($v)) {
					throw new InvalidArgumentException(sprintf('Předaný model %s neexistuje.', $v));
				}

				$interfaces = class_implements($v);
				if (!is_array($interfaces) || !in_array(IUploadModel::class, $interfaces, true)) {
					throw new InvalidStateException('Předaný model není instancí ' . IUploadModel::class);
				}

				return $v;
			}),
			'fileFilter' => Expect::string()->nullable()->before(function ($v): string {
				if (!class_exists($v)) {
					throw new InvalidArgumentException(sprintf('Třída pro filtrování souborů %s neexistuje.', $v));
				}

				$interfaces = class_implements($v);
				if (!is_array($interfaces) || !in_array(IMimeTypeFilter::class, $interfaces, true)) {
					throw new UnexpectedValueException('Třída pro filtrování souborů neimplementuje rozhraní ' . IMimeTypeFilter::class);
				}

				return $v;
			}),
			'renderer' => Expect::string(Html5Renderer::class)->before(function ($v): string {
				if (!class_exists($v)) {
					throw new InvalidArgumentException(sprintf('Třída pro vykreslování formuláře %s neexistuje.', $v));
				}

				$interfaces = class_implements($v);
				if (!is_array($interfaces) || !in_array(IUploadRenderer::class, $interfaces, true)) {
					throw new InvalidStateException('Třída pro vykreslování formuláře neimplementuje rozhraní ' . IUploadRenderer::class);
				}

				return $v;
			}),
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
			$this->getContainerBuilder()->getByType(Container::class),
		$this->getConfig(),
		]);
	}

}
