# Architecture du Projet

## Informations Generales

| Element | Details |
|---------|---------|
| **Framework** | Laravel 10.x (PHP ^8.1) |
| **Frontend** | Vite 5.x + Bootstrap 5.2 + Sass |
| **Base de donnees** | MySQL (via Laravel Eloquent ORM) |
| **Paiement** | Chargily Pay (^2.0) + SATIM (Guiddini) |
| **QR Code** | simplesoftwareio/simple-qrcode |
| **Auth API** | Laravel Sanctum 3.x |
| **Branches** | `yasser` (principale), remote `origin/yasser` |
| **Autoload custom** | `app/helpers.php` (fonctions globales) |

---

## Structure des Dossiers

```
G:\opowmila43\
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Controleurs panneau admin
│   │   │   ├── Api/                # Controleurs API
│   │   │   │   ├── PersonneController.php
│   │   │   │   └── RemotePersonController.php
│   │   │   ├── Auth/               # Controleurs d'authentification
│   │   │   │   ├── AdminAuthController.php
│   │   │   │   ├── ClubAuthController.php
│   │   │   │   ├── CompanyAuthController.php
│   │   │   │   ├── PersonAuthController.php
│   │   │   │   ├── ForgotPasswordController.php
│   │   │   │   └── ResetPasswordController.php
│   │   │   ├── ActivityController.php
│   │   │   ├── AdminController.php
│   │   │   ├── AuthController.php
│   │   │   ├── ChargilyPayController.php
│   │   │   ├── ClubController.php
│   │   │   ├── ClubDossierController.php
│   │   │   ├── ComplexeController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── DossierController.php
│   │   │   ├── EntrepriseDossierController.php
│   │   │   ├── EventController.php
│   │   │   ├── HomeController.php
│   │   │   ├── NewsController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── PaymentController_satim.php
│   │   │   ├── PersonAssuranceController.php
│   │   │   ├── PersonController.php
│   │   │   ├── PoolClosureController.php
│   │   │   ├── PricingPlanController.php
│   │   │   ├── ProfileController.php
│   │   │   └── ReservationController.php
│   │   ├── Kernel.php
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php
│   │       ├── ClubMiddleware.php
│   │       ├── CompanyMiddleware.php
│   │       ├── PersonMiddleware.php
│   │       ├── CheckAdminAccess.php
│   │       ├── VerifyComplexSignature.php
│   │       └── ... (standards Laravel)
│   ├── Mail/
│   │   └── PoolClosureMail.php
│   ├── Models/                     # 27 modeles Eloquent
│   ├── Providers/
│   └── helpers.php                 # Fonctions utilitaires globales
├── config/                         # 16 fichiers de config (dont appx.php custom)
├── database/
│   ├── factories/
│   ├── migrations/                 # 3 migrations supplementaires
│   └── seeders/
├── resources/
│   ├── css/
│   ├── js/
│   ├── sass/
│   └── views/                      # Vues Blade
├── routes/
│   ├── web.php                     # 828 lignes - routes principales
│   ├── api.php
│   ├── channels.php
│   └── console.php
└── public/
```

---

## Modeles Eloquent (27)

| Modele | Description |
|--------|-------------|
| `User` | Utilisateur generique (auth Sanctum) |
| `Person` | Personne (adherent, enfant, etc.) |
| `Club` | Club sportif |
| `Complex` | Complexe sportif |
| `ComplexActivity` | Pivot : activites d'un complexe |
| `ComplexSeat` | Places/seat d'un complexe |
| `SeatType` | Type de place |
| `Activity` | Activite sportive |
| `ActivityCategory` | Categorie d'activite |
| `AgeCategory` | Tranche d'age |
| `Season` | Saison sportive |
| `Schedule` | Horaires |
| `PricingPlan` | Plan tarifaire |
| `Reservation` | Reservation |
| `ReservationCredit` | Credit lie a une reservation |
| `Payment` | Paiement |
| `ChargilyPayment` | Paiement via Chargily Pay |
| `Dossier` | Dossier d'inscription |
| `Document` | Document attache au dossier |
| `PersonAssurance` | Assurance d'une personne |
| `PoolClosure` | Fermeture de piscine/complex |
| `News` | Actualites |
| `Event` | Evenements |
| `Team` | Equipe sportive |
| `MatchModel` | Match/rencontre |
| `Ticket` | Billet (evenements/matchs) |
| `Device` | Appareil (lecteur presence type ZKTeco) |

---

## Utilisateurs et Roles

Le projet utilise un systeme de **6 roles**. Un seul middleware (admin) couvre les 3 roles d'administration (admin / verificateur / financier) :

| Role | Login | Middleware | Description |
|------|-------|-----------|-------------|
| **Admin** | `/admin/login` | `admin` | Gestion totale du systeme + gestion des comptes admin/financier/verificateur |
| **Financier** | `/admin/login` | `admin` | Etat financier : voit et valide/annule les paiements des reservations. Sans complexe affecte → tous les complexes ; avec complexe affecte → son complexe seul (tuiles + reservations) |
| **Verificateur** | `/admin/login` | `admin` | Lecture des reservations (toutes ou son complexe seul selon `complex_id`), impression |
| **Club** | `/club/login` | `club` | Club sportif - gere ses personnes/dossiers |
| **Entreprise** | `/entreprise/login` | `entreprise` | Entreprise - gere ses personnes/dossiers |
| **Personne** | `/person/login` | `person` | Individu - gere son profil/enfants/reservations |

**Les roles admin, verificateur et financier n'ont pas d'inscription publique** : ils sont crees par un admin via le CRUD `/admins/*` (select "type" : مسؤول عام / مالي / متحقق). Les roles club, entreprise et personne ont des pages d'inscription publiques.

### Financier & Verificateur

Comportement apres connexion via `/admin/login` :
- Le **financier** et le **verificateur** sont rediriges directement vers `/reservations` (la liste des reservations = leur tableau de bord).
- La page affiche des **tuiles financieres** (total paye, reservations payees, non payees, total reservations) pour l'admin et le financier.
- Portee selon `complex_id` :
  - **Sans complexe affecte** : toutes les reservations de tous les complexes + tuiles globales.
  - **Avec complexe affecte** : uniquement les reservations de ce complexe + tuiles du complexe (`ReservationController::index` filtre via `complexActivity.complex_id`).
- Le **financier** peut confirmer/annuler les paiements (bouton `togglePayment`). Un financier lie a un complexe ne peut traiter que les reservations de son complexe (verifie dans le controller).
- Le **verificateur** voit les reservations en lecture seule (pas de boutons).
- La gestion des comptes (`/admins/*`) reste reservee au role `admin`.

---

## Routes Principales

### Pages Publiques (sans auth)
- `/` - Page d'accueil (welcome)
- `/home` - Accueil connecte
- `/complexes/filter/{type}` - Filtrage AJAX des complexes
- `/matches/public` - Matchs publics
- `/events/{id}` - Detail evenement
- `/news/{id}` - Detail actualite
- `/legal/terms`, `/legal/privacy` - Pages legales
- `/tickets/reserve/{id}` - Reservation de billets

### Authentification
- `/admin/login`, `/club/login`, `/entreprise/login`, `/person/login` (GET+POST)
- `/club/register`, `/entreprise/register`, `/person/register` (GET+POST)
- `/password/reset`, `/password/email`, `/password/reset/{token}` (forgot/reset)
- `*/logout` (POST, pour chaque role)

### Espace Personne (auth + person)
- `/person/dashboard` - Tableau de bord
- `/person/children/*` - Gestion des enfants
- `/profile/step/{step}` - Inscription par etapes (wizard)
- `/reservations/*` - CRUD reservations (select-type, list, form, store)
- `/dossier/*` - Impression/formulaire dossier
- `/forms/formulaire/{id}` - Telechargement formulaire

### Espace Club (auth + club)
- `/club/dashboard` - Tableau de bord
- `/club/persons/*` - CRUD personnes du club
- `/club/dossier/*` - Gestion dossier club
- `/club/profile/*` - Profil club

### Espace Entreprise (auth + entreprise)
- `/entreprise/dashboard` - Tableau de bord
- `/entreprise/persons/*` - CRUD personnes de l'entreprise
- `/entreprise/dossier/*` - Gestion dossier entreprise
- `/entreprise/profile/*` - Profil entreprise

### Espace Admin (auth + admin) - 50+ routes
- `/admin/dashboard` - Tableau de bord principal
- `/admin/complexes/*` - CRUD complexes
- `/admin/activities/*` - CRUD activites
- `/admin/schedules/*` - CRUD horaires
- `/admin/capacities/*` - CRUD capacites
- `/admin/pricing/*` - CRUD plans tarifaires
- `/admin/seasons` - CRUD saisons
- `/admin/age-categories` - CRUD categories d'age
- `/admin/activity-categories` - CRUD categories d'activite
- `/admin/persons` - CRUD personnes (global)
- `/admin/dossiers/*` - Validation/rejet des dossiers
- `/admin/clubs/*` - Validation/rejet des clubs
- `/admin/companies/*` - Validation/rejet des entreprises
- `/admin/assurances/*` - Gestion assurances
- `/admin/pool-closures/*` - Fermetures de piscines
- `/admin/reservations` - Liste reservations
- `/admin/news`, `/admin/events` - CRUD news/evenements
- `/admin/matches`, `/admin/teams`, `/admin/tickets` - Sport/matches
- `/admin/seat_types`, `/admin/complex_seats` - Gestion places
- `/admin/devices` - Appareils (ZKTeco)
- `/admin/accounts-without-dossiers` - Nettoyage comptes orphelins
- `/admins/*` - Gestion des comptes admin
- `/admin/dashboard_complex/{id}` - Dashboard par complexe

### Paiement
- `/payment/initiate` (POST) - Initier paiement
- `/payment/callback` (POST) - CallbackGuiddini/SATIM
- `/payment/return` (GET) - Retour redirect
- `/payment/receipt/{orderId}` - Recu telechargeable
- `/chargilypay/redirect` - Redirect Chargily Pay
- `/chargilypay/webhook` - Webhook Chargily

### API
- `/api/complex/{complex}/persons` - Personnes d'un complexe (signature verifiee)

---

## Controleurs Admin (Admin/)

| Controleur | Fonction |
|------------|----------|
| `ComplexController` | CRUD complexes |
| `ActivitysController` | CRUD activites |
| `PricingsPlanController` | CRUD plans tarifaires |
| `CapacityController` | CRUD capacites |
| `ScheduleController` | CRUD horaires + creneaux occupes |
| `AgeCategoryController` | CRUD categories d'age |
| `SeasonController` | CRUD saisons |
| `PersonsController` | CRUD personnes (admin global) |
| `ActivityCategoryController` | Categories d'activite |
| `SeatTypeController` | Types de places |
| `ComplexSeatController` | Places de complexes |
| `TeamController` | Equipes sportives |
| `MatchController` | Matchs/rencontres |
| `TicketController` | Billets |
| `DeviceController` | Appareils (ZKTeco, import utilisateurs) |
| `PersonAssuranceController` | Assurances (bulk, print, selection) |

---

## Vues Blade

### Structure
- `layouts/app.blade.php` - Layout principal (unique)
- `partials/` - Composants reutilisables

### Espace Admin (`resources/views/admin/`)
32 sous-dossiers couvrant toutes les entites : accounts, activities, activity_categories, admins, age_categories, assurances, capacities, clubs, companies, complex_seats, complexes, devices, dossiers, events, matches, news, persons, pool_closures, pricing_plans, profile, reservations, schedules, seasons, seat_types, teams, tickets.

### Espace Club (`resources/views/club/`)
- `dashboard.blade.php` - Tableau de bord
- `dossier/` - Gestion dossier (index, edit)
- `persons/` - Gestion personnes (create, edit)

### Espace Entreprise (`resources/views/entreprise/`)
- `dashboard.blade.php` - Tableau de bord

### Espace Personne (`resources/views/person/`)
- `dashboard.blade.php`

### Autres
- `home.blade.php`, `welcome.blade.php` - Accueil public
- `profile.blade.php`, `profile/` - Profil utilisateur
- `reservation/` - Reservations
- `payments/` - Resultats paiement
- `emails/` - Templates email (pool-closure)
- `legal/` - Conditions d'utilisation
- `auth/` - Pages d'authentification
- `complexes/` - Vues complexes
- `activities/` - Vues activites
- `dossier/` - Dossier d'inscription
- `pages/` - Pages statiques (edahabia-terms)

---

## Middleware Personnalises

| Middleware | Fichier | Role |
|------------|---------|------|
| `AdminMiddleware` | `AdminMiddleware.php` | Verifie les roles admin / verificateur / financier + scope complexe (dashboard global vs dashboard complexe) |
| `ClubMiddleware` | `ClubMiddleware.php` | Verifie le role club |
| `CompanyMiddleware` | `CompanyMiddleware.php` | Verifie le role entreprise |
| `PersonMiddleware` | `PersonMiddleware.php` | Verifie le role personne |
| `CheckAdminAccess` | `CheckAdminAccess.php` | Controle d'acces admin |
| `VerifyComplexSignature` | `VerifyComplexSignature.php` | Verification signature API complexe |

---

## Fonctionnalites Cles

1. **Inscription par etapes (Wizard)** - Profil personne en 4+ etapes avec validation JS cote client
2. **Comptes enfants** - Parent peut gerer plusieurs enfants, reservations par personne
3. **Dossiers** - Creation, validation/rejet par admin, impression PDF, autorisation parentale
4. **Reservations** - Selection type -> complexe -> formulaire -> paiement
5. **Paiement en ligne** - Chargily Pay (Edahabia/CIB) + SATIM (Guiddini)
6. **Gestion des complexes** - Activites, horaires, capacites, tarification, places
7. **Assurances** - Gestion bulk, impression, candidats
8. **Fermetures piscines** - Notification email aux abonnes
9. **Dashboard admin** - Statistiques par complexe (souscripteurs, reservations, sexe, age, montants)
10. **Export/Impression** - PDF dossiers, recus, listes personnes
11. **Appareils ZKTeco** - Import utilisateurs depuis lecteurs de presence
12. **Matchs/Billets** - Gestion equipes, matches, selection de places, billetterie
13. **News/Evenements** - CMS basique pour actualites
14. **Nettoyage comptes** - Suppression comptes orphelins sans dossier
15. **API securisee** - Endpoint personnes par complexe avec signature HMAC

---

## Dependances PHP

| Package | Usage |
|---------|-------|
| `laravel/framework ^10.10` | Framework principal |
| `laravel/sanctum ^3.3` | Auth API / tokens |
| `laravel/ui ^4.6` | Scaffolding auth |
| `chargily/chargily-pay ^2.0` | Paiement Edahabia/CIB |
| `guzzlehttp/guzzle ^7.2` | Client HTTP |
| `laravel/tinker ^2.8` | REPL |
| `simplesoftwareio/simple-qrcode` | Generation QR codes |

## Dependances Frontend

| Package | Usage |
|---------|-------|
| `bootstrap ^5.2.3` | Framework CSS |
| `sass ^1.56.1` | Compilation SCSS |
| `vite ^5.0.0` | Bundler |
| `@popperjs/core ^2.11.6` | Tooltips/Popovers |
| `axios ^1.6.4` | Requetes HTTP AJAX |
