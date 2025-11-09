<?php declare(strict_types = 1);

namespace Contributte\FileUpload\Model;

use Nette\Http\FileUpload;
use Nette\SmartObject;
use Nette\Utils\Strings;

/**
 * Class BaseUploadModel
 */
class BaseUploadModel implements IUploadModel
{

	use SmartObject;

	/**
	 * Uložení nahraného souboru.
	 *
	 * @param array<mixed> $params Pole vlastních parametrů.
	 * @return mixed Vlastní navrátová hodnota.
	 */
	public function save(FileUpload $file, array $params = []): mixed
	{
		return $file->getSanitizedName();
	}

	/**
	 * Zpracování požadavku o smazání souboru.
	 *
	 * @param mixed $uploaded Hodnota navrácená funkcí save.
	 */
	public function remove(mixed $uploaded): void
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
	public function rename(mixed $upload, string $newName): mixed
	{
		return Strings::webalize($newName);
	}

}
