# API Referencia

Táto dokumentácia popisuje verejné API rozhranie aplikácie MarkCMS, ktoré môžu využívať vývojári tretích strán na integráciu s našou platformou.

## Obsah

1. [Úvod](#úvod)
2. [Autentifikácia](#autentifikácia)
3. [Články](#články)
4. [Používatelia](#používatelia)
5. [Kategórie a tagy](#kategórie-a-tagy)
6. [Chybové kódy](#chybové-kódy)
7. [Limity a obmedzenia](#limity-a-obmedzenia)

## Úvod

MarkCMS poskytuje REST API, ktoré umožňuje vývojárom tretích strán pristupovať k obsahu a funkciám platformy. API používa štandardné HTTP metódy a vracia odpovede vo formáte JSON.

### Základné informácie

- **Základná URL**: `https://api.markcms.com/v1`
- **Formát odpovede**: JSON
- **Kódovanie**: UTF-8

### Verzie API

Aktuálna verzia API je `v1`. Verzia je súčasťou URL cesty.

## Autentifikácia

API používa autentifikáciu pomocou API kľúča. API kľúč musí byť odoslaný v hlavičke `X-API-Key` pri každom požiadavku.

### Získanie API kľúča

1. Prihláste sa do svojho účtu na MarkCMS
2. Prejdite do sekcie "Nastavenia" > "API"
3. Kliknite na tlačidlo "Vygenerovať API kľúč"
4. Skopírujte vygenerovaný kľúč

### Príklad požiadavku s autentifikáciou

```bash
curl -X GET "https://api.markcms.com/v1/articles" \
     -H "X-API-Key: váš_api_kľúč"
```

## Články

### Získanie zoznamu článkov

```
GET /articles
```

#### Parametre

| Parameter | Typ | Popis |
|-----------|-----|-------|
| `page` | integer | Číslo stránky (predvolene 1) |
| `limit` | integer | Počet článkov na stránku (predvolene 10, max 100) |
| `sort` | string | Pole, podľa ktorého sa má zoradiť (`created_at`, `updated_at`, `title`) |
| `order` | string | Smer zoradenia (`asc`, `desc`) |
| `type` | string | Typ článku (`article`, `blog`, `news`) |
| `category` | string | ID kategórie |
| `tag` | string | Tag |

#### Odpoveď

```json
{
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "title": "Názov článku",
      "slug": "nazov-clanku",
      "content": "Obsah článku...",
      "type": "article",
      "author_id": "7c0d7bb0-5b1b-4b9d-8c1a-9c2a3e4b5c6d",
      "categories": ["tech", "programming"],
      "tags": ["php", "slim", "api"],
      "created_at": "2023-01-01T12:00:00Z",
      "updated_at": "2023-01-02T14:30:00Z"
    },
    // ...
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 42,
    "total_pages": 5
  }
}
```

### Získanie konkrétneho článku

```
GET /articles/{id}
```

alebo

```
GET /articles/by-slug/{slug}
```

#### Odpoveď

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Názov článku",
    "slug": "nazov-clanku",
    "content": "Obsah článku...",
    "type": "article",
    "author_id": "7c0d7bb0-5b1b-4b9d-8c1a-9c2a3e4b5c6d",
    "categories": ["tech", "programming"],
    "tags": ["php", "slim", "api"],
    "created_at": "2023-01-01T12:00:00Z",
    "updated_at": "2023-01-02T14:30:00Z"
  }
}
```

### Vytvorenie nového článku

```
POST /articles
```

#### Telo požiadavku

```json
{
  "title": "Názov nového článku",
  "content": "Obsah nového článku...",
  "type": "article",
  "categories": ["tech", "programming"],
  "tags": ["php", "slim", "api"]
}
```

#### Odpoveď

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Názov nového článku",
    "slug": "nazov-noveho-clanku",
    "content": "Obsah nového článku...",
    "type": "article",
    "author_id": "7c0d7bb0-5b1b-4b9d-8c1a-9c2a3e4b5c6d",
    "categories": ["tech", "programming"],
    "tags": ["php", "slim", "api"],
    "created_at": "2023-01-01T12:00:00Z",
    "updated_at": "2023-01-01T12:00:00Z"
  }
}
```

### Aktualizácia článku

```
PUT /articles/{id}
```

#### Telo požiadavku

```json
{
  "title": "Aktualizovaný názov článku",
  "content": "Aktualizovaný obsah článku...",
  "categories": ["tech", "programming", "web"],
  "tags": ["php", "slim", "api", "rest"]
}
```

#### Odpoveď

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Aktualizovaný názov článku",
    "slug": "aktualizovany-nazov-clanku",
    "content": "Aktualizovaný obsah článku...",
    "type": "article",
    "author_id": "7c0d7bb0-5b1b-4b9d-8c1a-9c2a3e4b5c6d",
    "categories": ["tech", "programming", "web"],
    "tags": ["php", "slim", "api", "rest"],
    "created_at": "2023-01-01T12:00:00Z",
    "updated_at": "2023-01-02T14:30:00Z"
  }
}
```

### Odstránenie článku

```
DELETE /articles/{id}
```

#### Odpoveď

```json
{
  "success": true,
  "message": "Článok bol úspešne odstránený"
}
```

## Používatelia

### Získanie zoznamu používateľov

```
GET /users
```

#### Parametre

| Parameter | Typ | Popis |
|-----------|-----|-------|
| `page` | integer | Číslo stránky (predvolene 1) |
| `limit` | integer | Počet používateľov na stránku (predvolene 10, max 100) |
| `sort` | string | Pole, podľa ktorého sa má zoradiť (`created_at`, `name`) |
| `order` | string | Smer zoradenia (`asc`, `desc`) |
| `role` | string | Rola používateľa (`user`, `admin`) |

#### Odpoveď

```json
{
  "data": [
    {
      "id": "7c0d7bb0-5b1b-4b9d-8c1a-9c2a3e4b5c6d",
      "name": "Ján Novák",
      "email": "jan.novak@example.com",
      "role": "user",
      "created_at": "2023-01-01T12:00:00Z",
      "updated_at": "2023-01-02T14:30:00Z"
    },
    // ...
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 42,
    "total_pages": 5
  }
}
```

### Získanie konkrétneho používateľa

```
GET /users/{id}
```

#### Odpoveď

```json
{
  "data": {
    "id": "7c0d7bb0-5b1b-4b9d-8c1a-9c2a3e4b5c6d",
    "name": "Ján Novák",
    "email": "jan.novak@example.com",
    "role": "user",
    "created_at": "2023-01-01T12:00:00Z",
    "updated_at": "2023-01-02T14:30:00Z"
  }
}
```

## Kategórie a tagy

### Získanie zoznamu kategórií

```
GET /categories
```

#### Odpoveď

```json
{
  "data": [
    {
      "id": "tech",
      "name": "Technológie",
      "description": "Články o technológiách"
    },
    {
      "id": "programming",
      "name": "Programovanie",
      "description": "Články o programovaní"
    },
    // ...
  ]
}
```

### Získanie zoznamu tagov

```
GET /tags
```

#### Odpoveď

```json
{
  "data": [
    "php",
    "slim",
    "api",
    "rest",
    // ...
  ]
}
```

## Chybové kódy

API vracia štandardné HTTP stavové kódy:

| Kód | Popis |
|-----|-------|
| 200 | OK - Požiadavka bola úspešne spracovaná |
| 201 | Created - Zdroj bol úspešne vytvorený |
| 400 | Bad Request - Neplatný požiadavok |
| 401 | Unauthorized - Chýba alebo je neplatný API kľúč |
| 403 | Forbidden - Nemáte oprávnenie na prístup k tomuto zdroju |
| 404 | Not Found - Zdroj nebol nájdený |
| 422 | Unprocessable Entity - Validačná chyba |
| 429 | Too Many Requests - Prekročený limit požiadaviek |
| 500 | Internal Server Error - Chyba servera |

### Príklad chybovej odpovede

```json
{
  "error": {
    "code": 400,
    "message": "Neplatný požiadavok",
    "details": [
      "Pole 'title' je povinné",
      "Pole 'content' je povinné"
    ]
  }
}
```

## Limity a obmedzenia

- **Rate limiting**: 1000 požiadaviek za hodinu na API kľúč
- **Veľkosť tela požiadavku**: Maximálne 10 MB
- **Dĺžka obsahu článku**: Maximálne 100 000 znakov
- **Počet tagov na článok**: Maximálne 10
- **Počet kategórií na článok**: Maximálne 5

### Hlavičky rate limitingu

API vracia nasledujúce hlavičky pre informácie o rate limitingu:

- `X-RateLimit-Limit`: Celkový počet povolených požiadaviek za hodinu
- `X-RateLimit-Remaining`: Zostávajúci počet požiadaviek
- `X-RateLimit-Reset`: Čas (Unix timestamp), kedy sa limit obnoví
