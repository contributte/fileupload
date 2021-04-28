Od verze 2.0.0-beta2 lze zadávat do uploaderu výchozí soubory. Tyto soubory jsou při načtení zobrazeny jako již nahrané soubory, které může uživatel smazat.

## DefaultFile
Pro přidání výchozího souboru slouží kontejner **\Zet\FileUpload\Model\DefaultFile**.

```php
class DefaultFile extends Object {
	
	/**
	 * Callback pro smazání výchozího souboru s parametry (mixed $identifier).
	 *
	 * @var array
	 */
	public $onDelete = [];
	
	/**
	 * Odkaz na náhled obrázku.
	 *
	 * @var string
	 */
	private $preview;
	
	/**
	 * Název souboru.
	 *
	 * @var string
	 */
	private $filename;
	
	/**
	 * Identifikátor souboru sloužící pro jeho smazání.
	 *
	 * @var mixed
	 */
	private $identifier;
}
```

Kontejner nabízí možnost zadat PHP callback na funkci, která provede případné smazání výchozího souboru. Dále je možné nastavit si odkaz na zobrazení náhledu obrázku, název souboru a jeho identifikátor, který slouží pro callback při mazání souboru.

Výchozí soubor lze do uploaderu přidat pomocí setDefaultFiles(), které přijímá pole souborů nebo pomocí addDefaultFiles(), který přidá jediný soubor do pole.