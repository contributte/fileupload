<?php

namespace Zet\FileUpload\Model;

use Nette\Object;
use Nette\Utils\Html;

/**
 * Class DefaultFile
 * Kontejner pro zadávání výchozího souboru pro uploader.
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Model
 */
class DefaultFile extends Object {
	
	/**
	 * Callback pro smazání výchozího souboru s parametry (mixed $identifier).
	 *
	 * @var array
	 */
	public $onDelete = [];
	
	/**
	 * Data sloužící jako náhled obrázku.
	 *
	 * @var Html
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
	public function toArray() {
		return [
			"preview" => (string) $this->preview,
			"filename" => $this->filename,
			"id" => $this->identifier
		];
	}
	
	/**
	 * @return Html
	 */
	public function getPreview() {
		return $this->preview;
	}
	
	/**
	 * @param Html $preview
	 */
	public function setPreview($preview) {
		$this->preview = $preview;
	}
	
	/**
	 * @return string
	 */
	public function getFilename(): string {
		return $this->filename;
	}
	
	/**
	 * @param string $filename
	 */
	public function setFilename(string $filename) {
		$this->filename = $filename;
	}
	
	/**
	 * @return mixed
	 */
	public function getIdentifier() {
		return $this->identifier;
	}
	
	/**
	 * @param mixed $identifier
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}
	
	
}