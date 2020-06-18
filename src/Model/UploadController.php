<?php

declare(strict_types=1);

namespace Zet\FileUpload\Model;

use Nette\InvalidStateException;
use Zet\FileUpload\Template\JavascriptBuilder;
use Zet\FileUpload\Template\Renderer\BaseRenderer;

/**
 * Class UploadController
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload
 */
class UploadController extends \Nette\Application\UI\Control
{

	/** @var \Zet\FileUpload\FileUploadControl */
	private $uploadControl;

	/** @var \Nette\Http\Request */
	private $request;

	/** @var \Zet\FileUpload\Filter\IMimeTypeFilter */
	private $filter;

	/** @var BaseRenderer */
	private $renderer;


	/**
	 * UploadController constructor.
	 *
	 * @param \Zet\FileUpload\FileUploadControl $uploadControl
	 */
	public function __construct(\Zet\FileUpload\FileUploadControl $uploadControl)
	{
		$this->uploadControl = $uploadControl;
	}


	/**
	 * @param \Nette\Http\Request $request
	 */
	public function setRequest(\Nette\Http\Request $request): void
	{
		$this->request = $request;
	}


	/**
	 * @return \Zet\FileUpload\Filter\IMimeTypeFilter|NULL
	 */
	public function getFilter()
	{
		if ($this->filter === null) {
			/** @noinspection PhpInternalEntityUsedInspection */
			$className = $this->uploadControl->getFileFilter();
			if ($className !== null) {
				$filterClass = new $className;
				if ($filterClass instanceof \Zet\FileUpload\Filter\IMimeTypeFilter) {
					$this->filter = $filterClass;
				} else {
					throw new \Nette\UnexpectedValueException(
									'Třída pro filtrování souborů neimplementuje rozhraní \\Zet\\FileUpload\\Filter\\IMimeTypeFilter.'
					);
				}
			}
		}

		return $this->filter;
	}


	/**
	 * @return \Zet\FileUpload\FileUploadControl
	 */
	public function getUploadControl(): \Zet\FileUpload\FileUploadControl
	{
		return $this->uploadControl;
	}


	/**
	 * @return BaseRenderer
	 */
	public function getRenderer(): BaseRenderer
	{
		if ($this->renderer === null) {
			$rendererClass = $this->uploadControl->getRenderer();
			$this->renderer = new $rendererClass($this->uploadControl, $this->uploadControl->getTranslator());

			if (!($this->renderer instanceof BaseRenderer)) {
				throw new InvalidStateException(
								'Renderer musí být instancí třídy `\\Zet\\FileUpload\\Template\\BaseRenderer`.'
				);
			}
		}

		return $this->renderer;
	}


	/**
	 * Vytvoření šablony s JavaScriptem pro FileUpload.
	 *
	 * @return string
	 */
	public function getJavaScriptTemplate(): string
	{
		$builder = new JavascriptBuilder(
						$this->getRenderer(),
						$this
		);

		return $builder->getJsTemplate();
	}


	/**
	 * Vytvoření šablony s přehledem o uploadu.
	 *
	 * @return \Nette\Utils\Html
	 */
	public function getControlTemplate(): \Nette\Utils\Html
	{
		return $this->getRenderer()->buildDefaultTemplate();
	}


	/**
	 * Zpracování uploadu souboru.
	 */
	public function handleUpload(): void
	{
		$files = $this->request->getFiles();
		$token = $this->request->getPost('token');
		$params = json_decode($this->request->getPost('params'), true);

		/** @var \Nette\Http\FileUpload $file */
		$file = $files[$this->uploadControl->getHtmlName()];
		/** @noinspection PhpInternalEntityUsedInspection */
		$model = $this->uploadControl->getUploadModel();
		$cache = $this->uploadControl->getCache();
		$filter = $this->getFilter();

		try {
			if ($filter !== null && !$filter->checkType($file)) {
				throw new \Zet\FileUpload\InvalidFileException($this->getFilter()->getAllowedTypes());
			}

			if ($file->isOk()) {
				$returnData = $model->save($file, $params);
				/** @noinspection PhpInternalEntityUsedInspection */
				$cacheFiles = $cache->load($this->uploadControl->getTokenizedCacheName($token));
				if (empty($cacheFiles)) {
					$cacheFiles = [$this->request->getPost('id') => $returnData];
				} else {
					$cacheFiles[$this->request->getPost('id')] = $returnData;
				}
				/** @noinspection PhpInternalEntityUsedInspection */
				$cache->save($this->uploadControl->getTokenizedCacheName($token), $cacheFiles);
			}
		} catch (\Zet\FileUpload\InvalidFileException $e) {
			$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse([
									'id' => $this->request->getPost('id'),
									'error' => 100,
									'errorMessage' => $e->getMessage(),
			]));
		} catch (\Exception $e) {
			$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse([
									'id' => $this->request->getPost('id'),
									'error' => 99,
									'errorMessage' => $e->getMessage(),
			]));
		}

		$this->presenter->sendResponse(new \Nette\Application\Responses\JsonResponse([
								'id' => $this->request->getPost('id'),
								'error' => $file->getError(),
		]));
	}


	/**
	 * Odstraní nahraný soubor.
	 */
	public function handleRemove(): void
	{
		$id = $this->request->getQuery('id');
		$token = $this->request->getQuery('token');
	//$default = $this->request->getQuery('default', 0);
		$default = $this->request->getQuery('default');

		if ($default == null) {
			$cache = $this->uploadControl->getCache();
			/** @noinspection PhpInternalEntityUsedInspection */
			$cacheFiles = $cache->load($this->uploadControl->getTokenizedCacheName($token));
			if (isset($cacheFiles[$id])) {
				/** @noinspection PhpInternalEntityUsedInspection */
				$this->uploadControl->getUploadModel()->remove($cacheFiles[$id]);
				unset($cacheFiles[$id]);
				/** @noinspection PhpInternalEntityUsedInspection */
				$cache->save($this->uploadControl->getTokenizedCacheName($token), $cacheFiles);
			}
		} else {
			$files = $this->uploadControl->getDefaultFiles();

			foreach ($files as $file) {
				if ($file->getIdentifier() == $id) {
					$file->onDelete($id);
				}
			}
		}
	}


	/**
	 * Přejmenuje nahraný soubor.
	 */
	public function handleRename(): void
	{
		$id = $this->request->getQuery('id');
		$newName = $this->request->getQuery('newName');
		$token = $this->request->getQuery('token');

		$cache = $this->uploadControl->getCache();
		/** @noinspection PhpInternalEntityUsedInspection */
		$cacheFiles = $cache->load($this->uploadControl->getTokenizedCacheName($token));

		if (isset($cacheFiles[$id])) {
			/** @noinspection PhpInternalEntityUsedInspection */
			$cacheFiles[$id] = $this->uploadControl->getUploadModel()->rename($cacheFiles[$id], $newName);
			/** @noinspection PhpInternalEntityUsedInspection */
			$cache->save($this->uploadControl->getTokenizedCacheName($token), $cacheFiles);
		}
	}


	public function validate()
	{
		// Nette ^2.3.10 bypass
	}
}
