<?php

namespace Zet\FileUpload\Filter;

/**
 * Class BaseFilter
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Filter
 */
abstract class BaseFilter extends \Nette\Object implements IMimeTypeFilter {

	/**
	 * Vrátí seznam povolených typů souborů s jejich typickou koncovkou.
	 * @example array("text/plain" => "txt")
	 * @return string[]
	 */
	abstract protected function getMimeTypes();

	/**
	 * Ověří mimetype předaného souboru.
	 * @param \Nette\Http\FileUpload $file Nahraný soubor k ověření.
	 * @return bool Má soubor správný mimetype?
	 */
	public function checkType(\Nette\Http\FileUpload $file) {
		if(\Nette\Utils\Arrays::searchKey($this->getMimeTypes(), $file->getContentType()) !== FALSE) {
			return TRUE;
		} else {
			// Pokud se nepodaří ověřit mimetype, ověříme alespoň koncovku.
			if( array_search($this->getExtension($file->getName()), array_unique($this->getMimeTypes()) ) !== FALSE) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * Vrátí seznam povolených typů souborů.
	 * @return string
	 */
	public function getAllowedTypes() {
		return implode(array_unique($this->getMimeTypes()), ", ");
	}

	/**
	 * Vrátí koncovku souboru.
	 * @param string $filename
	 * @return string
	 */
	private function getExtension($filename) {
		$exploded = explode(".", $filename);
		return $exploded[ count($exploded) - 1 ];
	}
}