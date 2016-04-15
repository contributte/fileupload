<?php

namespace Zet\FileUpload\Model;

/**
 * Class BaseUploadModel
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Model
 */
class BaseUploadModel extends \Nette\Object implements IUploadModel {

	/**
	 * Uložení nahraného souboru.
	 * @param \Nette\Http\FileUpload $file
	 * @return mixed Vlastní navrátová hodnota.
	 */
	public function save(\Nette\Http\FileUpload $file) {
		return $file->getSanitizedName();
	}

	/**
	 * Zpracování požadavku o smazání souboru.
	 * @param \Nette\Http\FileUpload $file
	 */
	public function remove(\Nette\Http\FileUpload $file) {
		// TODO: Implement remove() method.
	}
}