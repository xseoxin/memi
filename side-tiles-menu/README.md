# Side Tiles Menu - WordPress Plugin

Profesjonalna wtyczka WordPress dodająca pionowe menu kafelków przy krawędzi strony z pełną personalizacją, animacjami i dostępnością.

## Funkcje

### Podstawowe
- ✅ Pozycja: lewa lub prawa krawędź strony
- ✅ Ułożenie pionowe: offset od góry/dołu (px/%)
- ✅ Tryb sticky (kafelki podążają za scrollowaniem)
- ✅ Konfigurowalna szerokość, wysokość i odstępy między kafelkami
- ✅ Trzy kształty: kwadrat, zaokrąglony, koło

### Zawartość kafelków
- ✅ Ikony SVG (własny kod SVG)
- ✅ Obrazy (upload przez Media Library)
- ✅ Tekst
- ✅ Linki z możliwością otwarcia w nowej karcie
- ✅ Tooltips i ARIA labels

### Stylizacja
- ✅ Kolory tła i tekstu/ikon
- ✅ Gradienty (linearne z konfiguracją kąta)
- ✅ Obramowania (szerokość i kolor)
- ✅ Cienie (X, Y, blur, kolor z obsługą rgba)
- ✅ Efekty hover (kolor tła, skalowanie)
- ✅ Konfigurowalne z-index

### Animacje
- ✅ Typy: wysuwanie, fade, skalowanie
- ✅ Czas trwania animacji (ms)
- ✅ Opóźnienie dla każdego kafelka
- ✅ Wsparcie dla `prefers-reduced-motion`

### Widoczność
- ✅ Breakpointy dla desktop/tablet/mobile
- ✅ Wykluczenia dla konkretnych stron
- ✅ Wykluczenia dla typów wpisów
- ✅ Warunki: zalogowany/niezalogowany

### Dostępność
- ✅ Focus states
- ✅ Nawigacja klawiaturą (strzałki, Home, End)
- ✅ ARIA labels
- ✅ Wsparcie dla high contrast mode
- ✅ Obsługa `prefers-reduced-motion`

### Wydajność
- ✅ Czysty JavaScript (bez jQuery)
- ✅ Minimalne CSS/JS
- ✅ Lazy loading dla obrazów
- ✅ Throttled resize handlers

### Integracje
- ✅ Blok Gutenberg
- ✅ Shortcode `[side_tiles]` i `[side_tiles id="tile_id"]`
- ✅ Eksport/import ustawień (JSON)
- ✅ Google Analytics 4 (opcjonalnie)
- ✅ Licznik kliknięć

### Personalizacja
- ✅ Pole na własny CSS
- ✅ Podgląd na żywo w panelu administracyjnym
- ✅ Pełna i18n z językiem polskim

### Techniczne
- ✅ PHP 8.0+
- ✅ WordPress 6.0+
- ✅ Struktura OOP
- ✅ WP Settings API
- ✅ Security: nonces, sanitization, escaping
- ✅ Zgodność z WP Coding Standards

## Instalacja

1. Skopiuj folder `side-tiles-menu` do katalogu `/wp-content/plugins/`
2. Aktywuj wtyczkę w panelu WordPress (Wtyczki → Zainstalowane wtyczki)
3. Przejdź do menu "Side Tiles Menu" w panelu administracyjnym

## Użycie

### Panel administracyjny

#### Ustawienia ogólne
1. Przejdź do **Side Tiles Menu → Ustawienia**
2. Skonfiguruj:
   - **Pozycja**: Wybierz lewą lub prawą krawędź
   - **Rozmiar**: Ustaw szerokość, wysokość i odstępy
   - **Kształt**: Kwadrat, zaokrąglony lub koło
   - **Styl**: Kolory, gradienty, cienie, efekty hover
   - **Animacje**: Typ, czas trwania, opóźnienie
   - **Widoczność**: Breakpointy, wykluczenia
   - **Analityka**: Integracja z GA4

#### Zarządzanie kafelkami
1. Przejdź do **Side Tiles Menu → Kafelki**
2. Kliknij **Dodaj nowy kafelek**
3. Wypełnij dane:
   - Tytuł (tooltip)
   - Typ treści: SVG / obraz / tekst
   - Link URL
   - Cel linku (_self / _blank)
   - Kolejność wyświetlania
   - ARIA label
4. Zapisz kafelek

#### Statystyki
1. Przejdź do **Side Tiles Menu → Statystyki**
2. Zobacz liczby kliknięć dla każdego kafelka

### Gutenberg

Dodaj blok **Side Tiles Menu** do dowolnego wpisu/strony:
- Wybierz "Wszystkie kafelki" lub pojedynczy kafelek
- Blok automatycznie wyrenderuje kafelki zgodnie z ustawieniami

### Shortcode

Użyj shortcode w treści wpisu/strony:

```php
// Wszystkie kafelki
[side_tiles]

// Pojedynczy kafelek
[side_tiles id="tile_123456"]
```

### Kod PHP

W szablonach motywu:

```php
<?php
// Wszystkie kafelki
echo do_shortcode('[side_tiles]');

// Pojedynczy kafelek
echo do_shortcode('[side_tiles id="tile_123456"]');

// Bezpośrednie wywołanie
if (function_exists('side_tiles_menu')) {
    echo side_tiles_menu()->frontend->render();
}
?>
```

## Eksport/Import

### Eksport ustawień
1. Przejdź do **Side Tiles Menu → Ustawienia**
2. Przewiń do sekcji "Eksport/Import Ustawień"
3. Kliknij **Eksportuj ustawienia**
4. Pobierz plik JSON

### Import ustawień
1. Przejdź do **Side Tiles Menu → Ustawienia**
2. Przewiń do sekcji "Eksport/Import Ustawień"
3. Kliknij **Importuj ustawienia**
4. Wybierz plik JSON
5. Potwierdź import

## Google Analytics 4

Aby włączyć tracking w GA4:
1. Przejdź do zakładki **Analityka**
2. Zaznacz "Włącz wysyłanie zdarzeń do GA4"
3. Wpisz swoje Measurement ID (np. G-XXXXXXXXXX)
4. Zapisz ustawienia

Wtyczka będzie wysyłać zdarzenie `side_tile_click` z następującymi parametrami:
- `tile_id` - ID kafelka
- `tile_title` - Tytuł kafelka
- `link_url` - URL docelowy
- `event_category` - "Side Tiles Menu"
- `event_label` - Tytuł kafelka

## Własny CSS

W zakładce **Personalizacja** możesz dodać własny CSS:

```css
/* Przykład: zmiana koloru przy hover dla konkretnego kafelka */
.side-tile[data-tile-id="tile_123456"]:hover {
    background-color: #ff0000 !important;
}

/* Przykład: pulsująca animacja */
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.side-tile {
    animation: pulse 2s infinite;
}
```

## Struktura plików

```
side-tiles-menu/
├── admin/
│   ├── css/
│   │   ├── admin.css
│   │   └── block-editor.css
│   └── js/
│       ├── admin.js
│       └── block.js
├── includes/
│   ├── class-settings.php
│   ├── class-frontend.php
│   ├── class-gutenberg-block.php
│   └── class-analytics.php
├── languages/
│   ├── side-tiles-menu.pot
│   └── side-tiles-menu-pl_PL.po
├── public/
│   ├── css/
│   │   └── frontend.css
│   └── js/
│       └── frontend.js
├── side-tiles-menu.php
└── README.md
```

## Wymagania

- PHP 8.0 lub nowszy
- WordPress 6.0 lub nowszy
- Nowoczesna przeglądarka z obsługą ES6+

## Kompatybilność

Wtyczka jest kompatybilna z:
- Wszystkimi standardowymi motywami WordPress
- Page builderami (Elementor, Beaver Builder, etc.)
- Wtyczkami cache (WP Rocket, W3 Total Cache, etc.)
- WPML i Polylang (wielojęzyczność)

## Bezpieczeństwo

Wtyczka implementuje najlepsze praktyki bezpieczeństwa:
- ✅ Weryfikacja nonces dla wszystkich akcji AJAX
- ✅ Sanitization wszystkich danych wejściowych
- ✅ Escaping wszystkich danych wyjściowych
- ✅ Sprawdzanie uprawnień użytkownika
- ✅ Ochrona przed XSS, CSRF, SQL Injection

## Wsparcie

W przypadku problemów lub pytań:
- Sprawdź konsolę przeglądarki pod kątem błędów JavaScript
- Sprawdź logi błędów WordPress
- Wyłącz inne wtyczki, aby wykluczyć konflikty
- Sprawdź czy motyw nie nadpisuje stylów wtyczki

## Licencja

GPL v2 or later

## Changelog

### 1.0.0 (2025-01-21)
- Pierwsza wersja publiczna
- Wszystkie funkcje podstawowe zaimplementowane

## Autor

Created with Claude Code
