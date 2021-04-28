## Výchozí renderery
Fileuploader přichází s třemi předdefinovanými renderery:
* [Html5Renderer](https://github.com/JZechy/jQuery-FileUpload/blob/master/src/Template/Renderer/Html5Renderer.php)
* [Bootstrap3Renderer](https://github.com/JZechy/jQuery-FileUpload/blob/master/src/Template/Renderer/Bootstrap3Renderer.php)
* [Bootstrap4Renderer](https://github.com/JZechy/jQuery-FileUpload/blob/master/src/Template/Renderer/Bootstrap4Renderer.php)

Ve výchozím nastavení se vybírá Html5Renderer.

## Vlastní renderer
Je možné si napsat i vlastní třídu pro vykreslování uploaderu. Vše probíhá za pomoci třídy **\Nette\Utils\Html**. Pro vykreslení uploaderu je k dispozici několik základních elementů/komponent, které můžete dle libosti upravovat.
* **container**: Element, který obaluje celý uploader.
* **input**: File input, na který jsou navázané události uploaderu.
* **globalProgress**: Element, který slouží jako progress bar pro celou frontu souborů.
* **globalProgressValue**: Element, který slouží pro vypsání % nahrávaní aktuální fronty.
* **fileProgress**: Element, který slouží jako progress bar pro samotný soubor.
* **fileProgressValue**: Element, který slouží pro vypsání % nahrávání aktuálního souboru.
* **imagePreview**: Element, do kterého je vložen náhled obrázku.
* **filePreview**: Element, do kterého je vypsán typ nahrávaného souboru.
* **filename**: Element, do kterého se vypisuje název souboru.
* **delete**: Element, který slouží jako tlačítko/odkaz pro smazání souboru.
* **errorMessage**: Element, do kterého se vypisují chybové zprávy.

Všechny elementy jsou ve výchozím stavu nastaveny jako divy, výjimku tvoří pouze následující:
* **input** je input type="file"
* **delete** je button type="button"
* **imagePreview** je img

Progress bary mohou být dle potřeby HTML5 tagy &lt;progress&gt; nebo &lt;div&gt;.

### BaseRenderer
Vlastní renderer musí dědit od třídy **\Zet\FileUpload\Template\Renderer\BaseRenderer**. Tato třída má pro své potomky atribut **$elements**, ve kterém jsou v poli na přislušných indexech uloženy Html Prototypes všech komponent.

BaseRenderer obsahuje tři abstraktní metody, které je třeba implementovat.
```php
/**
 * Class BaseRenderer
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Template\Renderer
 */
abstract class BaseRenderer extends Object implements IUploadRenderer {

	/**
	 * Sestavení výchozí šablony uploaderu.
	 *
	 * @return \Nette\Utils\Html
	 */
	abstract public function buildDefaultTemplate();
	
	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 *
	 * @return \Nette\Utils\Html
	 */
	abstract public function buildFileContainerTemplate();
	
	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 *
	 * @return \Nette\Utils\Html
	 */
	abstract public function buildFileError();
}
```

Metoda **buildDefaultTemplate()** sestavuje základní šablonu rendereru, tedy to, co je vidět po načtení stránky. Každý další přidaný soubor poté využívá šablonu, která je sestavená pomocí **buuildFileContainerTemplate()**. Pokud dojde při nahrávání souboru k chybě, je využita šablona z metody **buildFileError()**.

Pokud v šabloně není potřeba nějakou komponentu využít, je možné její HTML prototype přepsat hodnotou null, při vykreslování pak nebude docházet k pokusu o její naplnění/použití.