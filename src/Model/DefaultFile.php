<?php declare(strict_types = 1);

namespace Contributte\FileUpload\Model;

use Nette\SmartObject;

/**
 * Class DefaultFile
 * Kontejner pro zadávání výchozího souboru pro uploader.
 */
class DefaultFile
{

	use SmartObject;

	/**
	 * Callback pro smazání výchozího souboru s parametry (mixed $identifier).
	 *
	 * @var array<mixed>
	 */
	public array $onDelete = [];

	/**
	 * Odkaz na náhled obrázku.
	 */
	private string $preview;

	/**
	 * Název souboru.
	 */
	private string $filename;

	/**
	 * Identifikátor souboru sloužící pro jeho smazání.
	 */
	private mixed $identifier;

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

	public function getIdentifier(): mixed
	{
		return $this->identifier;
	}

	public function setIdentifier(mixed $identifier): void
	{
		$this->identifier = $identifier;
	}

}
