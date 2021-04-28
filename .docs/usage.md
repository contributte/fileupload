Zaregistrovanou komponentu lze použít zavoláním metody *addFileUpload()*.
```php
protected function createComponentMyForm() {
	$form = new \Nette\Application\UI\Form;
	$form->addFileUpload("uploader");

	return $form;
}
```
Metoda *addFileUpload()* má jeden povinný parametr a to název. Dále ovšem lze využít další dva nepovinné parametry - *maximální počet souborů* a *maximální velikosti souboru*.

Maximální velikosti souboru lze předat ve formátu **3M** nebo **500K**. Pokud maximální velikost není nastavena, bude načtena maximální velikost souboru pro upload z **php.ini**.

## UI Mode
Od verze 1.2.0 lze měnit vzhled uploaderu pomocí funkce FileUploadControl::setUIMode(), funkce příjímá jako parametr konstanty UI_FULL a UI_MINIMAL.

```php
FileUploadControl::setUIMode(FileUploadControl::UI_FULL); // Plné rozhraní.
FileUploadControl::setUIMode(FileUploadControl::UI_MINIMAL); // Minimalizované rozhraní.
```

## Vlastní parametry
Od verze 1.2.1 lze předávat FileUploaderu pole vlastních parametrů, k tomuto účelu slouží metoda **FileUplaodControl::setParams()**.

```php
FileUploadControl::setParams([
  "productId" => 23,
  "userId" => 10
]);
```

Takto nastavené parametry se poté automaticky předávají do UploadModelu přes parametr **$params**.

## Zpracování
Nahrané soubory lze získat při zpracování formuláře z pole **$values**. Prvek vrací pole přesně takových hodnot, které vrací metoda **save()** UploadModelu.

## Settery
```php
FileUploadControl::setMaxFiles(25); // Nastaví maximální počet souborů.
FileUploadControl::setMaxFileSize("2M"); // Nastaví maximální velikost souboru. V tomto případě 2MB.
FileUploadControl::setUploadModel('\Model\File\UploadModel'); // Nastaví vlastní upload model.
FileUploadControl::setFileFilter('\Zet\FileUpload\Filter\ImageFilter'); // Nastaví omezení nahrávaní souborů na určené typy. Lze zapsat stringem vlastní třídu nebo použít konstanty FileUploadControl.
FileUploadControl::setUiTemplate(FileUploadControl::UI_FULL, __DIR__ . "/path/to/my/template.latte");
FileUploadControl::setParams(["productId" => 10]); // Nastavení vlastních hodnot k odeslanému souboru
```