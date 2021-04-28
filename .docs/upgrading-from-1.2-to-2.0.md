## Registrace

* Z registrace odebrána položka uiMode. Nově nahrazeno nastavením Renderer.
* UI Mody jsou nahrazeny vlastní renderer class.

## Konfigurace

Do konfigurace byly přidány nové možnosti:

### renderer
Nahrazující uiMode, přejímá název třídy, která bude zajišťovat vykreslení FileUploaderu. Lze použít [předdefinované nebo vlastní](https://github.com/JZechy/jQuery-FileUpload/wiki/Rendering).

### translator
Název třídy, která má sloužit jako translator. Nepovinný parametr, pokud není zadán, uploader se pokusí vyhledat službu rozhraní **\Nette\Localization\ITranslator**.

### autoTranslate
Výchozí hodnota false. Automaticky přeloží zadané chybové hlášky.

### messages
Seznam vlastních chybových hlášek. Lze zadat již normální věty nebo výrazy pro translator.
Chybové hlášky jsou následující:
* maxFiles - Maximální počet souborů.
* maxSize - Maximální velikost souboru.
* fileTypes - Povolené typy souborů.
* fileSize - PHP chyba. Soubor je příliš velký.
* partialUpload - PHP chyba. Soubor byl nahrán částečně.
* noFile - PHP chyba. Nebyl nahrán žádný soubor.
* tmpFolder - PHP chyba. Chybí dočasná složka.
* cannotWrite - PHP chyba. Nepodařilo se zapsat soubor na disk.
* stopped - PHP chyba. Nahrávání souboru bylo přerušeno.

### uploadSettings
Seznam vlastních konfiguračních hodnot pro uploader. Tato položka slouží k vlastní úpravě blueimpova uploaderu, umožnuje zadat veškeré konfigurační vlastnosti, které uploader nabízí.