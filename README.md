# tw_base

`tw_base` bringt Grundlagen für Websites mit TYPO3 14: Überschriften mit passender Ebene, strukturierte Daten als JSON-LD, responsive Bilder, Informationen zu Links und die Urheberangaben von Dateien.

## Installation

Für TYPO3 14 im Composer-Modus:

```sh
composer require tollwerk/tw-base
```

Die Fassung für ältere Versionen von TYPO3 liegt im Zweig `4.x`.

## Entwicklung

```sh
composer install
composer check
```

`composer check` führt alle Prüfungen der [tollwerk guidelines](https://code.tollwerk.net/tollwerk/guidelines) aus, wie die Pipeline. Die Functional-Tests bauen eine leere Instanz von TYPO3 auf SQLite und brauchen dafür `pdo_sqlite`.

## Lizenz

GPL-2.0-or-later, siehe [LICENSE.md](LICENSE.md).
