# jQuery-FileUpload

jQuery-FileUpload je Nette UploadControl využívající [blueimp/jQuery-File-Upload](https://github.com/blueimp/jQuery-File-Upload) s bootstrap uživatelským rozhraním pro snadné nahrávání souborů.

## Instalace
Do vašeho bootstrap souboru stačí přidat tuto řádku:
### 1. Registrace
```php
Zet\FileUpload\FileUploadControl::register($container);
```
### 2. Skripty a CSS styly
#### 2.1. Obsah WWW složky
Obsah složky WWW překopírujte do WWW složky ve vašem projektu.
#### 2.2. Načtení skriptů a stylů
Pro načtení skriptů a stylů stačí přidat do latte souborů následující funkce:
```
{\Zet\FileUpload\FileUploadControl::getStyleSheet($basePath)}
{\Zet\FileUpload\FileUploadControl::getScripts($basePath)}
```
## Použití
Pro přidání prvku do formuláře stačí zavolat funkci addFileUpload(). Funkce má jeden povinný parametr a to název. Druhý parametr, nepovinný, pak určuje maximální počet souborů, které uživatel může nahrát.
```php
protected function createComponentMyForm() {
  $form = new \Nette\Application\UI\Form();
  
  $form->addFileUpload("upload");
  
  return $form;
}
```
