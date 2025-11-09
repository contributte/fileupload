<?php declare(strict_types = 1);

use Ninjify\Nunjuck\Toolkit;
use Tester\Assert;
use Contributte\FileUpload\Model\DefaultFile;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function () {
	$file = new DefaultFile();
	$file->setFilename('foo.bar');

	Assert::equal('foo.bar', $file->toArray()['filename']);
});
