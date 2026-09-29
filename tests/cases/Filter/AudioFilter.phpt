<?php declare(strict_types = 1);

use Contributte\FileUpload\Filter\AudioFilter;
use Contributte\Tester\Toolkit;
use Nette\Http\FileUpload;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

function createUpload(string $name, string $content): FileUpload
{
	$path = tempnam(sys_get_temp_dir(), 'fileupload');
	file_put_contents($path, $content);

	return new FileUpload([
		'name' => $name,
		'full_path' => $name,
		'size' => strlen($content),
		'tmp_name' => $path,
		'error' => UPLOAD_ERR_OK,
	]);
}

function createWav(): string
{
	// Minimal 44-byte RIFF/WAVE header (PCM, mono, 8 kHz, 8 bit, no samples)
	return 'RIFF' . pack('V', 36) . 'WAVE'
		. 'fmt ' . pack('VvvVVvv', 16, 1, 1, 8000, 8000, 1, 8)
		. 'data' . pack('V', 0);
}

// WAV file detected by content
Toolkit::test(function (): void {
	$filter = new AudioFilter();

	Assert::true($filter->checkType(createUpload('sound.wav', createWav())));
	Assert::true($filter->checkType(createUpload('sound', createWav())));
});

// WAV extension is listed in allowed types
Toolkit::test(function (): void {
	$filter = new AudioFilter();

	Assert::contains('wav', $filter->getAllowedTypes());
});

// Non-audio file is rejected
Toolkit::test(function (): void {
	$filter = new AudioFilter();

	Assert::false($filter->checkType(createUpload('document.txt', 'Hello world')));
});
