<?php declare(strict_types = 1);

namespace Tests\Fixtures\Template\Renderer;

use Nette\Utils\Html;

class InvalidRenderer
{

	public function buildDefaultTemplate(): Html
	{
		return Html::el('div');
	}

	public function buildFileContainerTemplate(): Html
	{
		return Html::el('div');
	}

	public function buildFileError(): Html
	{
		return Html::el('div');
	}

}
