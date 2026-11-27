# Changelog

Alle nennenswerten Änderungen an der Extension. Das Format folgt [Keep a Changelog](https://keepachangelog.com/de/1.1.0/), und das Projekt nutzt [semantische Versionen](https://semver.org/lang/de/); die Hauptversion folgt der von TYPO3.

## Unreleased

### Hinzugefügt

- `Mailer` und das Layout `TwBase` für Mails als Text und barrierefreies HTML.
- Fluid-Komponenten unter `Tollwerk\Components` mit dem Präfix `twc` und der Priorität 10; ein Listener meldet die Ressourcen jeder gerenderten Komponente beim AssetCollector an, `Examples` liest die Beispiele neben dem Template und `ExampleRenderer` rendert sie; `ExampleEndpoint` rendert ein Beispiel im Frontend seiner Seite und in seiner Sprache, über eine signierte Adresse, und `Records` lädt die Datensätze, die Beispiele für Models nennen.
- Die gemeinsame Laufzeit für JavaScript der Komponenten: `enhance()` unter `@tollwerk/tw-base/JavaScript/enhance.js`.
- Komponenten für Mails unter `twc:mail.*`: Rahmen, Fußzeile, Absatz, Button, Code, Angaben und Hinweis, gestaltet über Tokens, die eine Site in den Einstellungen `twBase.mail.tokens.*` ändert; `StyleInliner` schreibt die Styles beim Versand inline, mit `tijsverkoyen/css-to-inline-styles`.
- Das Site Set `tollwerk/tw-base` mit Absender, Kontaktadresse und Impressum für die Fußzeile der Mails; Antworten gehen an die Kontaktadresse.
- Ein Verzeichnis der Mails aller Extensions aus `Configuration/Mails.php`, `Mailer::composeExample()` und `MailInspector` für die Tests, auch für den Kontrast.

### Geändert

- Neu geschrieben für TYPO3 14, bisher als Pfad-Paket in der Website des Accessibility Club entwickelt. Die Fassung davor liegt im Zweig `4.x`.
