# All-In-One WordPress Restaurant Plugin

🍽️ Moderne Verwaltung von Speise- und Getränkekarten für WordPress mit REST-API, Dark Mode und umfangreichen Import/Export-Funktionen.

![Version](https://img.shields.io/badge/version-2.6.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)
![License](https://img.shields.io/badge/license-GPL%20v2-green)

## 🌟 Features

### Core-Funktionen
- 🍔 **Eigene Post Types** für Speisen, Getränke und Inhaltsstoffe
- 📝 **Shortcodes** für einfache Frontend-Ausgabe
- 🔌 **REST-API** Endpunkte `\aorp\v1\foods` und `\aorp\v1\drinks`
- ⚙️ **Einstellungsseite** mit übersichtlichen Tabs
- 📊 **CSV-Import/Export** für einfache Datenverwaltung
- 📄 **PDF-Export** für Speisekarten
- 🌙 **Dark Mode** Umschalter mit Cookie-Persistenz
- 🗺️ **Standortkarten** Integration
- 🎯 **Grid-Vorlagen** mit Shortcode-Generator

### Sicherheit & Performance (v2.4.0)
- 🔒 **Nonce-Verifizierung** für alle AJAX-Requests
- 🍪 **Sichere Cookies** mit SameSite-Attribut
- ⚡ **Conditional Loading** - Assets nur bei Bedarf
- 📦 **Transient Caching** für Menü-Items
- ♿ **Accessibility** mit ARIA-Attributen
- 🖼️ **Lazy Loading** für Bilder

## 📦 Installation

### Standard-Installation

1. Plugin in den Ordner `wp-content/plugins` kopieren:
   ```bash
   cd wp-content/plugins
   git clone https://github.com/stb-srv/All-In-One-Wordpress-Restaurant-Plugin.git
   ```

2. Im WordPress-Backend unter "Plugins" aktivieren

3. Administration erfolgt über den neuen Menüpunkt "AIO-Restaurant"

### Anforderungen

- WordPress 5.8 oder höher
- PHP 7.4 oder höher
- MySQL 5.6 oder höher (oder MariaDB)

## 💻 Verwendung

### Shortcodes

#### Speisekarte anzeigen
```
[speisekarte]
```

#### Getränkekarte anzeigen
```
[getraenkekarte]
```

#### Dark Mode Umschalter
```
[restaurant_lightswitcher]
```

### Einstellungen

Die Anzahl der Spalten für Speise- und Getränkekarte lässt sich in den Plugin-Einstellungen festlegen:

1. Navigiere zu **AIO-Restaurant → Einstellungen**
2. Wähle die Anzahl der Spalten (2 oder 3)
3. Speichern

### REST-API

#### Alle Speisen abrufen
```bash
GET /wp-json/aorp/v1/foods
```

#### Alle Getränke abrufen
```bash
GET /wp-json/aorp/v1/drinks
```

## 👨‍💻 Entwickler

### Projektstruktur

```
/
├── admin/              # Admin-Interface-Dateien
├── assets/             # CSS, JS und Bilder
│   ├── css/
│   └── js/
├── includes/           # Haupt-PHP-Klassen
│   ├── class-aorp-*.php
│   ├── class-loader.php
│   └── functions.php
├── languages/          # Übersetzungsdateien
├── docs/               # Dokumentation
└── samples/            # Beispieldateien
```

### Autoloader

Das Plugin verwendet einen einfachen Autoloader für alle Klassen:

```php
namespace AIO_Restaurant_Plugin;

class My_Custom_Class {
    // Wird automatisch geladen aus includes/class-my-custom-class.php
}
```

### Hooks & Filter

#### Actions

```php
// Nach der Plugin-Initialisierung
do_action( 'aorp_init' );

// Vor dem Rendern der Speisekarte
do_action( 'aorp_before_render_foods' );

// Nach dem Rendern der Speisekarte
do_action( 'aorp_after_render_foods' );
```

#### Filter

```php
// Erlaubte iframe-Domains anpassen
add_filter( 'aorp_iframe_whitelist', function( $domains ) {
    $domains[] = 'example.com';
    return $domains;
} );

// Spaltenanzahl überschreiben
add_filter( 'aorp_food_columns', function( $columns ) {
    return 3;
} );

// Cache-Dauer anpassen (in Sekunden)
add_filter( 'aorp_cache_duration', function( $duration ) {
    return 7200; // 2 Stunden
} );
```

### Eigene Erweiterungen

```php
// Eigene Funktion nach Plugin-Initialisierung ausführen
add_action( 'aorp_init', 'my_custom_function' );

function my_custom_function() {
    // Dein Code hier
}
```

## 🧪 Testing

### Manuelle Tests

1. Erstelle Test-Speisen und Getränke
2. Füge Shortcodes in eine Seite ein
3. Teste Dark Mode Umschalter
4. Prüfe CSV-Import/Export
5. Teste REST-API-Endpunkte

### Unit Tests (geplant)

```bash
composer install
vendor/bin/phpunit
```

## 🐛 Bekannte Probleme

- Keine aktuell bekannten Probleme
- Siehe [Issues](https://github.com/stb-srv/All-In-One-Wordpress-Restaurant-Plugin/issues) für offene Tickets

## 📝 Changelog

Vollständiges Changelog siehe [CHANGELOG.md](CHANGELOG.md)

## 🤝 Contributing

Beiträge sind willkommen! Bitte:

1. Fork das Repository
2. Erstelle einen Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit deine Änderungen (`git commit -m 'Add some AmazingFeature'`)
4. Push zum Branch (`git push origin feature/AmazingFeature`)
5. Öffne einen Pull Request

### Code-Standards

- Folge den [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Verwende Type Declarations (PHP 7.4+)
- PHPDoc für alle Klassen und Methoden
- Nonce-Verifizierung für alle AJAX-Requests
- Input Sanitization und Output Escaping

## 📜 Lizenz

Dieses Plugin ist lizenziert unter der GPL v2 oder später.

```
Copyright (C) 2024 stb-srv

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## 👤 Autor

**stb-srv**
- Website: [https://stb-srv.de](https://stb-srv.de)
- GitHub: [@stb-srv](https://github.com/stb-srv)

## 🚀 Support

Bei Fragen oder Problemen:

1. Prüfe die [Dokumentation](docs/)
2. Durchsuche [Issues](https://github.com/stb-srv/All-In-One-Wordpress-Restaurant-Plugin/issues)
3. Erstelle ein neues Issue mit detaillierter Beschreibung

## ⭐ Danksagungen

Danke an alle Contributors und die WordPress-Community!

---

**Made with ❤️ for the Restaurant Industry**
