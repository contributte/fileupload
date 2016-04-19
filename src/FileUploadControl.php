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
			\Nette\Forms\Container $container, $name, $maxFiles = 25, $maxFileSize = null
		) use ($class, $systemContainer, $uploadModel) {
			$component = new $class($name, $maxFiles, $maxFileSize); /** @var FileUploadControl $component */
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
	 */
	public static function getStyleSheet($basePath) {
		echo '<link rel="stylesheet" type="text/css" href="' . $basePath . '/fileupload/css/jquery.fileupload.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $basePath . '/fileupload/style.css">';
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
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/functions.js"></script>';
	}

	# --------------------------------------------------------------------
	# Control definition
	# --------------------------------------------------------------------
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
	 * FileUploadControl constructor.
	 * @param string $name Název inputu.
	 * @param int $maxFiles Maximální počet souborů.
	 * @param string $maxFileSize Maximální velikosti souboru.
	 */
	public function __construct($name, $maxFiles, $maxFileSize = null) {
		parent::__construct($name);
		$this->maxFiles = $maxFiles;
		if(is_null($maxFileSize)) {
			$this->maxFileSize = $this->parseIniSize(ini_get("upload_max_filesize"));
			$this->fileSizeString = ini_get("upload_max_filesize") ."B";
		} else {
			$this->maxFileSize = $this->parseIniSize($maxFileSize);
			$this->fileSizeString = $maxFileSize ."B";
		}
		$this->controller = new Model\UploadController($this);
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
	 */
	public function getContainer() {
		return $this->container;
	}

	/**
	 * @return int
	 */
	public function getMaxFiles() {
		return $this->maxFiles;
	}

	/**
	 * @param int $maxFiles
	 */
	public function setMaxFiles($maxFiles) {
		$this->maxFiles = $maxFiles;
	}

	/**
	 * @return Model\IUploadModel
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
	 */
	public function setUploadModel($uploadModel) {
		$this->uploadModel = $uploadModel;
	}

	/**
	 * @return int
	 */
	public function getMaxFileSize() {
		return $this->maxFileSize;
	}

	/**
	 * @param int $maxFileSize
	 */
	public function setMaxFileSize($maxFileSize) {
		$this->maxFileSize = $this->parseIniSize($maxFileSize);
	}

	/**
	 * @return \Nette\Caching\Cache
	 */
	public function getCache() {
		return $this->cache;
	}

	/**
	 * @return string
	 */
	public function getFileSizeString() {
		return $this->fileSizeString;
	}

	# --------------------------------------------------------------------
	# Methods
	# --------------------------------------------------------------------
	/**
	 * @return \Nette\Utils\Html
	 */
	public function getControl() {
		$this->setOption("rendered", TRUE);

		$container = \Nette\Utils\Html::el("div class='zet-fileupload-container'");
		$container->id = $this->getHtmlId() . "-container";
		$container->add($this->controller->getJavaScriptTemplate());
		$container->add($this->controller->getControlTemplate());

		return $container;
	}

	/**
	 * Vrátí nacachované hodnoty z controlleru.
	 * @return mixed|NULL
	 */
	public function getValue() {
		$files = $this->cache->load($this->getHtmlId());
		$this->cache->remove($this->getHtmlId());

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
