<?php declare(strict_types = 1);

use Contributte\FileUpload\Filter\AudioFilter;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Nette\Http\FileUpload;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Minimal 44-byte RIFF/WAVE header (PCM, mono, 8 kHz, 8 bit, no samples)
$wav = 'RIFF' . pack('V', 36) . 'WAVE'
	. 'fmt ' . pack('VvvVVvv', 16, 1, 1, 8000, 8000, 1, 8)
	. 'data' . pack('V', 0);

$check = static function (string $name, string $content): bool {
	$path = Environment::getTestDir() . '/' . $name;
	file_put_contents($path, $content);

	try {
		$upload = new FileUpload([
			'name' => $name,
			'full_path' => $name,
			'size' => strlen($content),
			'tmp_name' => $path,
			'error' => UPLOAD_ERR_OK,
		]);

		return (new AudioFilter())->checkType($upload);
	} finally {
		@unlink($path);
	}
};

// WAV file detected by content
Toolkit::test(static function () use ($check, $wav): void {
	Assert::true($check('sound.wav', $wav));
	Assert::true($check('sound', $wav));
});

// WAV extension is listed in allowed types
Toolkit::test(static function (): void {
	Assert::contains('wav', (new AudioFilter())->getAllowedTypes());
});

// Non-audio file is rejected
Toolkit::test(static function () use ($check): void {
	Assert::false($check('document.txt', 'Hello world'));
});
