<?php declare(strict_types = 1);

namespace Contributte\FileUpload;

use Contributte\FileUpload\Exception\InvalidValueException;
use Contributte\FileUpload\Model\BaseUploadModel;
use Contributte\FileUpload\Model\DefaultFile;
use Contributte\FileUpload\Model\IUploadModel;
use Contributte\FileUpload\Model\UploadController;
use Nette;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\DI\Container;
use Nette\Forms\Controls\UploadControl;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\InvalidStateException;
use Nette\Localization\Translator;
use Nette\Utils\Html;
use stdClass;

/**
 * Class FileUploadControl
 */
class FileUploadControl extends UploadControl
{

	// --------------------------------------------------------------------
	// Control definition
	// --------------------------------------------------------------------
	/**
	 * Povolí nahrávat pouze obrázky png, jpeg, jpg, gif.
	 */
	public const FILTER_IMAGES = 'Contributte\FileUpload\Filter\ImageFilter';

	/**
	 * Povolí nahrávat pouze dokumenty typu txt, doc, docx, xls, xlsx, ppt, pptx, pdf.
	 */
	public const FILTER_DOCUMENTS = 'Contributte\FileUpload\Filter\DocumentFilter';

	/**
	 * Povolí nahrávat soubory zip, tar, rar, 7z.
	 */
	public const FILTER_ARCHIVE = 'Contributte\FileUpload\Filter\ArchiveFilter';

	/**
	 * Povolí nahrávat pouze soubory mp3, ogg, aiff.
	 */
	public const FILTER_AUDIO = 'Contributte\FileUpload\Filter\AudioFilter';

	private Container $container;

	private Cache $cache;

	private int $maxFiles;

	private ?int $maxFileSize = null;

	private string $fileSizeString;

	private UploadController $controller;

	/** @var class-string<IUploadModel>|null */
	private $uploadModel;

	/**
	 * Třída pro filtrování nahrávaných souborů.
	 */
	private ?string $fileFilter = null;

	/**
	 * Pole vlastních definovaných parametrů.
	 *
	 * @var array<mixed>
	 */
	private array $params = [];

	private string $renderer;

	private string $token;

	/** @var DefaultFile[] */
	private array $defaultFiles = [];

	/**
	 * Seznam chybových hlášek.
	 * Chyby uploaderu:
	 * - maxFiles
	 * - maxSize
	 * - fileTypes
	 *
	 * Chyby v PHP:
	 * - fileSize
	 * - partialUpload
	 * - noFile
	 * - tmpFolder
	 * - cannotWrite
	 * - stopped
	 *
	 * @var string[]
	 */
	private array $messages = [];

	/**
	 * Automaticky překládat všechny chybové zprávy?
	 */
	private bool $autoTranslate = false;

	/**
	 * Pole vlastních hodnot pro konfiguraci uploaderu.
	 *
	 * @var array<mixed>
	 */
	private array $uploadSettings = [];

	/**
	 * @param string $name        Název inputu.
	 * @param int    $maxFiles    Maximální počet souborů.
	 * @param string $maxFileSize Maximální velikosti souboru.
	 */
	public function __construct(string $name, int $maxFiles, ?string $maxFileSize = null)
	{
		parent::__construct($name);

		$this->maxFiles = $maxFiles;
		if ($maxFileSize === null) {
			$this->fileSizeString = ini_get('upload_max_filesize') . 'B';
			$this->maxFileSize = $this->parseIniSize((string) ini_get('upload_max_filesize'));
		} else {
			$this->fileSizeString = $maxFileSize . 'B';
			$this->maxFileSize = $this->parseIniSize($maxFileSize);
		}

		$this->controller = new Model\UploadController($this);
		$this->monitor(Form::class, function (Form $form): void {
			$form->addComponent($this->controller, 'uploadController' . ucfirst($this->name));
		});
		$this->token = uniqid();
	}

	// --------------------------------------------------------------------
	// Registration
	// --------------------------------------------------------------------

	/**
	 * Delete cache
	 */
	public function __destruct()
	{
		$this->cache->remove($this->getTokenizedCacheName($this->token));
	}

	/**
	 * @static
	 * @param array<mixed>|stdClass $configuration
	 */
	public static function register(Container $systemContainer, array|stdClass $configuration): void
	{
		if (is_object($configuration)) {
			$configuration = json_decode((string) json_encode($configuration), true);
		}

		$class = self::class;
		Nette\Forms\Container::extensionMethod('addFileUpload', function (
			Nette\Forms\Container $container,
			$name,
			$maxFiles = null,
			$maxFileSize = null
		) use (
			$class,
			$systemContainer,
			$configuration
		): self {
			$maxFiles ??= $configuration['maxFiles'];
			$maxFileSize ??= $configuration['maxFileSize'];

			/** @var FileUploadControl $component */
			$component = new $class($name, $maxFiles, $maxFileSize);
			$component->setContainer($systemContainer);
			$component->setUploadModel($configuration['uploadModel']);
			$component->setFileFilter($configuration['fileFilter']);
			$component->setRenderer($configuration['renderer']);

			if ($configuration['translator'] === null) {
				$translator = $systemContainer->getByType(Translator::class, false);
				$component->setTranslator($translator);
			} else {
				$component->setTranslator($configuration['translator']);
			}

			$component->setAutoTranslate($configuration['autoTranslate']);
			$component->setMessages($configuration['messages']);
			$component->setUploadSettings($configuration['uploadSettings']);

			$container->addComponent($component, $name);

			return $component;
		});
	}

	/**
	 * Vloží CSS do stránky.
	 *
	 * @static
	 */
	public static function getHead(string $basePath): void
	{
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/functions.js"></script>';
	}

	/**
	 * Vloží skripty do stránky.
	 *
	 * @static
	 */
	public static function getScripts(string $basePath): void
	{
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/vendor/jquery.ui.widget.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/load-image.all.min.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload-process.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload-image.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload-video.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/controller.js"></script>';
	}

	/**
	 * @internal
	 */
	public function setContainer(Container $container): void
	{
		$this->container = $container;
		/** @noinspection PhpParamsInspection */
		$this->cache = new Cache($this->container->getByType('Nette\Caching\IStorage'));
		/** @noinspection PhpParamsInspection */
		$this->controller->setRequest($container->getByType('\Nette\Http\Request'));
	}

	/**
	 * @internal
	 */
	public function getContainer(): Container
	{
		return $this->container;
	}

	/**
	 * @internal
	 */
	public function getMaxFiles(): int
	{
		return $this->maxFiles;
	}

	public function setMaxFiles(int $maxFiles): self
	{
		$this->maxFiles = $maxFiles;

		return $this;
	}

	/**
	 * @internal
	 */
	public function getUploadModel(): IUploadModel
	{
		if ($this->uploadModel === null) {
			return new BaseUploadModel();
		} else {
			$model = $this->container->getByType($this->uploadModel);
			if ($model instanceof IUploadModel) {
				return $model;
			} else {
				throw new InvalidStateException(
					'The passed model is not an instance of \\Zet\\FileUpload\\Model\\IUploadModel.'
				);
			}
		}
	}

	/**
	 * @param class-string<IUploadModel> $uploadModel
	 */
	public function setUploadModel(string $uploadModel): self
	{
		$this->uploadModel = $uploadModel;

		return $this;
	}

	/**
	 * @internal
	 */
	public function getMaxFileSize(): ?int
	{
		return $this->maxFileSize;
	}

	public function setMaxFileSize(?string $maxFileSize): self
	{
		if (isset($maxFileSize)) {
			$this->maxFileSize = $this->parseIniSize($maxFileSize);
		}

		return $this;
	}

	public function getCache(): Cache
	{
		return $this->cache;
	}

	/**
	 * @internal
	 */
	public function getFileSizeString(): string
	{
		return $this->fileSizeString;
	}

	/**
	 * @internal
	 */
	public function getFileFilter(): ?string
	{
		return $this->fileFilter;
	}

	/**
	 * Nastaví třídu pro filtrování nahrávaných souborů.
	 */
	public function setFileFilter(?string $fileFilter): self
	{
		$this->fileFilter = $fileFilter;

		return $this;
	}

	/**
	 * Vrátí název pro frontu s tokenem.
	 *
	 * @internal
	 */
	public function getTokenizedCacheName(string $token): string
	{
		return $this->getHtmlId() . '-' . $token;
	}

	/**
	 * Vrátí identifikační token.
	 *
	 * @internal
	 */
	public function getToken(): string
	{
		return $this->token;
	}

	/**
	 * Nastavení vlastních parametrů k uploadovanému souboru.
	 *
	 * @param array<mixed> $params
	 */
	public function setParams(array $params): self
	{
		$this->params = $params;

		return $this;
	}

	/**
	 * @return array<mixed>
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	public function setRenderer(string $renderer): self
	{
		$this->renderer = $renderer;

		return $this;
	}

	public function getRenderer(): string
	{
		return $this->renderer;
	}

	/**
	 * @return DefaultFile[]
	 */
	public function getDefaultFiles(): array
	{
		return $this->defaultFiles;
	}

	/**
	 * @param DefaultFile[] $defaultFiles
	 */
	public function setDefaultFiles(array $defaultFiles): self
	{
		$this->defaultFiles = $defaultFiles;

		return $this;
	}

	public function addDefaultFile(DefaultFile $defaultFile): self
	{
		$this->defaultFiles[] = $defaultFile;

		return $this;
	}

	/**
	 * @param array<mixed> $messages
	 */
	public function setMessages(array $messages): self
	{
		$this->messages = $messages;

		return $this;
	}

	public function setMessage(string $index, string $message): self
	{
		$this->messages[$index] = $message;

		return $this;
	}

	/**
	 * @return array<string>
	 */
	public function getMessages(): array
	{
		return $this->messages;
	}

	public function isAutoTranslate(): bool
	{
		return $this->autoTranslate;
	}

	public function setAutoTranslate(bool $autoTranslate): self
	{
		$this->autoTranslate = $autoTranslate;

		return $this;
	}

	/**
	 * @return array<mixed>
	 */
	public function getUploadSettings(): array
	{
		return $this->uploadSettings;
	}

	/**
	 * @param array<mixed> $uploadSettings
	 */
	public function setUploadSettings(array $uploadSettings): self
	{
		$this->uploadSettings = $uploadSettings;

		return $this;
	}

	public function addUploadSettings(string $name, mixed $value): self
	{
		$this->uploadSettings[$name] = $value;

		return $this;
	}

	// --------------------------------------------------------------------
	// Methods
	// --------------------------------------------------------------------

	/**
	 * Získání identifikačního tokenu.
	 */
	public function loadHttpData(): void
	{
		parent::loadHttpData();

		/** @var Request $request */
		$request = $this->getContainer()->getByType('\Nette\Http\Request');
		$this->token = $request->getPost($this->getHtmlName() . '-token');
	}

	/**
	 * @throws InvalidValueException
	 */
	public function getControl(): Html
	{
		$this->checkSettings();

		$this->setOption('rendered', true);

		$container = Html::el("div class='zet-fileupload-container'");
		$container->id = $this->getHtmlId() . '-container';

		$token = Html::el("input type='hidden' value='" . $this->token . "'");
		$token->addAttributes(['name' => $this->getHtmlName() . '-token']);

		$container->addHtml($token);
		$container->addHtml($this->controller->getJavaScriptTemplate());
		$container->addHtml($this->controller->getControlTemplate());

		return $container;
	}

	/**
	 * Vrátí nacachované hodnoty z controlleru.
	 *
	 * @return array<FileUpload>|NULL
	 */
	public function getValue(): ?array
	{
		$value = $this->cache->load($this->getTokenizedCacheName($this->token));

		return is_array($value) ? $value : null;
	}

	/**
	 * Ověření nastavených direktiv, zda nepřekročují nastavení serveru.
	 *
	 * @throws InvalidValueException
	 */
	private function checkSettings(): void
	{
		$postMaxSize = $this->parseIniSize($postMaxSizeString = (string) ini_get('post_max_size'));
		$iniMaxFileSize = $this->parseIniSize($iniMaxFileSizeString = (string) ini_get('upload_max_filesize'));

		if ($this->maxFileSize > $postMaxSize) {
			throw new InvalidValueException(
				sprintf(
					'The setting for the maximum file size is larger than allowed by the directive `post_max_size` (%s).',
					$postMaxSizeString
				)
			);
		} elseif ($this->maxFileSize > $iniMaxFileSize) {
			throw new InvalidValueException(
				sprintf(
					'The setting for the maximum file size is larger than allowed by the directive `upload_max_filesize` (%s).',
					$iniMaxFileSizeString
				)
			);
		}
	}// --------------------------------------------------------------------
// Setters \ Getters
// --------------------------------------------------------------------

	/**
	 * Parses ini size
	 */
	private function parseIniSize(string $value): int
	{
		$units = ['k' => 1024, 'm' => 1048576, 'g' => 1073741824];
		$unit = strtolower(substr($value, -1));
		if (is_numeric($unit) || !isset($units[$unit])) {
			return (int) $value;
		}

		return ((int) $value) * $units[$unit];
	}

}
