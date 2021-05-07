<?php declare(strict_types=1);

namespace Zet\FileUpload\Template\Renderer;

use Nette\Utils\Html;

/**
 * Interface IUploadRenderer
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Template\Renderer
 */
interface IUploadRenderer {
	
	/**
	 * Sestavení výchozí šablony uploaderu.
	 */
	public function buildDefaultTemplate(): Html;
	
	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 */
	public function buildFileContainerTemplate(): Html;
	
	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 */
	public function buildFileError(): Html;
}
