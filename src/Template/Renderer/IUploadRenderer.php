<?php

declare(strict_types=1);

namespace Zet\FileUpload\Template\Renderer;

use Nette\Utils\Html;

/**
 * Interface IUploadRenderer
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Template\Renderer
 */
interface IUploadRenderer
{


	/**
	 * Sestavení výchozí šablony uploaderu.
	 *
	 * @return Html
	 */
	public function buildDefaultTemplate();


	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 *
	 * @return Html
	 */
	public function buildFileContainerTemplate();


	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 *
	 * @return Html
	 */
	public function buildFileError();
}
