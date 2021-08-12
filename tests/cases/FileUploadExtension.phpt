<?php declare(strict_types = 1);

namespace Tests\Cases;

use InvalidArgumentException;
use Nette\DI\InvalidConfigurationException;
use Nette\InvalidStateException;
use Nette\UnexpectedValueException;
use Ninjify\Nunjuck\TestCase\Nette\BaseContainerTestCase;
use Tester\Assert;
use Tests\Filters\InvalidFilter;
use Tests\Filters\ValidFilter;
use Tests\Helpers;
use Tests\Models\InvalidUploadModel;
use Tests\Models\ValidUploadModel;
use Tests\Renderers\InvalidRenderer;
use Zet\FileUpload\Filter\IMimeTypeFilter;
use Zet\FileUpload\Model\IUploadModel;
use Zet\FileUpload\Template\Renderer\Bootstrap4Renderer;
use Zet\FileUpload\Template\Renderer\IUploadRenderer;

$container = require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class FileUploadExtension extends BaseContainerTestCase
{

	public function testConfig(): void
	{
		Assert::noError(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'maxFiles' => 10,
					'maxFileSize' => '2M',
					'fileFilter' => 'Zet\FileUpload\Filter\ImageFilter',
				],
			]);
		});

		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'maxFiles' => '10',
					'maxFileSize' => '2M',
					'fileFilter' => 'Zet\FileUpload\Filter\ImageFilter',
					'uiMode' => 'full',
				],
			]);
		}, InvalidConfigurationException::class, "Unexpected item 'fileUpload › uiMode'.");

		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'maxFiles' => null,
				],
			]);
		}, InvalidConfigurationException::class);

		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'messages' => 'hello',
				],
			]);
		}, InvalidConfigurationException::class, "The item 'fileUpload › messages' expects to be array, 'hello' given.");
	}

	public function testUploadModels(): void
	{
		$uploadModel = 'App\Model\MyUploadModel';
		Assert::exception(function () use ($uploadModel): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'maxFiles' => 10,
					'maxFileSize' => '2M',
					'fileFilter' => 'Zet\FileUpload\Filter\ImageFilter',
					'uploadModel' => $uploadModel,
				],
			]);
		}, InvalidArgumentException::class, sprintf('Předaný model %s neexistuje.', $uploadModel));

		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'uploadModel' => InvalidUploadModel::class,
				],
			]);
		}, InvalidStateException::class, 'Předaný model není instancí ' . IUploadModel::class);

		Assert::noError(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'uploadModel' => ValidUploadModel::class,
				],
			]);
		});
	}

	public function testFilters(): void
	{
		$fileFilter = 'App\Model\MyFilter';
		Assert::exception(function () use ($fileFilter): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'maxFiles' => 10,
					'maxFileSize' => '2M',
					'fileFilter' => $fileFilter,
				],
			]);
		}, InvalidArgumentException::class, sprintf('Třída pro filtrování souborů %s neexistuje.', $fileFilter));

		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'fileFilter' => InvalidFilter::class,
				],
			]);
		}, UnexpectedValueException::class, 'Třída pro filtrování souborů neimplementuje rozhraní ' . IMimeTypeFilter::class);

		Assert::noError(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'fileFilter' => ValidFilter::class,
				],
			]);
		});
	}

	public function testRenderers(): void
	{
		$renderer = 'App\Model\MyRenderer';
		Assert::exception(function () use ($renderer): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'maxFiles' => 10,
					'maxFileSize' => '2M',
					'renderer' => $renderer,
				],
			]);
		}, InvalidArgumentException::class, sprintf('Třída pro vykreslování formuláře %s neexistuje.', $renderer));

		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'renderer' => InvalidRenderer::class,
				],
			]);
		}, InvalidStateException::class, 'Třída pro vykreslování formuláře neimplementuje rozhraní ' . IUploadRenderer::class);

		Assert::noError(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'fileUpload' => [
					'renderer' => Bootstrap4Renderer::class,
				],
			]);
		});
	}

}

(new FileUploadExtension($container))->run();
