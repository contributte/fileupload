<?php declare(strict_types = 1);

namespace Contributte\FileUpload\Model;

use Nette\Application\AbortException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Control;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\InvalidStateException;
use Nette\UnexpectedValueException;
use Nette\Utils\Html;
use Throwable;
use Contributte\FileUpload\Exception\InvalidFileException;
use Contributte\FileUpload\FileUploadControl;
use Contributte\FileUpload\Filter\IMimeTypeFilter;
use Contributte\FileUpload\Template\JavascriptBuilder;
use Contributte\FileUpload\Template\Renderer\BaseRenderer;

/**
 * Class UploadController
 *
 * @property Template $template
 * @author  Zechy <email@zechy.cz>
 */
class UploadController extends Control
{

	/** @var FileUploadControl */
	private $uploadControl;

	/** @var Request */
	private $request;

	/** @var IMimeTypeFilter */
	private $filter;

	/** @var BaseRenderer */
	private $renderer;

	/**
	 * @param FileUploadControl $uploadControl
	 */
	public function __construct(FileUploadControl $uploadControl)
	{
		$this->uploadControl = $uploadControl;
	}

	public function setRequest(Request $request): void
	{
		$this->request = $request;
	}

	public function getFilter(): ?IMimeTypeFilter
	{
		if ($this->filter === null) {
			/** @noinspection PhpInternalEntityUsedInspection */
			$className = $this->uploadControl->getFileFilter();
			if (!empty($className)) {
				$filterClass = new $className();
				if ($filterClass instanceof IMimeTypeFilter) {
					$this->filter = $filterClass;
				} else {
					throw new UnexpectedValueException(
						'The file filter class does not implement the interface \\Zet\\FileUpload\\Filter\\IMimeTypeFilter.'
					);
				}
			}
		}

		return $this->filter;
	}

	public function getUploadControl(): FileUploadControl
	{
		return $this->uploadControl;
	}

	public function getRenderer(): BaseRenderer
	{
		if ($this->renderer === null) {
			$rendererClass = $this->uploadControl->getRenderer();
			$this->renderer = new $rendererClass($this->uploadControl, $this->uploadControl->getTranslator());

			if (!($this->renderer instanceof BaseRenderer)) {
				throw new InvalidStateException(
					'The renderer must be an instance of the class `\\Zet\\FileUpload\\Template\\BaseRenderer`.'
				);
			}
		}

		return $this->renderer;
	}

	/**
	 * Vytvoření šablony s JavaScriptem pro FileUpload.
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
	 */
	public function getControlTemplate(): Html
	{
		return $this->getRenderer()->buildDefaultTemplate();
	}

	/**
	 * Zpracování uploadu souboru.
	 *
	 * @throws AbortException
	 */
	public function handleUpload(): void
	{
		$files = $this->request->getFiles();
		$token = $this->request->getPost('token');
		$params = json_decode($this->request->getPost('params'), true);

		/** @var FileUpload $file */
		$file = $files[$this->uploadControl->getHtmlName()];
		/** @noinspection PhpInternalEntityUsedInspection */
		$model = $this->uploadControl->getUploadModel();
		$cache = $this->uploadControl->getCache();
		$filter = $this->getFilter();

		try {
			if ($filter !== null && !$filter->checkType($file)) {
				throw new InvalidFileException($this->getFilter() !== null ? $this->getFilter()->getAllowedTypes() : '');
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
		} catch (InvalidFileException $e) {
			$this->presenter->sendResponse(new JsonResponse([
				'id' => $this->request->getPost('id'),
				'error' => 100,
				'errorMessage' => $e->getMessage(),
			]));

		} catch (Throwable $e) {
			$this->presenter->sendResponse(new JsonResponse([
				'id' => $this->request->getPost('id'),
				'error' => 99,
				'errorMessage' => $e->getMessage(),
			]));
		}

		$this->presenter->sendResponse(new JsonResponse([
			'id' => $this->request->getPost('id'),
			'error' => $file->getError(),
		]));
	}

	/**
	 * Odstraní nahraný soubor.
	 *
	 * @throws Throwable
	 */
	public function handleRemove(): void
	{
		$id = $this->request->getQuery('id');
		$token = $this->request->getQuery('token');
		$default = $this->request->getQuery('default');

		if ($default === null) {
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
				if ($file->getIdentifier() === $id) {
					$file->onDelete($id);
				}
			}
		}
	}

	/**
	 * Přejmenuje nahraný soubor.
	 *
	 * @throws Throwable
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

}
