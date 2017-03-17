<?php

namespace Zet\FileUpload;

use Nette;

/**
 * Class Nette22BridgeExtension
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload
 */
final class Nette22BridgeExtension extends \Nette\DI\CompilerExtension {
	
	public function loadConfiguration() {
	
	}
	
	/**
	 * @param \Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$init = $class->methods["initialize"];
		
		$init->addBody('
			$set = new \Latte\Macros\MacroSet($this->getService(?)->getCompiler());
			$set->addMacro("php", "<?php", "?>");
		', $this->getContainerBuilder()->getByType('\Lette\Engine'));
		$init->addBody('
			\Nette\Utils\Html::extensionMethod("addHtml", function(\Nette\Utils\Html $html, $child) {
				if($child instanceof \Nette\Utils\Html) {
					$html->add($child);
				} else {
					$html->setHtml($child);
				}
			});
		');
	}
}