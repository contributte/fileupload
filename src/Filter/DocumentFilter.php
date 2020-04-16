<?php

declare(strict_types=1);

namespace Zet\FileUpload\Filter;

/**
 * Class DocumentFilter
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Filter
 */
class DocumentFilter extends BaseFilter
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
				'text/plain' => 'txt',
				'application/msword' => 'doc',
				'application/vnd.ms-excel' => 'xls',
				'application/pdf' => 'pdf',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
				'application/vnd.ms-powerpoint' => 'ppt',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
		];
	}
}
