<?php declare(strict_types = 1);

namespace Tests\Filters;

class InvalidFilter
{

	protected function getMimeTypes(): array
	{
		return [
			'image/png' => 'png',
		];
	}

}
