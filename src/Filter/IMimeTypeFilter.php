<?php declare(strict_types = 1);

namespace Zet\FileUpload\Filter;

use Nette\Http\FileUpload;

/**
 * Interface IMimeTypeFilters
 * Rozhraní pro kontrolu Mime typu souboru.
 *
 * @author  Zechy <email@zechy.cz>
 */
interface IMimeTypeFilter
{

	/**
	 * Ověří mimetype předaného souboru.
	 *
	 * @param FileUpload $file Nahraný soubor k ověření.
	 * @return bool Má soubor správný mimetype?
	 */
	public function checkType(FileUpload $file);

	/**
	 * Vrátí seznam povolených typů souborů.
	 *
	 * @return string
	 */
	public function getAllowedTypes();

}
