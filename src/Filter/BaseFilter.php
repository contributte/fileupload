<?php declare(strict_types = 1);

namespace Contributte\FileUpload\Filter;

use Nette\Http\FileUpload;
use Nette\SmartObject;
use Nette\Utils\Arrays;

/**
 * Class BaseFilter
 */
abstract class BaseFilter implements IMimeTypeFilter
{

	use SmartObject;

	/**
	 * Ověří mimetype předaného souboru.
	 *
	 * @param FileUpload $file Nahraný soubor k ověření.
	 * @return bool Má soubor správný mimetype?
	 */
	public function checkType(FileUpload $file): bool
	{
		return Arrays::getKeyOffset($this->getMimeTypes(), (string) $file->getContentType()) !== null
			// Pokud se nepodaří ověřit mimetype, ověříme alespoň koncovku.
			? true
			: array_search($this->getExtension($file->getUntrustedName()), array_unique($this->getMimeTypes()), true) !== false;
	}

	/**
	 * Vrátí seznam povolených typů souborů.
	 */
	public function getAllowedTypes(): string
	{
		return implode(', ', array_unique($this->getMimeTypes()));
	}

	/**
	 * Vrátí seznam povolených typů souborů s jejich typickou koncovkou.
	 *
	 * @example array("text/plain" => "txt")
	 * @return string[]
	 */
	abstract protected function getMimeTypes(): array;

	/**
	 * Vrátí koncovku souboru.
	 */
	private function getExtension(string $filename): string
	{
		$exploded = explode('.', $filename);

		return $exploded[count($exploded) - 1];
	}

}
