# Dokumentace projektu IPP (Principy programovacích jazyků a OOP) 2019/2020
## první čáast (``parser.php``)
### autor: David Rubý (xrubyd00)
Tato část projektu se soustředí na parsování jazyka ``IPPcode20`` a generování ``XML`` stromu v jazyce ``PHP``.
Moje implementace vypadá následovně:

- První část zdrojového kódu je dictionary, kde klíč je jméno instrukce a hodnota je pole, které obsahuje tolik polí, kolik instrukce potřebuje argumentů. Tato pole obsahují řetězce, které určují, co se má v daném argumentu kontrolovat a generovat.
- Postupně se načítají řádky ze standartního vstupu, ze kterých se pomocí regulárních výrazů odstraňují komentáře a prázdné řádky.
- Zkontroluje se přítomnost hlavičky.
- Řádky se rozdělí podle mezer do pole (první položka pole je jméno instrukce a každá další položka reprezetuje 1 argument).
- Pro každý argument se zkontroluje, jaké možnosti můžou nastat a všechny se zkontrolují ve specializovaných funkcích... Pokud jedna z nich prošla přes regulární výraz a byl vygenerován XML záznam, vrací se true...
- Zkontroluje se, jestli aspoň některá z nich prošla...Každá funkce vrací true, nebo false, které se ukládají do proměnných... kontroluje se pomocí operátoru ``\\`` na všech proměnných, které nesou navrátovou hodnotu. 
- Následně se načte další řádek a průběh se opakuje až do konce dokumentu.
- Po dosažení konce souboru se XML záznam vypíše na standartní výstup.