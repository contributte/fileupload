<?php declare(strict_types = 1);

namespace Tests\Fixtures\Model;

use Contributte\FileUpload\Model\BaseUploadModel;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

class ValidUploadModel extends BaseUploadModel
{

	public function remove(mixed $uploaded): void
	{
		FileSystem::delete($uploaded[0] . '/' . $uploaded[1]);
	}

	public function rename(mixed $upload, string $newName): void
	{
		FileSystem::rename($upload[0] . '/' . $upload[1], $upload[0] . '/' . $newName);
	}

	/**
	 * @param array<mixed> $params
	 * @return array<string>|false
	 */
	public function save(FileUpload $file, array $params = []): array|false
	{
		if ($file->isOk()) {
			$file->move($params['folder'] . '/' . $file->name);

			return [$params['folder'], $file->name];
		}

		return false;
	}

}
