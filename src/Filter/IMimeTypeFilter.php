<?php declare(strict_types = 1);

namespace Contributte\FileUpload\Filter;

use Nette\Http\FileUpload;

/**
 * Interface IMimeTypeFilters
 * Rozhraní pro kontrolu Mime typu souboru.
 */
interface IMimeTypeFilter
{

	/**
	 * Ověří mimetype předaného souboru.
	 *
	 * @param FileUpload $file Nahraný soubor k ověření.
	 * @return bool Má soubor správný mimetype?
	 */
	public function checkType(FileUpload $file): bool;

	/**
	 * Vrátí seznam povolených typů souborů.
	 */
	public function getAllowedTypes(): string;

}
