# All-In-One WordPress Restaurant Plugin

Dieses Plugin bietet eine moderne Verwaltung von Speise- und Getränkekarten für WordPress. Die Funktionen sind in einzelne Klassen aufgeteilt und können leicht erweitert werden.

## Features

- Eigene Post Types für Speisen, Getränke und Inhaltsstoffe
- Shortcodes für die Ausgabe im Frontend
- REST-API Endpunkte `\aorp\v1\foods` und `\aorp\v1\drinks`
- Einstellungsseite mit vorbereiteter Lizenzverwaltung
- Import/Export Platzhalter
- Darkmode Umschalter

## Installation

1. Plugin in den Ordner `wp-content/plugins` kopieren
2. Im Backend aktivieren

## Shortcodes

- `[speisekarte columns="2"]` – zeigt alle Speisen, optional 2 oder 3 Spalten
- `[getraenkekarte columns="2"]` – zeigt alle Getränke, optional 2 oder 3 Spalten
- `[restaurant_lightswitcher]` – Button zum Umschalten des Darkmode

## Entwicklerhinweise

Die Hauptfunktionen befinden sich im Verzeichnis `includes/`. Jede Komponente ist über Filter und Actions erweiterbar. Ein einfacher Autoloader lädt alle Klassen automatisch.
