<?php declare(strict_types = 1);

namespace Tests\Cases;

use Nette\DI\Compiler;
use Nette\DI\InvalidConfigurationException;
use Nette\InvalidStateException;
use Nette\UnexpectedValueException;
use Tester\Assert;
use Tester\TestCase;
use Tests\Fixtures\Filter\InvalidFilter;
use Tests\Fixtures\Filter\ValidFilter;
use Tests\Fixtures\Model\InvalidUploadModel;
use Tests\Fixtures\Model\ValidUploadModel;
use Tests\Fixtures\Renderer\InvalidRenderer;
use Tests\Toolkit\Container;
use Tests\Toolkit\Helpers;
use Zet\FileUpload\Exception\InvalidArgumentException;
use Zet\FileUpload\Filter\IMimeTypeFilter;
use Zet\FileUpload\Model\IUploadModel;
use Zet\FileUpload\Template\Renderer\Bootstrap4Renderer;
use Zet\FileUpload\Template\Renderer\IUploadRenderer;

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
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							fileFilter: Zet\FileUpload\Filter\ImageFilter
						'));
				})
				->build();
		});

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							fileFilter: Zet\FileUpload\Filter\ImageFilter
							uiMode: full
						'));
				})
				->build();
		}, InvalidConfigurationException::class, "Unexpected item 'fileUpload › uiMode'.");

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							maxFiles: null
					'));
				})
				->build();
		}, InvalidConfigurationException::class);

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							messages: hello
					'));
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
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							fileFilter: Zet\FileUpload\Filter\ImageFilter
							uploadModel: ' . $uploadModel));
				})
				->build();
		}, InvalidArgumentException::class, sprintf('The passed model %s does not exist.', $uploadModel));

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							uploadModel: ' . InvalidUploadModel::class));
				})
				->build();
		}, InvalidStateException::class, 'The passed model is not an instance of ' . IUploadModel::class);

		Assert::noError(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							uploadModel: ' . ValidUploadModel::class));
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
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							fileFilter: ' . $fileFilter));
				})
				->build();
		}, InvalidArgumentException::class, sprintf('The file filter class %s does not exist.', $fileFilter));

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							fileFilter: ' . InvalidFilter::class));
				})
				->build();
		}, UnexpectedValueException::class, 'The file filter class does not implement the interface ' . IMimeTypeFilter::class);

		Assert::noError(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							fileFilter: ' . ValidFilter::class));
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
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							maxFiles: 10
							maxFileSize: 2M
							renderer: ' . $renderer));
				})
				->build();
		}, InvalidArgumentException::class, sprintf('The form renderer class %s does not exist.', $renderer));

		Assert::exception(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							renderer: ' . InvalidRenderer::class));
				})
				->build();
		}, InvalidStateException::class, 'The form renderer class does not implement the interface ' . IUploadRenderer::class);

		Assert::noError(function (): void {
			Container::of()
				->withDefaults()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addConfig(Helpers::neon('
						fileUpload:
							renderer: ' . Bootstrap4Renderer::class));
				})
				->build();
		});
	}

}

(new FileUploadExtension())->run();
