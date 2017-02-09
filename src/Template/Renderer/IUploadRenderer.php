<?php

namespace Zet\FileUpload\Template\Renderer;

/**
 * Interface IUploadRenderer
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Template\Renderer
 */
interface IUploadRenderer {
	
	/**
	 * Sestavení výchozí šablony uploaderu.
	 * @return \Nette\Utils\Html
	 */
	public function buildDefaultTemplate();
	
	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 * @return \Nette\Utils\Html
	 */
	public function buildFileContainerTemplate();
	
	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 * @return \Nette\Utils\Html
	 */
	public function buildFileError();
}