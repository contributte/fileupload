<?php declare(strict_types = 1);

namespace Zet\FileUpload\Model;

use Nette\SmartObject;

/**
 * Class DefaultFile
 * Kontejner pro zadávání výchozího souboru pro uploader.
 *
 * @author  Zechy <email@zechy.cz>
 */
class DefaultFile
{

	use SmartObject;

	/**
	 * Callback pro smazání výchozího souboru s parametry (mixed $identifier).
	 *
	 * @var array<string>
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
	 * @return array<mixed>
	 */
	public function toArray(): array
	{
		return [
			'preview' => $this->preview,
			'filename' => $this->filename,
			'id' => $this->identifier,
		];
	}

	public function getPreview(): string
	{
		return $this->preview;
	}

	public function setPreview(string $preview): void
	{
		$this->preview = $preview;
	}

	public function getFilename(): string
	{
		return $this->filename;
	}

	public function setFilename(string $filename): void
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
	public function setIdentifier($identifier): void
	{
		$this->identifier = $identifier;
	}

}
