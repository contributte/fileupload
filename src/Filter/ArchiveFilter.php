<?php

declare(strict_types=1);

namespace Zet\FileUpload\Filter;

/**
 * Class ArchiveFilter
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Filter
 */
class ArchiveFilter extends BaseFilter
{


	/**
	 * Vrátí seznam povolených typů souborů s jejich typickou koncovkou.
	 *
	 * @example array("text/plain" => "txt")
	 * @return string[]
	 */
	protected function getMimeTypes()
	{
		return [
				'application/zip' => 'zip',
				'application/x-rar-compressed' => 'rar',
				'application/x-tar' => 'tar',
				'application/x-7z-compressed' => '7z',
		];
	}
}
