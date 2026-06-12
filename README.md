# Planer kućanstva

**Planer kućanstva** (Household Planner) je full-stack web aplikacija za
organizaciju zajedničkog kućanstva: praćenje i podjelu troškova, dugova
između cimera/članova obitelji, zadataka kućanstva, popisa za kupnju i
računa. Projekt je izrađen kao studentski projekt iz kolegija razvoja web
aplikacija i demonstrira potpuni full-stack tok: REST API (Laravel +
MySQL) i SPA frontend (Vue 3 + TypeScript) koji komuniciraju putem HTTP
zahtjeva s autentikacijom temeljenom na tokenima.

## Što aplikacija radi

- **Registracija i prijava** korisnika (Laravel Sanctum token autentikacija).
- **Kućanstva** - korisnik stvara ili se priključuje kućanstvu; kućanstvo
  ima članove s ulogama (član / admin kućanstva).
- **Troškovi** - unos zajedničkih troškova (naziv, iznos, kategorija, datum,
  opis), automatska podjela troška na odabrane članove, pretraga i filtri
  (po nazivu, kategoriji, osobi koja je platila, rasponu datuma),
  pregled detalja troška i priloženih računa.
- **Dugovi** - automatski izračun "tko kome duguje" na temelju nepodmirenih
  udjela u troškovima, s mogućnošću označavanja duga kao podmirenog.
- **Zadaci kućanstva** - CRUD zadataka, dodjela članu, rok, status
  (na čekanju / završeno), filtriranje po statusu i osobi.
- **Popis za kupnju** - zajednički popis artikala s količinama i
  oznakom "kupljeno".
- **Računi** - učitavanje slike/PDF računa uz trošak i pregled svih
  učitanih računa kućanstva.
- **Nadzorna ploča** - sažetak: ukupni troškovi ovog mjeseca, koliko
  korisnik duguje / koliko se njemu duguje, broj otvorenih zadataka,
  najnoviji troškovi i zadaci.
- **Administracija** - admin/super admin upravljaju članovima kućanstva;
  super admin upravlja svim korisnicima (promjena uloge, aktivacija /
  deaktivacija računa).

## Korisničke uloge

| Uloga | Ovlasti |
|---|---|
| **user** (korisnik) | Prijava, pregled i upravljanje vlastitim troškovima, pregled troškova/dugova kućanstva, zadaci, popis za kupnju, učitavanje računa. |
| **admin** | Sve što i `user`, plus upravljanje članovima kućanstva (dodavanje/uklanjanje), uređivanje svih troškova i zadataka kućanstva, pristup admin panelu korisnika (pregled). |
| **super_admin** | Sve što i `admin`, plus upravljanje svim korisnicima sustava - promjena uloge i aktivacija/deaktivacija računa. |

## Tehnologije

**Backend**
- PHP 8.2+, Laravel 11
- MySQL (Eloquent ORM, migracije, seederi)
- Laravel Sanctum (token autentikacija)
- REST API, middleware za uloge (`role:`) i aktivne korisnike (`active`)

**Frontend**
- Vue 3 (Composition API) + TypeScript
- Vite
- Vue Router (zaštita ruta po autentikaciji i ulogama)
- Pinia (state management)
- Axios (HTTP klijent s interceptorima za token i 401)
- Tailwind CSS v4 (flat, prilagođena paleta boja)

## Struktura repozitorija

```
planer-kucanstva/
├── backend/   # Laravel API izvorni kod (vidi backend/SETUP.md)
└── frontend/  # Vue 3 + TypeScript SPA
```

---

## Pokretanje backenda (Laravel)

> Ovaj repozitorij sadrži **izvorni kod Laravel aplikacije** (modeli,
> kontroleri, migracije, seederi, rute, middleware), ali ne i sam Laravel
> framework (`vendor/`), jer se on instalira putem Composera. Detaljne
> korake za spajanje ovog koda u svjež Laravel projekt pronađite u
> [`backend/SETUP.md`](backend/SETUP.md).

Sažetak koraka:

```bash
# 1. Stvori svježi Laravel 11 projekt i dodaj Sanctum
composer create-project laravel/laravel backend
cd backend
composer require laravel/sanctum

# 2. Kopiraj datoteke iz ovog backend/ foldera u novostvoreni projekt
#    (modeli, kontroleri, middleware, rute, migracije, seederi, config/cors.php)
#    - detaljna tablica preslikavanja u backend/SETUP.md

# 3. Podesi .env (kopiraj vrijednosti iz backend/.env.example)
php artisan key:generate

# 4. Stvori bazu (MySQL)
#    CREATE DATABASE planer_kucanstva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 5. Pokreni migracije i seedere
php artisan migrate
php artisan db:seed

# 6. Omogući pristup uploadanim računima
php artisan storage:link

# 7. Pokreni API server
php artisan serve
```

API je dostupan na `http://localhost:8000/api`.

### Primjer `.env` (backend)

```env
APP_NAME="Planer kucanstva"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=planer_kucanstva
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
FILESYSTEM_DISK=public

SANCTUM_STATEFUL_DOMAINS=localhost:5173
FRONTEND_URL=http://localhost:5173
```

---

## Pokretanje frontenda (Vue 3 + TypeScript)

```bash
cd frontend
npm install
cp .env.example .env   # po potrebi prilagodi VITE_API_URL
npm run dev
```

Frontend se pokreće na `http://localhost:5173` i komunicira s backendom na
adresi definiranoj u `VITE_API_URL` (zadano `http://localhost:8000/api`).

### Primjer `.env` (frontend)

```env
VITE_API_URL=http://localhost:8000/api
```

### Build za produkciju

```bash
npm run build
```

---

## Testni korisnici

Nakon pokretanja `php artisan db:seed`, dostupni su sljedeći korisnici
(lozinka za sve: `password`):

| E-mail | Lozinka | Uloga |
|---|---|---|
| `superadmin@planer.test` | `password` | super_admin |
| `admin@planer.test` | `password` | admin |
| `ana@planer.test` | `password` | user |
| `marko@planer.test` | `password` | user |

Svi pripadaju demo kućanstvu **"Stan u Zagrebu"** koje sadrži primjer
troškova, zadataka i popisa za kupnju.

---

## API rute (sažetak)

Sve rute su prefiksirane s `/api`. Zaštićene rute zahtijevaju `Authorization: Bearer <token>`
zaglavlje (Sanctum token dobiven prijavom/registracijom).

### Autentikacija (javno)
- `POST /auth/register` - registracija novog korisnika
- `POST /auth/login` - prijava, vraća korisnika i token

### Autentikacija (zaštićeno)
- `POST /auth/logout` - odjava (poništava token)
- `GET /auth/me` - podaci o prijavljenom korisniku

### Nadzorna ploča
- `GET /dashboard` - statistika za prijavljenog korisnika i njegovo kućanstvo

### Kućanstvo
- `GET /household` - kućanstvo prijavljenog korisnika (s članovima)
- `POST /household` - stvaranje novog kućanstva
- `PUT /household/{household}` - uređivanje kućanstva
- `GET /household/{household}/members` - popis članova
- `POST /household/{household}/members` - dodavanje člana po e-mailu *(admin/super_admin)*
- `DELETE /household/{household}/members/{user}` - uklanjanje člana *(admin/super_admin)*

### Troškovi
- `GET /expenses` - popis troškova (paginirano), filtri: `search`, `category`, `paid_by_user_id`, `date_from`, `date_to`
- `POST /expenses` - dodavanje troška (uz mogući upload računa i `split_with[]`)
- `GET /expenses/{expense}` - detalji troška (s udjelima i računima)
- `PUT /expenses/{expense}` / `POST /expenses/{expense}` *(s `_method=PUT` za multipart)* - uređivanje
- `DELETE /expenses/{expense}` - brisanje

### Dugovi
- `GET /debts` - "tko kome duguje" (moji dugovi, dugovi prema meni, saldo po osobi)
- `PATCH /debts/{share}/settle` - oznaka da je udio u trošku podmiren

### Zadaci
- `GET /tasks` - popis zadataka, filtri: `status`, `assigned_to_user_id`
- `POST /tasks` - novi zadatak
- `PUT /tasks/{task}` - uređivanje
- `PATCH /tasks/{task}/status` - promjena statusa (na čekanju/završeno)
- `DELETE /tasks/{task}` - brisanje

### Popis za kupnju
- `GET /shopping-items` - popis artikala, filter `is_purchased`
- `POST /shopping-items` - novi artikl
- `PUT /shopping-items/{shoppingItem}` - uređivanje
- `PATCH /shopping-items/{shoppingItem}/purchased` - oznaka kupljeno/nije kupljeno
- `DELETE /shopping-items/{shoppingItem}` - brisanje

### Računi
- `GET /receipts` - svi učitani računi kućanstva
- `POST /receipts` - upload računa (vezan na trošak)
- `DELETE /receipts/{receipt}` - brisanje računa

### Administracija korisnika
- `GET /users` - popis korisnika, filtri `search`, `role` *(admin/super_admin)*
- `GET /users/{user}` - detalji korisnika *(admin/super_admin)*
- `PUT /users/{user}` - uređivanje profila *(admin/super_admin)*
- `PATCH /users/{user}/role` - promjena uloge *(super_admin)*
- `PATCH /users/{user}/deactivate` - aktivacija/deaktivacija računa *(super_admin)*

---

## Frontend - stranice

| Ruta | Stranica | Pristup |
|---|---|---|
| `/` | Početna (landing) | svi |
| `/login` | Prijava | gost |
| `/register` | Registracija | gost |
| `/dashboard` | Nadzorna ploča | prijavljeni |
| `/household` | Kućanstvo i članovi | prijavljeni |
| `/expenses` | Troškovi (popis, filtri, CRUD) | prijavljeni |
| `/expenses/:id` | Detalji troška | prijavljeni |
| `/debts` | Dugovi | prijavljeni |
| `/tasks` | Zadaci | prijavljeni |
| `/shopping` | Popis za kupnju | prijavljeni |
| `/upload-receipt` | Učitavanje računa | prijavljeni |
| `/admin/users` | Upravljanje korisnicima | admin / super_admin |
| `/403` | Pristup odbijen | - |
| `*` | Stranica nije pronađena (404) | - |

## Dizajn

Frontend koristi pažljivo definiranu, "flat" paletu boja (bez gradijenata,
sjaja ili glassmorphism efekata) definiranu kao CSS varijable u
`frontend/src/style.css`:

- `--color-surface` `#c6ced7` - glavna pozadina
- `--color-secondary` `#989d9e` - rubovi, neaktivni elementi
- `--color-muted` `#696c71` - sekundarni tekst
- `--color-accent` `#714a3a` - gumbi, naglašene akcije
- `--color-panel` `#42454b` - bočna traka, tamne kartice
- `--color-ink` `#2f292b` - glavni tekst

## Status projekta

Projekt je funkcionalno cjelovit: backend (modeli, migracije, seederi,
kontroleri, rute, middleware za uloge) i frontend (sve navedene stranice,
Pinia spremišta, API servisi, komponente) su implementirani prema
specifikaciji. Frontend je uspješno testiran (`npm run build`).

Eventualni sljedeći koraci za produkciju: napisati automatizirane testove
(PHPUnit / Vitest), dodati notifikacije u sučelje (model i ruta su
pripremljeni), te postaviti CI/CD.
