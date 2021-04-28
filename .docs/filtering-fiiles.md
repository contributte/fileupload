## Filtrování souborů
Nahrané soubory lze filtrovat pomocí jejich mimetype, případně koncovky, je třeba možné tak omezit nahrávání souborů pouze na obrázky. K tomuto účelu existují filtrační třídy.

Všechny filtry jsou instancí abstraktní třídy **\Zet\FileUpload\Filter\BaseFilter**, který implementuje základní metodiku určování správnosti souboru pomocí mimetype, případně dle koncovky souboru. Tento základní filtr implementuje rozhraní **\Zet\FileUpload\Filter\IMimeTypeFilter**.

## Základní filtry
```php
FileUploadControl::FILTER_IMAGES; // Povolí nahrávat pouze obrázky png, jpeg, jpg, gif.
FileUploadControl::FILTER_DOCUMENTS; // Povolí nahrávat pouze dokumenty typu txt, doc, docx, xls, xlsx, ppt, pptx, pdf.
FileUploadControl::FILTER_ARCHIVE; // Povolí nahrávat soubory zip, tar, rar, 7z.
FileUploadControl::FILTER_AUDIO; // Povolí nahrávat pouze soubory mp3, ogg, aiff.

FileUploadControl::setFileFilter("Konstanta nebo vlastní třída"); // Nastaví třídu, podle které se bude určovat, zda soubor lze nahrát
```
Metoda setFileFilter příjímá jako parametr string, ve kterém je napsán název třídy, např. **Zet\FileUpload\Filter\ImageFilter**.

## Vlastní filtr
### Užití BaseFilter
Chcete-li využít základní metodiku určování správnosti typu souboru, lze vytvořit vlastní třídu, která bude potomkem **\Zet\FileUpload\Filter\BaseFilter**.

Takto vytvořená třída musí mít vlastní implementaci funkce getMimeTypes(), které vrací pole mimetypů souboru a jejich koncovek, např.
```php
/**
 * Vrátí seznam povolených typů souborů s jejich typickou koncovkou.
 * @example array("text/plain" => "txt")
 * @return string[]
 */
protected function getMimeTypes() {
	return array(
		"image/png" => "png",
		"image/pjpeg" => "jpeg",
		"image/jpeg" => "jpg",
		"image/gif" => "gif",
	);
}
```

### Vlastní metodika filtrování 
Pokud si chcete vytvořit vlastní způsob určování typu souboru, lze vytvořit vlastní třídu, která ale musí implementovat rozhraní **\Zet\FileUpload\Filter\IMimeTypeFilter**.

```php
/**
 * Interface IMimeTypeFilters
 * Rozhraní pro kontrolu Mime typu souboru.
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Filter
 */
interface IMimeTypeFilter {

	/**
	 * Ověří mimetype předaného souboru.
	 * @param \Nette\Http\FileUpload $file Nahraný soubor k ověření.
	 * @return bool Má soubor správný mimetype?
	 */
	public function checkType(\Nette\Http\FileUpload $file);

	/**
	 * Vrátí seznam povolených typů souborů.
	 * @return string
	 */
	public function getAllowedTypes();
}
```