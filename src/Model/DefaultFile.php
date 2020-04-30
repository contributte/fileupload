<?php

declare(strict_types=1);

namespace Zet\FileUpload\Model;

use Nette\SmartObject;

/**
 * Class DefaultFile
 * Kontejner pro zadávání výchozího souboru pro uploader.
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Model
 */
class DefaultFile
{

	use SmartObject;

	/**
	 * Callback pro smazání výchozího souboru s parametry (mixed $identifier).
	 *
	 * @var array
	 */
	public $onDelete = [];

	/**
	 * Odkaz na náhled obrázku.
	 *
	 * @var string
	 */
	private $preview;

	/**
	 * Název souboru.
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Identifikátor souboru sloužící pro jeho smazání.
	 *
	 * @var mixed
	 */
	private $identifier;


	/**
	 * @return array
	 */
	public function toArray()
	{
		return [
				'preview' => $this->preview,
				'filename' => $this->filename,
				'id' => $this->identifier,
		];
	}


	/**
	 * @return string
	 */
	public function getPreview()
	{
		return $this->preview;
	}


	/**
	 * @param string $preview
	 */
	public function setPreview($preview)
	{
		$this->preview = $preview;
	}


	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}


	/**
	 * @param string $filename
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
	}


	/**
	 * @return mixed
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}


	/**
	 * @param mixed $identifier
	 */
	public function setIdentifier($identifier)
	{
		$this->identifier = $identifier;
	}
}
