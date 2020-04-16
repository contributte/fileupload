<?php

declare(strict_types=1);

namespace Zet\FileUpload\Model;

/**
 * Interface IUploadController
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Model
 */
interface IUploadModel
{


	/**
	 * Uložení nahraného souboru.
	 *
	 * @param \Nette\Http\FileUpload $file
	 * @param array                  $params Pole vlastních parametrů.
	 * @return mixed Vlastní navrátová hodnota.
	 */
	public function save(\Nette\Http\FileUpload $file, array $params = []);


	/**
	 * Zpracování přejmenování souboru.
	 *
	 * @param mixed  $upload  Hodnota navrácená funkcí save.
	 * @param string $newName Nové jméno souboru.
	 * @return mixed Vlastní návratová hodnota.
	 */
	public function rename($upload, $newName);


	/**
	 * Zpracování požadavku o smazání souboru.
	 *
	 * @param mixed $uploaded Hodnota navrácená funkcí save.
	 */
	public function remove($uploaded);
}
