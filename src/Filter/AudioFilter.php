<?php declare(strict_types = 1);

namespace Zet\FileUpload\Filter;

/**
 * Class AudioFilter
 *
 * @author  Zechy <email@zechy.cz>
 */
class AudioFilter extends BaseFilter
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
			'audio/mpeg3' => 'mp3',
			'audio/x-mpeg-3' => 'mp3',
			'audio/ogg' => 'ogg',
			'audio/x-aiff' => 'aiff',
		];
	}

}
