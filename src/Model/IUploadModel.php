<?php

namespace Zet\FileUpload\Model;

/**
 * Interface IUploadController
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Model
 */
interface IUploadModel {

	/**
	 * Uložení nahraného souboru.
	 * @param \Nette\Http\FileUpload $file
	 * @return mixed Vlastní navrátová hodnota.
	 */
	public function save(\Nette\Http\FileUpload $file);

	/**
	 * Zpracování požadavku o smazání souboru.
	 * @param $uploaded Hodnota navrácená funkcí save.
	 */
	public function remove($uploaded);

}