# tw_base

`tw_base` bringt Grundlagen für Websites mit TYPO3 14: Überschriften mit passender Ebene, strukturierte Daten als JSON-LD, responsive Bilder, Informationen zu Links, die Urheberangaben von Dateien, die Infrastruktur für Fluid-Komponenten aller Pakete von tollwerk und barrierefreie Mails.

Texte der Mails, des Backends und der Einstellungen gibt es auf Englisch, Deutsch, Spanisch, Französisch und Italienisch.

## Installation

Für TYPO3 14 im Composer-Modus:

```sh
composer require tollwerk/tw-base
```

Die Fassung für ältere Versionen von TYPO3 liegt im Zweig `4.x`.

## Komponenten

`tw_base` meldet seine Fluid-Komponenten unter dem Namespace `Tollwerk\Components` mit der **Priorität 10** an und bindet dafür das Präfix `twc`, das alle Pakete von tollwerk für ihre Komponenten teilen. Eine Extension nimmt eine eigene Priorität unter 100, ein Sitepackage eine ab 100; die höhere ersetzt eine Komponente unter demselben Namen.

```php
// Configuration/Fluid/ComponentCollections.php
return [
    'Tollwerk\\Components' => [
        'templatePaths' => [40 => 'EXT:tw_events/Resources/Private/Components'],
    ],
];
```

Zu `Event/Teaser/Teaser.fluid.html` in `Resources/Private/Components` gehören:

- **Ressourcen** unter demselben Pfad in `Resources/Public/Components`: `Teaser.css`, `Teaser.critical.css` für CSS, das inline in den Kopf kommt, und `Teaser.js` als ES-Modul, jeweils nur, wenn es sie gibt. Ein Listener auf `RenderComponentEvent` meldet sie für jede gerenderte Komponente einmal beim AssetCollector an, aus dem Paket, dessen Template gilt; der Seiten-Cache behält sie. Das Stylesheet der Komponente für die Seite einer Site, etwa `twc:a11yclub.page`, kommt vor allen anderen, denn jede Komponente der Site baut darauf auf, auch wenn die Seite ihren Inhalt zuerst rendert. Für ES-Module bildet das Paket `Resources/Public/` oder einen Ordner darüber in `Configuration/JavaScriptModules.php` ab, etwa `'@tollwerk/tw-events/' => 'EXT:tw_events/Resources/Public/'`, und das Modul importiert andere über diese Bezeichner.
- **Beispiele** in `Teaser.examples.php` neben dem Template: `requires` nennt die Komponenten, in denen sie nur funktioniert, `examples` ihre Varianten und Zustände mit Bezeichnung, optionaler Gruppe, Argumenten, Slots und `within`, dazu optional die Seite (`page`) und die Sprache (`language`), auf der ein Beispiel rendert. Ein Argument, das ein Model erwartet, nennt einen Datensatz, etwa `['record' => 'tx_twevents_event', 'uid' => 3]`, auch in einer Liste; `Records` lädt ihn als Model der Klasse, die Extbase seiner Tabelle zuordnet. Ohne `model` gilt die eine Klasse, die eine `Configuration/Extbase/Persistence/Classes.php` der Tabelle zuordnet. Gehören mehrere Klassen zu einer Tabelle oder ordnet Extbase die Klasse nur nach ihrem Namen zu, wie `Vendor\Ext\Domain\Model\Thing` der Tabelle `tx_ext_domain_model_thing`, nennt der Verweis die Klasse als `model`; so nennt er auch Dateien und Referenzen auf Dateien des Core, wie sie die Inhaltselemente des Core übergeben, etwa `['record' => 'sys_file_reference', 'uid' => 1, 'model' => FileReference::class]`. Den Aufbau beschreibt `Tollwerk\TwBase\Component\Example\Examples`.

Die gemeinsame Laufzeit für JavaScript liegt unter `@tollwerk/tw-base/JavaScript/`. `enhance()` aus `enhance.js` ruft eine Funktion einmal für jedes Element auf, das zu einem Selektor passt, auch für Elemente, die später ins Dokument kommen. Wirft die Funktion für ein Element, meldet `enhance()` den Fehler mit `reportError()` und macht mit den übrigen weiter:

```js
import { enhance } from '@tollwerk/tw-base/JavaScript/enhance.js';

enhance('.FormField__input', (input) => { /* … */ });
```

Mit dem Modul einer Komponente meldet der Listener auch `enhance.js` an. So steht `@tollwerk/tw-base/` in der Import Map der Seite, auch wenn das Paket der Komponente `tw_base` nicht unter `dependencies` nennt; TYPO3 gibt im Frontend nur die Präfixe der Pakete aus, deren Module die Seite lädt, und die ihrer Abhängigkeiten.

`ExampleRenderer` rendert eine Komponente mit einem ihrer Beispiele, eingebettet in die Beispiele der Komponenten, die sie voraussetzt. Damit Links, Übersetzungen und Texte aus dem Rich-Text-Editor wie auf der Website arbeiten, rendert ein Beispiel im Frontend seiner Seite und in seiner Sprache: `ExampleEndpoint::uri()` liefert die Adresse der Seite mit Komponente, Beispiel und einer Signatur aus dem Schlüssel der Installation. Zwei Middlewares erkennen sie, schalten für diesen Request den Seiten-Cache ab und antworten, sobald das TypoScript der Seite bereitsteht, mit dem HTML des Beispiels statt mit der Seite; ohne gültige Signatur zeigt die Adresse nur die Seite. Die Tests eines Pakets rendern so jedes Beispiel, mit den Datensätzen aus ihren Fixtures und einer Site mit TypoScript; die Liste dafür liefert `Examples::find()` ohne TYPO3, für einen Data Provider:

```php
public static function examples(): iterable
{
    foreach (Examples::find(\dirname(__DIR__, 2) . '/Resources/Private/Components') as $component => $file) {
        foreach (array_keys(Examples::fromFile($file)->examples) as $example) {
            yield $component . '#' . $example => [$component, $example];
        }
    }
}

public function exampleRenders(string $component, string $example): void
{
    $site = $this->get(SiteFinder::class)->getSiteByIdentifier('example');
    $uri = $this->get(ExampleEndpoint::class)->uri($site, $component, $example);
    $html = (string) $this->executeFrontendSubRequest(new InternalRequest((string) $uri))->getBody();
    // …
}
```

## Mails

`Tollwerk\TwBase\Mail\Mailer` verschickt Mails mit `FluidEmail` des Core, immer als Text und als HTML. Der Mailer erzeugt sie mit der `TemplatedEmailFactory`, also gelten auch die Pfade einer Site in `email.templateRootPaths` und `email.layoutRootPaths`. Eine Extension legt ihre Vorlagen unter einer eigenen Zahl in `$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths']` ab, denn TYPO3 ordnet die Pfade nach ihren Zahlen und hängt die einer Site mit der nächsten Zahl an; ein Schlüssel aus Text bricht die Mails aller Extensions ab. Der Core belegt 0 und 10, `tw_base` legt sein Layout unter 20 ab. Eine Vorlage hat je eine Datei `.fluid.html` und `.fluid.txt` mit `<f:layout name="TwBase" />`, dem Abschnitt `Main` und dem Abschnitt `Reason`, der in einem Satz sagt, warum die Person die Mail bekommt:

```php
$mailer->send($request, new Address($email, $name), 'TwAccounts/Code', $subject, ['code' => $code]);
```

Die Vorlage erhält die übergebenen Variablen, dazu `subject`, `languageCode`, `website`, `sender` und `footerLinks`.

- **Rahmen:** Das Layout setzt das HTML in `twc:mail.frame` mit Sprache, Betreff als Titel und einziger Überschrift der ersten Ebene, Namen der Website und einer Fassung für den dunklen Modus, und darunter `twc:mail.footer`. Für Classic Outlook steht eine Tabelle mit `role="presentation"` in bedingten Kommentaren, die alle anderen Programme nicht sehen.
- **Fußzeile aus der Site:** Das Site Set `tollwerk/tw-base` hat die Einstellungen `twBase.mail.sender` für Absender und Anschrift, `twBase.mail.contact` für die Adresse für Fragen, an die auch Antworten gehen, und `twBase.mail.imprint` für die Seite mit dem Impressum. Eine Site setzt sie im Modul „Sites“ des Backends oder in ihrer `settings.yaml`. Zeigt die Site die Seite des Impressums nicht, weil sie fehlt, gelöscht, verborgen oder abgelaufen ist oder zu einer anderen Site gehört, geht die Mail ohne den Link hinaus, und eine Warnung steht im Log.
- **Bausteine:** `twc:mail.paragraph`, `twc:mail.button`, `twc:mail.code` für einen Code auf eigener Zeile, `twc:mail.facts` für eine Liste von Angaben und `twc:mail.notice` für einen Hinweis. Der Textteil kommt ohne sie aus.
- **Gestaltung:** Die Komponenten tragen nur Klassen; ihr CSS liegt wie bei jeder Komponente unter `Resources/Public/Components/Mail`, die Tokens stehen als Custom Properties in `:root` von `Frame.css`. Beim Versand ersetzt `StyleInliner` jedes Token durch seinen Wert und schreibt die Regeln in die Attribute `style`, weil viele Mailprogramme weder Stylesheets noch Custom Properties kennen; At-Rules wie `@media` für den dunklen Modus kommen in jeder Tiefe in den Kopf. Tokens aus `:root` gelten auf der obersten Ebene; ein `:root` in einem Block wie `@media` gilt nur in diesem Block, über den anderen. Eine Site ändert ein Token mit einer Einstellung unter `twBase.mail.tokens`, im Backend mit Farbwählern: `twBase.mail.tokens.color.accent` setzt `--mail-color-accent`, `twBase.mail.tokens.dark.color.accentText` setzt `--mail-dark-color-accent-text`. Weitere Regeln bringt sie mit `twBase.mail.stylesheet` mit, einem Stylesheet, das nach denen der Komponenten kommt und die Tokens nutzen kann. Ein Test hält die Vorgaben in `Frame.css` und in den Einstellungen gleich.
- **Verzeichnis:** Jede Extension meldet ihre Mails in `Configuration/Mails.php` an, mit Schlüssel, Vorlage, Zweck, Empfänger und Beispielen; den Aufbau beschreibt `MailDefinition`. `MailRegistry` liefert alle Mails, `Mailer::composeExample()` setzt eine mit einem ihrer Beispiele zusammen.
- **Prüfungen:** `MailInspector::inspect()` nennt, was eine Mail unzugänglich macht: fehlende Sprache, fehlender Titel oder fehlendes Farbschema, nicht genau eine Überschrift der ersten Ebene, Tabellen, Bilder ohne Alternativtext, Links ohne sprechenden Text, Links oder Codes, die im Textteil fehlen, Tokens ohne Wert und Kontraste unter WCAG 2.2 AA, gemessen an den Farben in den Attributen `style`, in Hex oder `rgb()`; eine durchscheinende Farbe oder eine andere Schreibweise meldet die Prüfung als nicht prüfbar. So fällt auch eine Farbe einer Site auf, die zu wenig Kontrast hat. Die Tests jeder Extension prüfen damit alle Beispiele ihrer Mails: `self::assertSame([], $inspector->inspect($mail))`.

## Entwicklung

```sh
composer install
composer check
```

`composer check` führt alle Prüfungen der [tollwerk guidelines](https://code.tollwerk.net/tollwerk/guidelines) aus, wie die Pipeline. Die Functional-Tests bauen eine leere Instanz von TYPO3 auf SQLite und brauchen dafür `pdo_sqlite`.

## Lizenz

GPL-2.0-or-later, siehe [LICENSE.md](LICENSE.md).
