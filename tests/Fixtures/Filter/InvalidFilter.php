<?php declare(strict_types = 1);

namespace Tests\Fixtures\Filter;

class InvalidFilter
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
