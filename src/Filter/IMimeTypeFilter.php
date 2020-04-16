<?php

declare(strict_types=1);

namespace Zet\FileUpload\Filter;

/**
 * Interface IMimeTypeFilters
 * Rozhraní pro kontrolu Mime typu souboru.
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Filter
 */
interface IMimeTypeFilter
{


	/**
	 * Ověří mimetype předaného souboru.
	 *
	 * @param \Nette\Http\FileUpload $file Nahraný soubor k ověření.
	 * @return bool Má soubor správný mimetype?
	 */
	public function checkType(\Nette\Http\FileUpload $file);


	/**
	 * Vrátí seznam povolených typů souborů.
	 *
	 * @return string
	 */
	public function getAllowedTypes();
}
