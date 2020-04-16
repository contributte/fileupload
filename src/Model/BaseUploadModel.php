<?php

declare(strict_types=1);

namespace Zet\FileUpload\Model;

use Nette\SmartObject;

/**
 * Class BaseUploadModel
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Model
 */
class BaseUploadModel implements IUploadModel
{

	use SmartObject;


	/**
	 * Uložení nahraného souboru.
	 *
	 * @param \Nette\Http\FileUpload $file
	 * @param array                  $params Pole vlastních parametrů.
	 * @return mixed Vlastní navrátová hodnota.
	 */
	public function save(\Nette\Http\FileUpload $file, array $params = [])
	{
		return $file->getSanitizedName();
	}


	/**
	 * Zpracování požadavku o smazání souboru.
	 *
	 * @param mixed $uploaded Hodnota navrácená funkcí save.
	 */
	public function remove($uploaded)
	{
		// By Pass...
	}


	/**
	 * Zpracování přejmenování souboru.
	 *
	 * @param mixed  $upload  Hodnota navrácená funkcí save.
	 * @param string $newName Nové jméno souboru.
	 * @return mixed Vlastní návratová hodnota.
	 */
	public function rename($upload, $newName)
	{
		return \Nette\Utils\Strings::webalize($newName);
	}
}
