All-In-One-WordPress-Restaurant-Plugin (AIWRP)

Version: 1.5
Autor: Dein Name

## Funktionen

- Digitale Speisekarte mit Suchfunktion
- Getränkekarte inklusive Legende der Inhaltsstoffe je Kategorie
- Dark‑Mode Umschalter mit auswählbaren Icon-Sets oder eigenen Symbolen
- Import/Export mit Historie und Mustervorlagen (CSV, JSON, YAML)
- Widgets und Shortcodes für Speisekarte, Getränkekarte, Lightswitcher und Leaflet-Karte
- Grid‑Overlay Generator für individuelle Rasters
- Verwaltung einer Leaflet-Karte zur Einbettung über Shortcode
- Umfangreiche Einstellungen für Schriftgrößen, Spaltenanzahl und Farben

## Installation

1. ZIP hochladen über **Plugins → Installieren → Plugin hochladen**
2. Plugin aktivieren
3. Kategorien und Speisen bzw. Getränke unter den entsprechenden Menüpunkten anlegen

## Shortcodes

- `[speisekarte]` – gibt die Speisekarte aus
- `[getraenkekarte]` – zeigt die Getränkekarte
- `[restaurant_lightswitcher]` – Dark‑Mode Schalter
- `[aio_leaflet_map]` – Leaflet-Karte
- `[wp_grid_menu_overlay]` – Grid-Overlay

## Leaflet Karte

Über **Karten** legst du Breiten- und Längengrad, Zoomstufe und einen Text für das Popup fest. Das Popup enthält automatisch einen Link zur Navigation.

```
[aio_leaflet_map]
```

## Dark‑Mode Icons

Im Bereich **Dark Mode** wählst du ein Icon-Set oder lädst eigene Bilder hoch. Anschließend bestimmst du die Farben des Dark Modes.

## Import/Export

Im Bereich **Import/Export** findest du Beispieldateien, die dir beim Aufbau eigener CSV-, JSON- oder YAML-Dateien helfen. Nach Anpassung lädst du die Datei wieder hoch und alle Daten werden übernommen.

## Grid‑Vorlagen

Unter **Grid-Vorlagen** erstellst du Rasterlayouts für den `[wp_grid_menu_overlay]`‑Shortcode. Jede Zelle kann einen eigenen Inhalt oder Shortcode enthalten. Auf mobilen Geräten werden die Zellen automatisch untereinander angezeigt.
