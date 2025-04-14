# Optimalizácia frontendu

Táto dokumentácia popisuje optimalizáciu frontendu v aplikácii, ktorá využíva lazy loading komponentov a ďalšie techniky pre zlepšenie výkonu.

## Lazy loading komponentov

Aplikácia používa lazy loading pre načítanie JavaScript komponentov až keď sú potrebné. Toto zlepšuje počiatočný čas načítania stránky a znižuje množstvo JavaScript kódu, ktorý musí byť spracovaný pri prvom načítaní.

```javascript
// resources/js/main.js
const loadComponent = async (name) => {
  return import(`./components/${name}.js`)
    .then(module => module.default)
    .catch(() => {
      console.warn(`Failed to load component: ${name}`);
      return null;
    });
};

// Použitie
document.addEventListener('DOMContentLoaded', async () => {
  const darkMode = await loadComponent('darkMode');
  if (darkMode) darkMode.init();
  
  const animations = await loadComponent('animations');
  if (animations) animations.init();
});
```

## Štruktúra komponentov

Každý komponent je implementovaný ako samostatný modul s vlastnou zodpovednosťou:

### Dark Mode komponent

```javascript
// resources/js/components/darkMode.js
const darkMode = {
    init() {
        // Inicializácia dark mode
        // Kontrola uložených preferencií
        // Nastavenie počiatočného stavu
        // Pridanie event listenerov
    },
    
    updateToggleState(isDark) {
        // Aktualizácia stavu prepínača
    }
};

export default darkMode;
```

### Animations komponent

```javascript
// resources/js/components/animations.js
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const animations = {
    init() {
        this.initHeaderAnimation();
        this.initScrollAnimations();
    },
    
    initHeaderAnimation() {
        // Animácia pre header
    },
    
    initScrollAnimations() {
        // Animácie aktivované pri scrollovaní
    }
};

export default animations;
```

## Konfigurácia Vite.js

Aplikácia používa Vite.js pre build a optimalizáciu frontend assets. Konfigurácia je v súbore `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'resources/js/main.js'),
      },
      output: {
        manualChunks: {
          gsap: ['gsap'],
          vendor: ['gsap/ScrollTrigger']
        }
      }
    },
    minify: true,
    sourcemap: false
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources')
    }
  },
  server: {
    strictPort: true,
    port: 5173
  }
});
```

Táto konfigurácia zabezpečuje:

1. **Code splitting** - Rozdelenie kódu do menších častí
2. **Vendor chunking** - Oddelenie knižníc tretích strán do samostatných súborov
3. **Minifikácia** - Zmenšenie veľkosti súborov
4. **Manifest** - Generovanie manifest súboru pre správne načítanie assets

## Výhody optimalizácie

1. **Rýchlejšie načítanie stránky**
   - Menšia počiatočná veľkosť JavaScript súborov
   - Paralelné načítanie menších súborov

2. **Lepšia výkonnosť**
   - Komponenty sa načítavajú až keď sú potrebné
   - Menšie zaťaženie prehliadača pri prvom načítaní

3. **Lepšia organizácia kódu**
   - Kód je rozdelený do menších, znovupoužiteľných komponentov
   - Každý komponent má svoju vlastnú zodpovednosť

4. **Jednoduchšia údržba**
   - Izolované komponenty je jednoduchšie testovať a aktualizovať
   - Jasná separácia zodpovedností

## Použitie v šablónach

V Twig šablónach sa JavaScript súbory načítavajú pomocou ViteAssetHelper:

```twig
{# Vite JS #}
{{ vite.jsTag('js/main.js')|raw }}
```

Tento helper automaticky načíta správne súbory podľa manifest súboru generovaného Vite.js.
