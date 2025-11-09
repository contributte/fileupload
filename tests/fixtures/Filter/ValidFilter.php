<?php declare(strict_types = 1);

namespace Tests\Fixtures\Filter;

use Contributte\FileUpload\Filter\BaseFilter;

class ValidFilter extends BaseFilter
{

	/**
	 * @return array<string, string>
	 */
	protected function getMimeTypes(): array
	{
		return [
			'image/png' => 'png',
		];
	}

}
