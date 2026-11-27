# Anweisungen für KI-Agenten

Dieses Projekt folgt den [tollwerk guidelines](https://code.tollwerk.net/tollwerk/guidelines). Sie sind verbindlich für Menschen und KI-Agenten. Vor jeder Änderung lesen; in PHP-Projekten liegen sie nach `composer install` auch in `vendor/tollwerk/guidelines/guidelines/`.

## Profile

Dieses Projekt übernimmt die Basis der guidelines und diese Profile, wie in `guidelines.json` eingestellt:

- `typo3`
- `frontend`

## Über dieses Projekt

`tw_base` bringt Grundlagen für Websites mit TYPO3 14: Überschriften mit passender Ebene, strukturierte Daten als JSON-LD, responsive Bilder, Informationen zu Links, die Urheberangaben von Dateien, die Infrastruktur für Fluid-Komponenten und barrierefreie Mails. Die Dokumentation ist Deutsch, Code, Kommentare und Commit-Nachrichten sind Englisch.

- **Prüfungen:** `composer check` führt `guidelines gate` aus, dieselben Prüfungen wie die Pipeline, nach `npm ci` auch die für CSS und JavaScript.
- **Entwicklung:** Websites binden diesen Klon während der Entwicklung ein; getestet wird die Extension für sich allein in einer leeren Instanz von TYPO3.
- **Ältere Fassungen:** Der Stand vor TYPO3 14 liegt im Zweig `4.x`.

## Regeln dieses Projekts

- **Docblocks:** `@category Tollwerk`, `@package Tollwerk\TwBase`.
- **Geprüfte Ordner:** `Classes`, `Configuration` und `tests`, statt `packages` und `tests` einer TYPO3-Anwendung; eine Extension hat ihren Code an der Wurzel.

## Abweichungen von den guidelines

Keine. Eine Abweichung braucht die Freigabe des Maintainers und steht hier mit ihrem Grund.
