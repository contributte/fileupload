<?php

namespace Zet\FileUpload;

/**
 * Class FileUploadControl
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload
 */
class FileUploadControl extends \Nette\Forms\Controls\UploadControl {

	# --------------------------------------------------------------------
	# Registration
	# --------------------------------------------------------------------
	/**
	 * @static
	 * @param $systemContainer
	 * @param string $uploadModel
	 */
	public static function register($systemContainer, $uploadModel = NULL) {
		$class = __CLASS__;
		\Nette\Forms\Container::extensionMethod("addFileUpload", function (
			\Nette\Forms\Container $container, $name, $maxFiles = 25, $maxFileSize = NULL
		) use ($class, $systemContainer, $uploadModel) {
			$component = new $class($name, $maxFiles, $maxFileSize);
			/** @var FileUploadControl $component */
			$component->setContainer($systemContainer);
			$component->setUploadModel($uploadModel);
			$container->addComponent($component, $name);

			return $component;
		});
	}

	/**
	 * Vloží CSS do stránky.
	 * @static
	 * @param string $basePath
	 * @throws \Nette\DeprecatedException Use FileUploadControl::getHead()
	 */
	public static function getStyleSheet($basePath) {
		throw new \Nette\DeprecatedException("Use FileUploadControl::getHead() instead.");
		
		echo '<link rel="stylesheet" type="text/css" href="' . $basePath . '/fileupload/css/jquery.fileupload.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $basePath . '/fileupload/style.css">';
	}
	
	/**
	 * Vloží CSS do stránky.
	 * @static
	 * @param string $basePath
	 */
	public static function getHead($basePath) {
		echo '<link rel="stylesheet" type="text/css" href="' . $basePath . '/fileupload/css/jquery.fileupload.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $basePath . '/fileupload/style.css">';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/functions.js"></script>';
	}

	/**
	 * Vloží skripty do stránky.
	 * @static
	 * @param string $basePath
	 */
	public static function getScripts($basePath) {
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/vendor/jquery.ui.widget.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/load-image.all.min.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/canvas-to-blob.min.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.iframe-transport.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload-process.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload-image.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload-video.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/controller.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/ui/uiRenderer.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/ui/full.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/ui/minimal.js"></script>';
	}

	# --------------------------------------------------------------------
	# Control definition
	# --------------------------------------------------------------------
	/**
	 * Povolí nahrávat pouze obrázky png, jpeg, jpg, gif.
	 * @var string
	 */
	const FILTER_IMAGES = 'Zet\FileUpload\Filter\ImageFilter';

	/**
	 * Povolí nahrávat pouze dokumenty typu txt, doc, docx, xls, xlsx, ppt, pptx, pdf.
	 * @var string
	 */
	const FILTER_DOCUMENTS = 'Zet\FileUpload\Filter\DocumentFilter';

	/**
	 * Povolí nahrávat soubory zip, tar, rar, 7z.
	 * @var string
	 */
	const FILTER_ARCHIVE = 'Zet\FileUpload\Filter\ArchiveFilter';

	/**
	 * Povolí nahrávat pouze soubory mp3, ogg, aiff.
	 * @var string
	 */
	const FILTER_AUDIO = 'Zet\FileUpload\Filter\AudioFilter';
	
	/**
	 * Plnohodntné a detailní rozhraní pro nahrávání souborů.
	 * @var int
	 */
	const UI_FULL = 1;
	
	/**
	 * Minimální rozhraní.
	 * @var int
	 */
	const UI_MINIMAL = 2;

	/**
	 * @var \Nette\DI\Container
	 */
	private $container;

	/**
	 * @var \Nette\Caching\Cache
	 */
	private $cache;

	/**
	 * @var int
	 */
	private $maxFiles;

	/**
	 * @var int
	 */
	private $maxFileSize;

	/**
	 * @var string
	 */
	private $fileSizeString;

	/**
	 * @var \Zet\FileUpload\Model\UploadController
	 */
	private $controller;

	/**
	 * @var string
	 */
	private $uploadModel;
	
	/**
	 * @var int
	 */
	private $uiMode = self::UI_FULL;

	/**
	 * Třída pro filtrování nahrávaných souborů.
	 * @var string
	 */
	private static $fileFilter;

	/**
	 * @var string
	 */
	private $token;

	/**
	 * FileUploadControl constructor.
	 * @param string $name Název inputu.
	 * @param int $maxFiles Maximální počet souborů.
	 * @param string $maxFileSize Maximální velikosti souboru.
	 */
	public function __construct($name, $maxFiles, $maxFileSize = NULL) {
		parent::__construct($name);
		$this->maxFiles = $maxFiles;
		if(is_null($maxFileSize)) {
			$this->maxFileSize = $this->parseIniSize(ini_get("upload_max_filesize"));
			$this->fileSizeString = ini_get("upload_max_filesize") . "B";
		} else {
			$this->maxFileSize = $this->parseIniSize($maxFileSize);
			$this->fileSizeString = $maxFileSize . "B";
		}
		$this->controller = new Model\UploadController($this);
		$this->token = uniqid();
	}

	/**
	 * @param $form
	 */
	protected function attached($form) {
		parent::attached($form);
		$this->form->addComponent($this->controller, "uploadController");
	}

	# --------------------------------------------------------------------
	# Setters \ Getters
	# --------------------------------------------------------------------
	/**
	 * @param \Nette\DI\Container $container
	 * @internal
	 */
	public function setContainer($container) {
		$this->container = $container;
		/** @noinspection PhpParamsInspection */
		$this->cache = new \Nette\Caching\Cache($this->container->getByType('Nette\Caching\IStorage'));
		/** @noinspection PhpParamsInspection */
		$this->controller->setRequest($container->getByType('\Nette\Http\Request'));
	}

	/**
	 * @return \Nette\DI\Container
	 * @internal
	 */
	public function getContainer() {
		return $this->container;
	}

	/**
	 * @return int
	 * @internal
	 */
	public function getMaxFiles() {
		return $this->maxFiles;
	}
	
	/**
	 * @param int $maxFiles
	 * @return $this
	 */
	public function setMaxFiles($maxFiles) {
		$this->maxFiles = $maxFiles;
		return $this;
	}

	/**
	 * @return Model\IUploadModel
	 * @internal
	 */
	public function getUploadModel() {
		if(is_null($this->uploadModel)) {
			return new Model\BaseUploadModel();
		} else {
			$model = $this->container->getByType($this->uploadModel);
			if($model instanceof Model\IUploadModel) {
				return $model;
			} else {
				throw new \Nette\InvalidStateException(
					"Předaný model není instancí \\Zet\\FileUpload\\Model\\IUploadModel."
				);
			}
		}
	}
	
	/**
	 * @param string $uploadModel
	 * @return $this
	 */
	public function setUploadModel($uploadModel) {
		$this->uploadModel = $uploadModel;
		return $this;
	}
	
	/**
	 * @param int $mode
	 * @return $this
	 */
	public function setUIMode($mode) {
		$this->uiMode = $mode;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getUIMode() {
		return $this->uiMode;
	}

	/**
	 * @return int
	 * @internal
	 */
	public function getMaxFileSize() {
		return $this->maxFileSize;
	}
	
	/**
	 * @param int $maxFileSize
	 * @return $this
	 */
	public function setMaxFileSize($maxFileSize) {
		$this->maxFileSize = $this->parseIniSize($maxFileSize);
		return $this;
	}

	/**
	 * @return \Nette\Caching\Cache
	 */
	public function getCache() {
		return $this->cache;
	}

	/**
	 * @return string
	 * @internal
	 */
	public function getFileSizeString() {
		return $this->fileSizeString;
	}

	/**
	 * @return string
	 * @internal
	 */
	public function getFileFilter() {
		return self::$fileFilter;
	}
	
	/**
	 * Nastaví třídu pro filtrování nahrávaných souborů.
	 * @param string $fileFilter
	 * @return $this
	 */
	public function setFileFilter($fileFilter) {
		self::$fileFilter = $fileFilter;
		return $this;
	}
	
	/**
	 * Vrátí název pro frontu s tokenem.
	 * @param string $token
	 * @return string
	 * @internal
	 */
	public function getTokenizedCacheName($token) {
		return $this->getHtmlId() . "-" . $token;
	}

	/**
	 * Vrátí identifikační token.
	 * @return string
	 * @internal
	 */
	public function getToken() {
		return $this->token;
	}

	# --------------------------------------------------------------------
	# Methods
	# --------------------------------------------------------------------
	/**
	 * Získání identifikačního tokenu.
	 */
	public function loadHttpData() {
		parent::loadHttpData();
		$request = $this->getContainer()->getByType('\Nette\Http\Request'); /** @var \Nette\Http\Request $request */
		$this->token = $request->getPost($this->getHtmlName() ."-token");
	}

	/**
	 * @return \Nette\Utils\Html
	 */
	public function getControl() {
		$this->setOption("rendered", TRUE);

		$container = \Nette\Utils\Html::el("div class='zet-fileupload-container'");
		$container->id = $this->getHtmlId() . "-container";

		$token = \Nette\Utils\Html::el("input type='hidden' value='" . $this->token . "'");
		$token->addAttributes(["name" => $this->getHtmlName() ."-token"]);
		
		if(method_exists(\Nette\Utils\Html::class, "addHtml")) {
			$container->addHtml($token);
			$container->addHtml($this->controller->getJavaScriptTemplate());
			$container->addHtml($this->controller->getControlTemplate());
		} else { // pro starší nette
			$container->add($token);
			$container->add($this->controller->getJavaScriptTemplate());
			$container->add($this->controller->getControlTemplate());
		}

		return $container;
	}

	/**
	 * Vrátí nacachované hodnoty z controlleru.
	 * @return mixed|NULL
	 */
	public function getValue() {
		$files = $this->cache->load($this->getTokenizedCacheName($this->token));
		$this->cache->remove($this->getTokenizedCacheName($this->token));

		return $files;
	}

	/**
	 * Parses ini size
	 * @param string $value
	 * @return int
	 */
	private function parseIniSize($value) {
		$units = array('k' => 1024, 'm' => 1048576, 'g' => 1073741824);
		$unit = strtolower(substr($value, -1));
		if(is_numeric($unit) || !isset($units[ $unit ])) {
			return $value;
		}

		return ((int) $value) * $units[ $unit ];
	}

}
