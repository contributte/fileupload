<?php declare(strict_types = 1);

namespace Tests\Fixtures\Filter;

use Zet\FileUpload\Filter\BaseFilter;

class ValidFilter extends BaseFilter
{

	protected function getMimeTypes(): array
	{
		return [
			'image/png' => 'png',
		];
	}

}
