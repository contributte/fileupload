<?php declare(strict_types = 1);

namespace Zet\FileUpload;

use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\UnexpectedValueException;
use Zet\FileUpload\Exception\InvalidArgumentException;
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
					throw new InvalidArgumentException(sprintf('The passed model %s does not exist.', $v));
				}

				$interfaces = class_implements($v);
				if (!is_array($interfaces) || !in_array(IUploadModel::class, $interfaces, true)) {
					throw new InvalidStateException('The passed model is not an instance of ' . IUploadModel::class);
				}

				return $v;
			}),
			'fileFilter' => Expect::string()->nullable()->before(function ($v): string {
				if (!class_exists($v)) {
					throw new InvalidArgumentException(sprintf('The file filter class %s does not exist.', $v));
				}

				$interfaces = class_implements($v);
				if (!is_array($interfaces) || !in_array(IMimeTypeFilter::class, $interfaces, true)) {
					throw new UnexpectedValueException('The file filter class does not implement the interface ' . IMimeTypeFilter::class);
				}

				return $v;
			}),
			'renderer' => Expect::string(Html5Renderer::class)->before(function ($v): string {
				if (!class_exists($v)) {
					throw new InvalidArgumentException(sprintf('The form renderer class %s does not exist.', $v));
				}

				$interfaces = class_implements($v);
				if (!is_array($interfaces) || !in_array(IUploadRenderer::class, $interfaces, true)) {
					throw new InvalidStateException('The form renderer class does not implement the interface ' . IUploadRenderer::class);
				}

				return $v;
			}),
			'translator' => Expect::string()->nullable(),
			'autoTranslate' => Expect::bool(false),
			'messages' => Expect::structure([
				'maxFiles' => Expect::string('Maximum number of files je {maxFiles}.'),
				'maxSize' => Expect::string('Maximum file size je {maxSize}.'),
				'fileTypes' => Expect::string('Allowed file types are {fileTypes}.'),

				// PHP Errors
				'fileSize' => Expect::string('The file is too large.'),
				'partialUpload' => Expect::string('The file was only partially uploaded.'),
				'noFile' => Expect::string('No file uploaded.'),
				'tmpFolder' => Expect::string('Temporary folder is missing.'),
				'cannotWrite' => Expect::string('Failed to write file to disk.'),
				'stopped' => Expect::string('File upload interrupted.'),
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
