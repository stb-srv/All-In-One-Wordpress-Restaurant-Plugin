# Changelog

Alle wichtigen Änderungen an diesem Plugin werden in dieser Datei dokumentiert.

## [2.4.0] - 2024-12-12

### 🔒 Security
- Nonce-Verifizierung für Dark Mode Toggle implementiert
- SameSite-Cookie-Attribut für erhöhte Sicherheit hinzugefügt
- Secure-Flag für Cookies bei HTTPS-Verbindungen
- Verbesserte iframe-Sanitization mit Domain-Whitelist
- Durchgehende Input-Validierung und Escaping optimiert

### ⚡ Performance
- Bedingte Asset-Einbindung nur bei Verwendung von Shortcodes
- Admin-Assets werden nur auf relevanten Seiten geladen
- `wp_enqueue_media()` nur noch bei Bedarf
- Transient-Caching für Menü-Items (1 Stunde)
- Optimierte Datenbankabfragen mit besseren Parametern
- Versionierte Assets für effektives Cache-Busting

### 🐛 Bugfixes
- Fehlerbehandlung bei Plugin-Initialisierung verbessert
- Leere Kategorien werden korrekt behandelt
- Fehlerhafte Getränkegrößen-Zeilen werden ignoriert

### 🎉 Features
- Plugin-Konstanten für Version, Pfade und URLs
- Activation/Deactivation Hooks implementiert
- Automatische Standardeinstellungen bei Aktivierung
- Textdomain-Loading für Internationalisierung
- ARIA-Attribute für bessere Accessibility
- Semantic HTML5-Elemente (figure, role-Attribute)
- Lazy Loading für Bilder

### 💻 Code-Qualität
- Type Declarations für alle Funktionsparameter und Rückgabewerte
- Umfassende PHPDoc-Dokumentation
- Try-catch-Blöcke für robustere Fehlerbehandlung
- Verbesserte Code-Struktur und Lesbarkeit
- WordPress Coding Standards durchgehend eingehalten

### 📖 Dokumentation
- CHANGELOG.md hinzugefügt
- README.md erweitert mit Entwickler-Infos
- Inline-Dokumentation verbessert

## [2.3.0] - 2024-XX-XX

### Added
- Verwaltung von Kontaktnachrichten mit Löschfunktion

## [2.2.0] - 2024-XX-XX

### Added
- Grid-Inhalte lassen sich nun über einen Tab der Vorlagen-Seite bearbeiten

## [2.1.0] - 2024-XX-XX

### Added
- Grid-Vorlagen listen nun den zugehörigen Shortcode auf

## [2.0.0] - 2024-XX-XX

### Added
- Eigene Post Types für Speisen, Getränke und Inhaltsstoffe
- Shortcodes für die Ausgabe im Frontend
- REST-API Endpunkte `/aorp/v1/foods` und `/aorp/v1/drinks`
- Einstellungsseite mit übersichtlichen Untertabs
- CSV-Import sowie CSV- und PDF-Export
- Darkmode Umschalter
- Standortkarten über die Seite "AIO-Karten"
- Grid-Vorlagen mit Shortcode-Ausgabe

---

## Versionierungs-Schema

Wir verwenden [Semantic Versioning](https://semver.org/lang/de/):

- **MAJOR**: Inkompatible API-Änderungen
- **MINOR**: Neue Funktionen (abwärtskompatibel)
- **PATCH**: Bugfixes (abwärtskompatibel)
