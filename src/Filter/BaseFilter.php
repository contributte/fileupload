<?php declare(strict_types = 1);

namespace Zet\FileUpload\Filter;

use Nette\Http\FileUpload;
use Nette\SmartObject;
use Nette\Utils\Arrays;

/**
 * Class BaseFilter
 *
 * @author  Zechy <email@zechy.cz>
 */
abstract class BaseFilter implements IMimeTypeFilter
{

	use SmartObject;

	/**
	 * Vrátí seznam povolených typů souborů s jejich typickou koncovkou.
	 *
	 * @example array("text/plain" => "txt")
	 * @return string[]
	 */
	abstract protected function getMimeTypes(): array;

	/**
	 * Ověří mimetype předaného souboru.
	 *
	 * @param FileUpload $file Nahraný soubor k ověření.
	 * @return bool Má soubor správný mimetype?
	 */
	public function checkType(FileUpload $file): bool
	{
		if (Arrays::getKeyOffset($this->getMimeTypes(), (string)$file->getContentType()) !== null) {
			return true;
		} else {
			// Pokud se nepodaří ověřit mimetype, ověříme alespoň koncovku.
			return array_search($this->getExtension($file->getUntrustedName()), array_unique($this->getMimeTypes()), true) !== false;
		}
	}

	/**
	 * Vrátí seznam povolených typů souborů.
	 */
	public function getAllowedTypes(): string
	{
		return implode(', ', array_unique($this->getMimeTypes()));
	}

	/**
	 * Vrátí koncovku souboru.
	 */
	private function getExtension(string $filename): string
	{
		$exploded = explode('.', $filename);

		return $exploded[count($exploded) - 1];
	}

}
