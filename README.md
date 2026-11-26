# tw_base

`tw_base` bringt Grundlagen für Websites mit TYPO3 14: Überschriften mit passender Ebene, strukturierte Daten als JSON-LD, responsive Bilder, Informationen zu Links, die Urheberangaben von Dateien und barrierefreie Mails.

## Installation

Für TYPO3 14 im Composer-Modus:

```sh
composer require tollwerk/tw-base
```

Die Fassung für ältere Versionen von TYPO3 liegt im Zweig `4.x`.

## Mails

`Tollwerk\TwBase\Mail\Mailer` verschickt Mails mit `FluidEmail` des Core, immer als Text und als HTML. Das Layout `TwBase` gibt dem HTML die Sprache der Seite als `lang`, den Betreff als Titel und als Überschrift und eine Breite, die sich gut lesen lässt; die Gestaltung steht inline, weil Mailprogramme Stylesheets oft ignorieren. Der Textteil hat dieselben Abschnitte ohne Markup.

Eine Extension legt ihre Vorlagen unter einem eigenen Schlüssel in `$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths']` ab, je eine Datei `.fluid.html` und `.fluid.txt` mit `<f:layout name="TwBase" />` und dem Abschnitt `Main`:

```php
$mailer->send($request, new Address($email, $name), 'TwAccounts/Code', $subject, ['code' => $code]);
```

Die Vorlage erhält die übergebenen Variablen, dazu `subject` und `languageCode`.

## Entwicklung

```sh
composer install
composer check
```

`composer check` führt alle Prüfungen der [tollwerk guidelines](https://code.tollwerk.net/tollwerk/guidelines) aus, wie die Pipeline. Die Functional-Tests bauen eine leere Instanz von TYPO3 auf SQLite und brauchen dafür `pdo_sqlite`.

## Lizenz

GPL-2.0-or-later, siehe [LICENSE.md](LICENSE.md).
