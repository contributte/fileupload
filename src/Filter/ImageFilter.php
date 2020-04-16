<?php

declare(strict_types=1);

namespace Zet\FileUpload\Filter;

/**
 * Class ImageFilter
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Filter
 */
class ImageFilter extends BaseFilter
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
				'image/png' => 'png',
				'image/pjpeg' => 'jpeg',
				'image/jpeg' => 'jpg',
				'image/gif' => 'gif',
				'image/tif' => 'tif',
		];
	}
}
