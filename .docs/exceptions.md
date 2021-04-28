## Server-Side Controller
Na straně controlleru lze z UploadModelu vyhazovat libovolné výjimky. Výjimky jsou při zpracování odchyceny a uploaderu se odešle JSON odpoveď s informacemi o chybě.

## Client-Side
Pokud při nahrávání souboru bude ze serveru navrácena chyba, bude nahrazen v seznamu souborů jeho název chybovou hláškou. V případě, že aplikace není v provozním režimu, je informace s chybovou hláškou z výjimky vypsána jako error do konzole.