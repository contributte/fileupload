<?php declare(strict_types = 1);

namespace Contributte\FileUpload\Filter;

/**
 * Class ImageFilter
 *
 * @author  Zechy <email@zechy.cz>
 */
class ImageFilter extends BaseFilter
{

	/**
	 * Vrátí seznam povolených typů souborů s jejich typickou koncovkou.
	 *
	 * @example array("text/plain" => "txt")
	 * @return string[]
	 */
	protected function getMimeTypes(): array
	{
		return [
			'image/png' => 'png',
			'image/pjpeg' => 'jpeg',
			'image/jpeg' => 'jpg',
			'image/gif' => 'gif',
		];
	}

}
