<?php

namespace Zet\FileUpload\Model;

/**
 * Class UploadController
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload
 */
class UploadController extends \Nette\Application\UI\Control {

	/**
	 * @var \Zet\FileUpload\FileUploadControl
	 */
	private $uploadControl;

	/**
	 * @var \Nette\Http\Request
	 */
	private $request;

	/**
	 * @var \Zet\FileUpload\Filter\IMimeTypeFilter
	 */
	private $filter;

	/**
	 * UploadController constructor.
	 * @param \Zet\FileUpload\FileUploadControl $uploadControl
	 */
	public function __construct(\Zet\FileUpload\FileUploadControl $uploadControl) {
		$this->uploadControl = $uploadControl;
	}

	/**
	 * @param \Nette\Http\Request $request
	 */
	public function setRequest($request) {
		$this->request = $request;
	}

	/**
	 * @return \Zet\FileUpload\Filter\IMimeTypeFilter
	 */
	public function getFilter() {
		if(is_null($this->filter)) {
			$className = $this->uploadControl->getFileFilter();
			$filterClass = new $className;
			if($filterClass instanceof \Zet\FileUpload\Filter\IMimeTypeFilter) {
				$this->filter = $filterClass;
			} else {
				throw new \Nette\UnexpectedValueException(
					"Třída pro filtrování souborů neimplementuje rozhraní \\Zet\\FileUpload\\Filter\\IMimeTypeFilter."
				);
			}
		}

		return $this->filter;
	}

	/**
	 * Vytvoření šablony s JavaScriptem pro FileUpload.
	 * @return string
	 */
	public function getJavaScriptTemplate() {
		$template = $this->template;
		$template->setFile(__DIR__ . "/../Template/javascript.latte");
		$template->uploadUrl = $this->link("upload");
		$template->deleteLink = $this->link("remove");
		$template->inputId = $this->uploadControl->getHtmlId();
		$template->maxFiles = $this->uploadControl->getMaxFiles();
		$template->maxFileSize = $this->uploadControl->getMaxFileSize();
		$template->fileSizeString = $this->uploadControl->getFileSizeString();
		$template->productionMode = \Tracy\Debugger::$productionMode;

		return (string) $template;
	}

	/**
	 * Vytvoření šablony s přehledem o uploadu.
	 * @return string
	 */
	public function getControlTemplate() {
		$template = $this->template;
		$template->setFile(__DIR__ . "/../Template/control.latte");
		$template->htmlId = $this->uploadControl->getHtmlId();
		$template->htmlName = $this->uploadControl->getHtmlName();

		return (string) $template;
	}

	/**
	 * Zpracování uploadu souboru.
	 */
	public function handleUpload() {
		$files = $this->request->getFiles();
		$file = $files[ $this->uploadControl->getHtmlName() ];
		$model = $this->uploadControl->getUploadModel();
		$cache = $this->uploadControl->getCache();

		try {
			if(!$this->getFilter()->checkType($file)) {
				throw new \Zet\FileUpload\InvalidFileException($this->getFilter()->getAllowedTypes());
			}

			if($file->isOk()) {
				$returnData = $model->save($file);

				$cacheFiles = $cache->load($this->uploadControl->getHtmlId());
				if(empty($cacheFiles)) {
					$cacheFiles = array($this->request->getPost("id") => $returnData);
				} else {
					$cacheFiles[ $this->request->getPost("id") ] = $returnData;
				}
				$cache->save($this->uploadControl->getHtmlId(), $cacheFiles);
			}

		} catch(\Zet\FileUpload\InvalidFileException $e) {
			$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse(array(
				"id" => $this->request->getPost("id"),
				"error" => 100,
				"errorMessage" => $e->getMessage()
			)));

		} catch(\Exception $e) {
			$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse(array(
				"id" => $this->request->getPost("id"),
				"error" => 99,
				"errorMessage" => $e->getMessage()
			)));
		}

		$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse(array(
			"id" => $this->request->getPost("id"),
			"error" => $file->getError()
		)));
	}

	/**
	 * Odstraní nahraný soubor.
	 */
	public function handleRemove() {
		$id = $this->request->getQuery("id");

		$cache = $this->uploadControl->getCache();
		$cacheFiles = $cache->load($this->uploadControl->getHtmlId());
		if(isset($cacheFiles[$id])) {
			$this->uploadControl->getUploadModel()->remove($cacheFiles[$id]);
			unset($cacheFiles[$id]);
			$cache->save($this->uploadControl->getHtmlId(), $cacheFiles);
		}
	}

	public function validate() {
		// Nette 2.3.10 bypass
	}
}
