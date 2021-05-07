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
	abstract protected function getMimeTypes();

	/**
	 * Ověří mimetype předaného souboru.
	 *
	 * @param FileUpload $file Nahraný soubor k ověření.
	 * @return bool Má soubor správný mimetype?
	 */
	public function checkType(FileUpload $file)
	{
		if (Arrays::searchKey($this->getMimeTypes(), $file->getContentType()) !== false) {
			return true;
		} else {
			// Pokud se nepodaří ověřit mimetype, ověříme alespoň koncovku.
			return array_search($this->getExtension($file->getName()), array_unique($this->getMimeTypes()), true) !== false;
		}
	}

	/**
	 * Vrátí seznam povolených typů souborů.
	 *
	 * @return string
	 */
	public function getAllowedTypes()
	{
		return implode(', ', array_unique($this->getMimeTypes()));
	}

	/**
	 * Vrátí koncovku souboru.
	 *
	 * @param string $filename
	 * @return string
	 */
	private function getExtension($filename)
	{
		$exploded = explode('.', $filename);

		return $exploded[count($exploded) - 1];
	}

}
