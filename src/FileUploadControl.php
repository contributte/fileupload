<?php

namespace Zet\FileUpload;

use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Tracy\Debugger;
use Zet\FileUpload\Model\DefaultFile;

/**
 * Class FileUploadControl
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload
 */
class FileUploadControl extends \Nette\Forms\Controls\UploadControl {
	
	# --------------------------------------------------------------------
	# Registration
	# --------------------------------------------------------------------
	/**
	 * @static
	 * @param       $systemContainer
	 * @param array $configuration
	 */
	public static function register(\Nette\DI\Container $systemContainer, $configuration = []) {
		$class = __CLASS__;
		\Nette\Forms\Container::extensionMethod("addFileUpload", function(
			\Nette\Forms\Container $container, $name, $maxFiles = null, $maxFileSize = null
		) use ($class, $systemContainer, $configuration) {
			$maxFiles = is_null($maxFiles) ? $configuration["maxFiles"] : $maxFiles;
			$maxFileSize = is_null($maxFileSize) ? $configuration["maxFileSize"] : $maxFileSize;
			
			/** @var FileUploadControl $component */
			$component = new $class($name, $maxFiles, $maxFileSize);
			$component->setContainer($systemContainer);
			$component->setUploadModel($configuration["uploadModel"]);
			$component->setFileFilter($configuration["fileFilter"]);
			$component->setRenderer($configuration["renderer"]);
			
			if($configuration["translator"] === null) {
				$translator = $systemContainer->getByType(ITranslator::class);
				$component->setTranslator($translator);
			} else {
				$component->setTranslator($configuration["translator"]);
			}
			
			$component->setAutoTranslate($configuration["autoTranslate"]);
			$component->setMessages($configuration["messages"]);
			$component->setUploadSettings($configuration["uploadSettings"]);
			
			$container->addComponent($component, $name);
			
			return $component;
		});
	}
	
	/**
	 * Vloží CSS do stránky.
	 *
	 * @static
	 * @param string $basePath
	 */
	public static function getHead($basePath) {
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/functions.js"></script>';
	}
	
	/**
	 * Vloží skripty do stránky.
	 *
	 * @static
	 * @param string $basePath
	 */
	public static function getScripts($basePath) {
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/vendor/jquery.ui.widget.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/load-image.all.min.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload-process.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload-image.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/js/jquery.fileupload-video.js"></script>';
		echo '<script type="text/javascript" src="' . $basePath . '/fileupload/controller.js"></script>';
	}
	
	# --------------------------------------------------------------------
	# Control definition
	# --------------------------------------------------------------------
	/**
	 * Povolí nahrávat pouze obrázky png, jpeg, jpg, gif.
	 *
	 * @var string
	 */
	const FILTER_IMAGES = 'Zet\FileUpload\Filter\ImageFilter';
	
	/**
	 * Povolí nahrávat pouze dokumenty typu txt, doc, docx, xls, xlsx, ppt, pptx, pdf.
	 *
	 * @var string
	 */
	const FILTER_DOCUMENTS = 'Zet\FileUpload\Filter\DocumentFilter';
	
	/**
	 * Povolí nahrávat soubory zip, tar, rar, 7z.
	 *
	 * @var string
	 */
	const FILTER_ARCHIVE = 'Zet\FileUpload\Filter\ArchiveFilter';
	
	/**
	 * Povolí nahrávat pouze soubory mp3, ogg, aiff.
	 *
	 * @var string
	 */
	const FILTER_AUDIO = 'Zet\FileUpload\Filter\AudioFilter';
	
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
	 * Třída pro filtrování nahrávaných souborů.
	 *
	 * @var string
	 */
	private $fileFilter;
	
	/**
	 * Pole vlastních definovaných parametrů.
	 *
	 * @var array
	 */
	private $params = [];
	
	/**
	 * @var string
	 */
	private $renderer;
	
	/**
	 * @var string
	 */
	private $token;
	
	/**
	 * @var DefaultFile[]
	 */
	private $defaultFiles = [];
	
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
	private $messages = [];
	
	/**
	 * Automaticky překládat všechny chybové zprávy?
	 *
	 * @var bool
	 */
	private $autoTranslate = false;
	
	/**
	 * Pole vlastních hodnot pro konfiguraci uploaderu.
	 *
	 * @var array
	 */
	private $uploadSettings = [];
	
	/**
	 * FileUploadControl constructor.
	 *
	 * @param string $name        Název inputu.
	 * @param int    $maxFiles    Maximální počet souborů.
	 * @param string $maxFileSize Maximální velikosti souboru.
	 */
	public function __construct($name, $maxFiles, $maxFileSize = null) {
		parent::__construct($name);
		$this->maxFiles = $maxFiles;
		if(is_null($maxFileSize)) {
			$this->fileSizeString = ini_get("upload_max_filesize") . "B";
			$this->maxFileSize = $this->parseIniSize(ini_get("upload_max_filesize"));
		} else {
			$this->fileSizeString = $maxFileSize . "B";
			$this->maxFileSize = $this->parseIniSize($maxFileSize);
		}
		$this->controller = new Model\UploadController($this);
		$this->token = uniqid();
	}
	
	/**
	 * Ověření nastavených direktiv, zda nepřekročují nastavení serveru.
	 *
	 * @throws \Zet\FileUpload\InvalidValueException
	 */
	private function checkSettings() {
		$postMaxSize = $this->parseIniSize($postMaxSizeString = ini_get("post_max_size"));
		$iniMaxFileSize = $this->parseIniSize($iniMaxFileSizeString = ini_get("upload_max_filesize"));
		
		if($this->maxFileSize > $postMaxSize) {
			throw new InvalidValueException(
				sprintf(
					"Nastavení pro maximální velikost souboru je větší, než dovoluje direktiva `post_max_size` (%s).",
					$postMaxSizeString
				)
			);
		} else if($this->maxFileSize > $iniMaxFileSize) {
			throw new InvalidValueException(
				sprintf(
					"Nastavení pro maximální velikost souboru je větší, než dovoluje direktiva `upload_max_filesize` (%s).",
					$iniMaxFileSizeString
				)
			);
		}
	}
	
	/**
	 * @param Form $form
	 */
	protected function attached($form) {
		parent::attached($form);
		$this->form->addComponent($this->controller, "uploadController" . ucfirst($this->name));
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
		return $this->fileFilter;
	}
	
	/**
	 * Nastaví třídu pro filtrování nahrávaných souborů.
	 *
	 * @param string $fileFilter
	 * @return $this
	 */
	public function setFileFilter($fileFilter) {
		$this->fileFilter = $fileFilter;
		
		return $this;
	}
	
	/**
	 * Vrátí název pro frontu s tokenem.
	 *
	 * @param string $token
	 * @return string
	 * @internal
	 */
	public function getTokenizedCacheName($token) {
		return $this->getHtmlId() . "-" . $token;
	}
	
	/**
	 * Vrátí identifikační token.
	 *
	 * @return string
	 * @internal
	 */
	public function getToken() {
		return $this->token;
	}
	
	/**
	 * Nastavení vlastních parametrů k uploadovanému souboru.
	 *
	 * @param array $params
	 * @return FileUploadControl
	 */
	public function setParams(array $params) {
		$this->params = $params;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}
	
	/**
	 * @param string $renderer
	 * @return FileUploadControl
	 */
	public function setRenderer($renderer) {
		$this->renderer = $renderer;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getRenderer() {
		return $this->renderer;
	}
	
	/**
	 * @return DefaultFile[]
	 */
	public function getDefaultFiles() {
		return $this->defaultFiles;
	}
	
	/**
	 * @param DefaultFile[] $defaultFiles
	 * @return FileUploadControl
	 */
	public function setDefaultFiles($defaultFiles) {
		$this->defaultFiles = $defaultFiles;
		
		return $this;
	}
	
	/**
	 * @param DefaultFile $defaultFile
	 * @return FileUploadControl
	 */
	public function addDefaultFile($defaultFile) {
		$this->defaultFiles[] = $defaultFile;
		
		return $this;
	}
	
	/**
	 * @param string[] $messages
	 * @return FileUploadControl
	 */
	public function setMessages(array $messages) {
		$this->messages = $messages;
		
		return $this;
	}
	
	/**
	 * @param string $index
	 * @param string $message
	 * @return FileUploadControl
	 */
	public function setMessage($index, $message) {
		$this->messages[ $index ] = $message;
		
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	public function getMessages() {
		return $this->messages;
	}
	
	/**
	 * @return bool
	 */
	public function isAutoTranslate() {
		return $this->autoTranslate;
	}
	
	/**
	 * @param bool $autoTranslate
	 * @return FileUploadControl
	 */
	public function setAutoTranslate($autoTranslate) {
		$this->autoTranslate = $autoTranslate;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getUploadSettings() {
		return $this->uploadSettings;
	}
	
	/**
	 * @param array $uploadSettings
	 * @return FileUploadControl
	 */
	public function setUploadSettings($uploadSettings) {
		$this->uploadSettings = $uploadSettings;
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @param mixed  $value
	 * @return FileUploadControl
	 */
	public function addUploadSettings($name, $value) {
		$this->uploadSettings[ $name ] = $value;
		
		return $this;
	}
	
	# --------------------------------------------------------------------
	# Methods
	# --------------------------------------------------------------------
	/**
	 * Získání identifikačního tokenu.
	 */
	public function loadHttpData() {
		parent::loadHttpData();
		
		/** @var \Nette\Http\Request $request */
		$request = $this->getContainer()->getByType('\Nette\Http\Request');
		$this->token = $request->getPost($this->getHtmlName() . "-token");
	}
	
	/**
	 * @return \Nette\Utils\Html
	 * @throws InvalidValueException
	 */
	public function getControl() {
		$this->checkSettings();
		
		$this->setOption("rendered", true);
		
		$container = \Nette\Utils\Html::el("div class='zet-fileupload-container'");
		$container->id = $this->getHtmlId() . "-container";
		
		$token = \Nette\Utils\Html::el("input type='hidden' value='" . $this->token . "'");
		$token->addAttributes(["name" => $this->getHtmlName() . "-token"]);
		
		$container->addHtml($token);
		$container->addHtml($this->controller->getJavaScriptTemplate());
		$container->addHtml($this->controller->getControlTemplate());
		
		return $container;
	}
	
	/**
	 * Vrátí nacachované hodnoty z controlleru.
	 *
	 * @return mixed|NULL
	 */
	public function getValue() {
		$files = $this->cache->load($this->getTokenizedCacheName($this->token));
		
		return $files;
	}
	
	/**
	 * Delete cache
	 */
	public function __destruct() {
		$this->cache->remove($this->getTokenizedCacheName($this->token));
	}
	
	/**
	 * Parses ini size
	 *
	 * @param string $value
	 * @return int
	 */
	private function parseIniSize($value) {
		$units = ['k' => 1024, 'm' => 1048576, 'g' => 1073741824];
		$unit = strtolower(substr($value, -1));
		if(is_numeric($unit) || !isset($units[ $unit ])) {
			return $value;
		}
		
		return ((int)$value) * $units[ $unit ];
	}
	
}
