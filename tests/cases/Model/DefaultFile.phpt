<?php declare(strict_types = 1);

use Contributte\Tester\Toolkit;
use Tester\Assert;
use Contributte\FileUpload\Model\DefaultFile;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$file = new DefaultFile();
	$file->setFilename('foo.bar');

	Assert::equal('foo.bar', $file->toArray()['filename']);
});
