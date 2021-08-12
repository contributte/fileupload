<?php declare(strict_types = 1);

namespace Tests\Models;

use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

class InvalidUploadModel
{

	public function remove($uploaded): void
	{
		FileSystem::delete($uploaded[0] . '/' . $uploaded[1]);
	}

	public function rename($upload, $newName)
	{
		FileSystem::rename($upload[0] . '/' . $upload[1], $upload[0] . '/' . $newName);
	}

	public function save(FileUpload $file, array $params = [])
	{
		if ($file->isOk()) {
			$file->move($params['folder'] . '/' . $file->name);
			return [$params['folder'], $file->name];
		}

		return false;
	}

}
