# Datenschutz

`tw_base` legt keine eigenen Tabellen an und speichert selbst keine personenbezogenen Daten außer den Urheberangaben von Dateien. Diese Seite nennt alle Stellen, an denen die Extension solche Daten verarbeitet, für das Verzeichnis der Verarbeitungstätigkeiten einer Website, die sie einsetzt.

## Urheberangaben von Dateien

- **Daten:** Name der Person oder Organisation, die ein Werk geschaffen hat (`creator` von EXT:filemetadata), ihre Adresse im Web (`tx_twbase_creator_url`) und das Jahr der Entstehung, jeweils in den Metadaten einer Datei in `sys_file_metadata`.
- **Herkunft:** Die Redaktion trägt sie im Backend ein; der Upgrade-Wizard `twBase_attributionFrom4x` überträgt die Werte aus den Spalten von 4.x.
- **Zweck:** Die Urheberangabe, die Lizenzen wie CC BY verlangen. `base:file.attribution` zeigt sie öffentlich zusammen mit der Datei.
- **Löschen:** Mit den Metadaten der Datei, also mit der Datei selbst, oder indem die Redaktion die Felder leert. Die alten Spalten von 4.x bleiben, bis der Datenbankvergleich sie entfernt.

## Mails

- **Daten:** Name und Mailadresse der Empfängerin oder des Empfängers, die die aufrufende Extension dem `Mailer` übergibt, und die Inhalte, die sie in die Mail schreibt; dazu Absender, Kontaktadresse und Impressum aus den Einstellungen der Site.
- **Zweck:** Der Versand der Mail, die die aufrufende Extension auslöst.
- **Speichern und Löschen:** `tw_base` speichert nichts davon. Die Mail geht an den Mailer von TYPO3; ob sie dort in einer Warteschlange oder einem Log bleibt, entscheidet die Konfiguration der Installation unter `$GLOBALS['TYPO3_CONF_VARS']['MAIL']`. Die Warnung im Log, wenn das Impressum fehlt, nennt nur die Seite und die Site.

## Sonst

Strukturierte Daten, Überschriften, Bilder und Links geben aus, was Templates und Datensätze ihnen übergeben, und speichern nichts. Die Beispiele der Komponenten laden Datensätze, die ihre Definition nennt, und legen keine an.
