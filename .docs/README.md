# Contributte / Fileupload

jQuery-FileUpload is component which extends UploadControl in Nette form using [blueimp/jQuery-File-Upload](https://github.com/blueimp/jQuery-File-Upload).

## Content

## Setup

```
extensions:
    fileUpload: Zet\FileUpload\FileUploadExtension
```

### Configuration of DI extension options

```
fileUpload:
	maxFiles: 10
	maxFileSize: 2M
	fileFilter: Zet\FileUpload\Filter\ImageFilter
	uploadModel: App\Model\MyUploadModel
	uiMode: # full nebo minimal
```

## CSS and JS

### Copy files
Jelikož komponenta využívá externí soubory a styly pro fileupload, je potřeba přesunout ze zdrojové složky komponenty obsah složky WWW do složky WWW ve vašem projektu.

This component needs for its precise functionality some third party scripts and styles. Copy all required assets from aassets folder to www root of project.

### Loading CSS and JS in layout.latte

```
{\Zet\FileUpload\FileUploadControl::getHead($basePath)}
{\Zet\FileUpload\FileUploadControl::getScripts($basePath)}
``` 

## Usage

Komponenta nabízí vlastní možnost zpracování uploadu souboru. K tomuto účelu zde slouží tzv. UploadModel. V základu se využívá vlastní UploadModel, který ovšem soubory fyzicky neukládá na server.


### Interface

Vlastní UploadModel musí implementovat rozhraní **\Zet\FileUpload\Model\IUploadModel**.

```php
namespace Zet\FileUpload\Model;

/**
 * Interface IUploadController
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Model
 */
interface IUploadModel {

	/**
	 * Uložení nahraného souboru.
	 * @param \Nette\Http\FileUpload $file
	 * @param array $params Pole vlastních hodnot.
	 * @return mixed Vlastní navrátová hodnota.
	 */
	public function save(\Nette\Http\FileUpload $file, array $params = []);

	/**
	 * Zpracování přejmenování souboru.
	 * @param $upload Hodnota navrácená funkcí save.
	 * @param $newName Nové jméno souboru.
	 * @return mixed Vlastní návratová hodnota.
	 */
	public function rename($upload, $newName);

	/**
	 * Zpracování požadavku o smazání souboru.
	 * @param $uploaded Hodnota navrácená funkcí save.
	 */
	public function remove($uploaded);

}
```

Interface poskytuje tři hlavní metody - save, rename a remove.

Save se volá při nahrávání souboru na server, od této metody se očekává libovolná návratová hodnota, která bude poté navrácena při získávání hodnoty pole při zpracování formuláře. Pokud byly metodou **FileUploadControl::setParams()** nastaveny vlastní parametry pro odesílané soubory, předávají se jako pole do parametr **$params**.

Metoda remove je k odstranění souboru po nahrání, jejím parametrem je hodnota, která byla navrácena funkcí save.

Metoda rename slouží k přejmenování nahraného souboru. Jejím prvním parametrem je hodnota, která byla vrácena funkcí save a nový název souboru. Rename by měl vracet stejnou hodnotu jako save.

### Nastavení modelu

Vlastní modelovou třídu lze zapsat do fileupload extension v config.neon.
```
fileUpload:
    uploadModel: App\Model\MyUploadModel
```