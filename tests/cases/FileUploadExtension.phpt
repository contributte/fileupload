<?php declare(strict_types = 1);

namespace Tests\Cases;

use Nette\DI\Compiler;
use Nette\DI\InvalidConfigurationException;
use Nette\InvalidStateException;
use Nette\UnexpectedValueException;
use Tester\Assert;
use Tester\TestCase;
use Tests\Toolkit\Container;
use Tests\Toolkit\Helpers;
use Contributte\FileUpload\Exception\InvalidArgumentException;
use Contributte\FileUpload\Filter\IMimeTypeFilter;
use Contributte\FileUpload\Model\IUploadModel;
use Contributte\FileUpload\Template\Renderer\IUploadRenderer;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class FileUploadExtension extends TestCase
{

	public function testConfig(): void
	{
		Assert::noError(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							fileFilter: Contributte\FileUpload\Filter\ImageFilter
						NEON));
				})
				->build();
		});

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							fileFilter: Contributte\FileUpload\Filter\ImageFilter
							uiMode: full
						NEON));
				})
				->build();
		}, InvalidConfigurationException::class, "Unexpected item 'fileUpload › uiMode'.");

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							maxFiles: null
					NEON));
				})
				->build();
		}, InvalidConfigurationException::class);

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							messages: hello
					NEON));
				})
				->build();
		}, InvalidConfigurationException::class, "The item 'fileUpload › messages' expects to be array, 'hello' given.");
	}

	public function testUploadModels(): void
	{
		$uploadModel = 'App\Model\MyUploadModel';
		Assert::exception(function () use ($uploadModel): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler) use ($uploadModel): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							fileFilter: Contributte\FileUpload\Filter\ImageFilter
							uploadModel: $uploadModel
					NEON));
				})
				->build();
		}, InvalidArgumentException::class, sprintf('The passed model %s does not exist.', $uploadModel));

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							uploadModel: Tests\Fixtures\Model\InvalidUploadModel
					NEON));
				})
				->build();
		}, InvalidStateException::class, 'The passed model is not an instance of ' . IUploadModel::class);

		Assert::noError(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							uploadModel: Tests\Fixtures\Model\ValidUploadModel
					NEON));
				})
				->build();
		});
	}

	public function testFilters(): void
	{
		$fileFilter = 'App\Model\MyFilter';
		Assert::exception(function () use ($fileFilter): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler) use ($fileFilter): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							fileFilter: $fileFilter
					NEON));
				})
				->build();
		}, InvalidArgumentException::class, sprintf('The file filter class %s does not exist.', $fileFilter));

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							fileFilter: Tests\Fixtures\Filter\InvalidFilter
					NEON));
				})
				->build();
		}, UnexpectedValueException::class, 'The file filter class does not implement the interface ' . IMimeTypeFilter::class);

		Assert::noError(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							fileFilter: Tests\Fixtures\Filter\ValidFilter
					NEON));
				})
				->build();
		});
	}

	public function testRenderers(): void
	{
		$renderer = 'App\Model\MyRenderer';
		Assert::exception(function () use ($renderer): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler) use ($renderer): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							renderer: $renderer
					NEON));
				})
				->build();
		}, InvalidArgumentException::class, sprintf('The form renderer class %s does not exist.', $renderer));

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							renderer: Tests\Fixtures\Template\Renderer\InvalidRenderer
					NEON));
				})
				->build();
		}, InvalidStateException::class, 'The form renderer class does not implement the interface ' . IUploadRenderer::class);

		Assert::noError(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon(<<<NEON
						fileUpload:
							renderer: Contributte\FileUpload\Template\Renderer\Bootstrap4Renderer
					NEON));
				})
				->build();
		});
	}

}

(new FileUploadExtension())->run();
