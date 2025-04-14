# Plán optimalizácie projektu

Tento dokument obsahuje plán optimalizácie projektu, ktorý bude implementovaný v budúcnosti.

## 1. Optimalizácia frontendu (Vite + Tailwind)

### Konfigurácia Vite

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: '../public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'js/main.js'),
        admin: resolve(__dirname, 'js/admin.js')
      },
      output: {
        assetFileNames: 'assets/[name]-[hash][extname]',
        entryFileNames: 'js/[name]-[hash].js'
      }
    }
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, './')
    }
  }
});
```

### Výhody
- Rýchlejší build proces
- Hot Module Replacement (HMR) pre rýchlejší vývoj
- Lepšia organizácia JavaScript a CSS súborov
- Optimalizácia veľkosti výsledných súborov

## 2. Optimalizácia backendu

### Konfigurácia závislostí

```php
// config/dependencies.php
return [
    // Session
    Session::class => DI\autowire()
        ->constructorParameter('settings', [
            'name' => 'app_session',
            'autorefresh' => true,
            'lifetime' => '24 hours'
        ]),

    // DB Repository
    UserRepositoryInterface::class => DI\autowire(DatabaseUserRepository::class)
        ->constructorParameter('table', 'users'),

    // Cache
    CacheInterface::class => DI\factory(function () {
        return new FilesystemAdapter('app', 3600, __DIR__.'/../var/cache');
    })
];
```

### Výhody
- Lepšia správa sessions
- Jednoduchšia konfigurácia repozitárov
- Implementácia cache pre zrýchlenie aplikácie

## 3. Optimalizácia Twig šablón

### Základná šablóna

```twig
{# layout.twig #}
<!DOCTYPE html>
<html lang="en">
<head>
    {% block head %}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}{% endblock %}</title>
    {{ vite_asset('css/app.css') }}
    {% endblock %}
</head>
<body class="bg-gray-50">
    {% block content %}{% endblock %}
    {{ vite_asset('js/main.js') }}
</body>
</html>
```

### Výhody
- Jednoduchšie šablóny
- Lepšia integrácia s Vite
- Konzistentný vzhľad

## 4. Dôležité kroky na budúci týždeň

### Dokončiť integráciu Vite
- Skontrolovať build proces
- Nastaviť HMR pre vývoj

### Optimalizovať databázové dotazy
- Pridať caching
- Vytvoriť indexy

### Refaktorovať middleware
- Zjednodušiť error handling
- Pridať request logging

### Dokončiť testy
- Pridať integration testy
- Vytvoriť testovaciu DB

## Prioritizácia úloh

1. **Vysoká priorita**
   - Dokončenie integrácie Vite
   - Optimalizácia databázových dotazov

2. **Stredná priorita**
   - Refaktorovanie middleware
   - Optimalizácia Twig šablón

3. **Nižšia priorita**
   - Dokončenie testov
   - Ďalšie optimalizácie frontendu

## Časový plán

- **Týždeň 1**: Integrácia Vite a optimalizácia frontendu
- **Týždeň 2**: Optimalizácia databázových dotazov a implementácia cache
- **Týždeň 3**: Refaktorovanie middleware a Twig šablón
- **Týždeň 4**: Dokončenie testov a finálne úpravy

## Záver

Tieto optimalizácie výrazne zlepšia výkon a udržateľnosť projektu. Implementácia bude prebiehať postupne, aby sa minimalizoval vplyv na existujúcu funkcionalitu.
