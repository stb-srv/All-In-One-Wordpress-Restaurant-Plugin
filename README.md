All-In-One-WordPress-Restaurant-Plugin (AIWRP)

Version: 1.4.0
Autor: Dein Name

## Übersicht

Dieses Plugin bietet alles, was Restaurant-Personal ohne IT-Kenntnisse benötigt:

1. Digitale Speisekarte
2. Lightswitcher (Dark Mode)
3. Import/Export & Historie
4. Widgets für Speisekarte und Lightswitcher
5. Einfache Verwaltung aller Daten auf einer Seite inklusive Bearbeiten und Löschen von Speisen sowie Filterfunktion
6. Kategorien besitzen eine Bezeichnung und lassen sich filtern und bearbeiten
7. Import/Export berücksichtigt Kategorien, Inhaltsstoffe und weitere Einstellungen
8. Spaltenanzahl der Speisekarte (1–3) über Einstellungen wählbar
9. Schriftgrößen für Nummer, Titel, Beschreibung und Preis über den Einstellungen-Bereich Darstellung per Dropdown anpassbar
10. Preise werden stets mit dem Euro-Zeichen ("€") angezeigt
11. Eigener Shortcode-Generator für individuelle Overlays

## Installation

1. ZIP hochladen über Plugins → Installieren → Plugin hochladen  
2. Plugin aktivieren  
3. Alle Kategorien und Speisen lassen sich unter
   "Speisekarte → Verwaltung" bequem anlegen und bearbeiten

## Shortcodes

- [speisekarte] – Zeigt die Speisekarte an
- [restaurant_lightswitcher] – Dark Mode Switcher
Die Widgets "Speisekarte" und "Lightswitcher" können ebenfalls in Sidebars verwendet werden.

## Dark‑Mode Icons

Im Menüpunkt **Dark Mode** legst du Aussehen und Verhalten des Lichtschalters fest.
Zunächst wählst du ein passendes Icon-Set oder lädst eigene Symbole hoch. Anschließend bestimmst du mit dem Template lediglich die Farben des Dark Modes.

### Verfügbare Icon‑Sets

| Name      | Symbole |
|-----------|---------|
| default   | ☀️ / 🌙 |
| alt       | 🌞 / 🌜 |
| minimal   | 🔆 / 🌑 |
| eclipse   | 🌞 / 🌚 |
| sunset    | 🌇 / 🌃 |
| cloudy    | ⛅ / 🌙 |
| simple    | ☼ / ☾ |
| twilight  | 🌄 / 🌌 |
| starry    | ⭐ / 🌜 |
| morning   | 🌅 / 🌠 |
| bright    | 🔆 / 🔅 |
| flower    | 🌻 / 🌑 |
| smiley    | 😀 / 😴 |
| custom    | eigene Icons |

### Eigene Icons hochladen

1. Wähle im Dropdown **Icon Set** den Eintrag **Eigene Icons**.
2. Klicke bei "Eigenes Icon hell" bzw. "Eigenes Icon dunkel" auf *Bild auswählen* und lade jeweils eine 32x32‑PNG mit transparentem Hintergrund hoch.
3. Speichere die Einstellungen.

Kostenlose Icons findest du zum Beispiel auf [flaticon.com](https://www.flaticon.com).


### Mustervorlagen für den Import

Im Bereich **Import/Export** stehen jetzt Beispieldateien zur Verfügung.
Sie helfen dir dabei, CSV-, JSON- oder YAML-Dateien korrekt zu strukturieren.
Du findest die Vorlagen direkt auf der Import/Export-Seite:

- `import-template.csv`
- `import-template.json`
- `import-template.yaml`

Lade die gewünschte Datei herunter, trage deine Daten ein und importiere sie anschließend wieder über das Formular.
Beim YAML-Format können Nummern und Preise ohne Anführungszeichen angegeben werden. Eventuell vorhandene Anführungszeichen werden beim Import automatisch entfernt.

### Grid-Vorlagen

Unter **Grid-Vorlagen** verwaltest du Rasterlayouts für den Shortcode `[wp_grid_menu_overlay]`.
Nach der Installation ist bereits eine Beispielvorlage vorhanden, die du bearbeiten kannst.

1. Öffne im Admin-Bereich den Menüpunkt **Grid-Vorlagen**.
2. Mit *Neue Vorlage* legst du ein neues Raster an, über *Bearbeiten* passt du bestehende Vorlagen an.
3. Innerhalb des Editors kannst du Zeilen und Spalten hinzufügen oder entfernen und für jede Zelle die Größe wählen.
4. Speichere das Raster. In der Liste lassen sich Vorlagen duplizieren, löschen oder als Standard markieren.

Auf Beitrags- und Seitenbearbeitungen erscheint die Metabox **Grid-Overlay Inhalt**.
Dort wählst du eine Vorlage aus und fügst die Inhalte für jede Zelle ein.
Für jede Zelle steht ein visueller Editor bereit, mit dem du Texte, Bilder oder Shortcodes komfortabel einfügst.
Der Shortcode gibt anschließend das definierte Raster auf der Seite aus.

Unter **Grid-Inhalte** kannst du außerdem Standardinhalte für jede Zelle einer Vorlage hinterlegen. Diese werden angezeigt, wenn ein Beitrag keine eigenen Inhalte besitzt.

