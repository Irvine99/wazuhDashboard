# wazuhDashboard
# Wazuh IP UI

Interface web interne en PHP/Tailwind pour lister automatiquement les adresses IP détectées par Wazuh et générer un bouton/action par IP.

---

## 🚀 Fonctionnalités

* Récupération des IP via l’API REST de Wazuh.
* Déduplication et cache local (fichier JSON) pour alléger l’API.
* Rafraîchissement automatique côté client (JS) toutes les X secondes.
* UI réactive avec Tailwind CSS (via CDN ou build local).
* Architecture simple MVC “light” : Services, Controllers, Views.
* Point d’entrée unique (`public/index.php`) pour servir l’app et l’API Ajax.

---

## 🧱 Stack & Prérequis

* **PHP** ≥ 8.0 (cURL, JSON activés).
* **Composer** (optionnel, si tu veux autoload PSR-4).
* **Node.js/NPM** (optionnel, si tu build Tailwind toi-même).
* Accès HTTP(S) sortant vers l’API Wazuh.

---

## 📁 Architecture des dossiers

```
wazuh-ip-ui/
├── app/
│   ├── Config/
│   │   └── config.php
│   ├── Services/
│   │   └── WazuhClient.php
│   ├── Controllers/
│   │   └── IpController.php
│   └── Views/
│       ├── layout.php
│       └── ip/
│           └── list.php
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── js/app.js
│   │   └── css/tailwind.css   (si build local)
└── storage/
    └── cache/ips.json
```

> DocumentRoot (ou vhost) doit pointer sur le dossier `public/`.

---

## 🔧 Installation rapide

```bash
# 1. Récupère le code
git clone https://git.example.com/ton-projet/wazuh-ip-ui.git
cd wazuh-ip-ui

# 2. (Optionnel) Installer les dépendances PHP si tu utilises Composer
composer install

# 3. Configurer les droits d’écriture sur le cache
mkdir -p storage/cache
chmod -R 775 storage

# 4. (Optionnel) Build Tailwind local
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
# Puis lance la compilation (voir section Tailwind ci-dessous)
```

---

## ⚙️ Configuration

Fichier : `app/Config/config.php`

```php
<?php
return [
    'wazuh' => [
        'url'       => 'https://wazuh.example.com:55000',
        'user'      => 'wazuh_user',
        'password'  => 'wazuh_pass',
        'verify_ssl'=> true, // false seulement en dev
    ],
    'cache_ttl' => 60, // durée de cache en secondes
];
```

### Variables d’environnement (optionnel)

Tu peux charger les identifiants via `.env` et `vlucas/phpdotenv` si tu préfères :

```env
WAZUH_URL=https://wazuh.example.com:55000
WAZUH_USER=wazuh_user
WAZUH_PASS=wazuh_pass
VERIFY_SSL=true
CACHE_TTL=60
```

---

## 🖼️ Front-end & Tailwind

### Option 1 — CDN (le plus rapide)

Dans `layout.php` :

```html
<script src="https://cdn.tailwindcss.com"></script>
```

### Option 2 — Build local (prod/stabilité)

1. Ajoute Tailwind :

   ```bash
   npm install -D tailwindcss postcss autoprefixer
   npx tailwindcss init -p
   ```
2. Configure `tailwind.config.js` :

   ```js
   module.exports = {
     content: [
       "./app/Views/**/*.php",
       "./public/assets/js/**/*.js"
     ],
     theme: { extend: {} },
     plugins: [],
   }
   ```
3. Fichier source CSS `public/assets/css/input.css` :

   ```css
   @tailwind base;
   @tailwind components;
   @tailwind utilities;
   ```
4. Build :

   ```bash
   npx tailwindcss -i ./public/assets/css/input.css -o ./public/assets/css/tailwind.css --watch
   ```
5. Dans `layout.php`, remplace le CDN par :

   ```html
   <link rel="stylesheet" href="/assets/css/tailwind.css">
   ```

---

## 🔄 Flux de données

1. **Front** charge `/index.php?ajax=1` (fetch JS).
2. **IpController** vérifie le cache, sinon appelle `WazuhClient`.
3. **WazuhClient** s’authentifie, récupère les alertes (champ `srcip`) et renvoie un tableau d’IP uniques.
4. Le JS génère un bouton par IP (classe Tailwind).
5. L’action du bouton est gérée dans `app.js` (`actionOnIP`).

---

## ✏️ Personnalisation

* **Filtrer les IP** : adapte la requête dans `WazuhClient::getIPs()` (dates, niveaux d’alertes, autres champs).
* **Actions bouton** : modifie `actionOnIP(ip)` (whois, blocage firewall, lien détail Wazuh, etc.).
* **Intervalle de refresh** : `setInterval(loadIPs, 60000)` (60 s) dans `app.js`.
* **UI** : change les classes Tailwind ou ajoute des composants (modales, toasts…).

---

## 🔐 Sécurité

* Active `verify_ssl` et installe un certificat CA correct.
* Mets les identifiants Wazuh hors du code (env, vault).
* Protège l’accès au site (auth interne, VPN, IP allowlist).
* Valide/échappe toutes les sorties même si l’IP semble “inoffensive”.

---

## 🐞 Dépannage

| Problème         | Piste                                                                              |
| ---------------- | ---------------------------------------------------------------------------------- |
| `Auth failed`    | Vérifie user/pass Wazuh, droits API, URL et certif SSL                             |
| `cURL error 60`  | Certificat SSL inconnu → installe/indique le CA ou désactive SSL uniquement en dev |
| Retour JSON vide | Pas d’alertes avec `srcip`, filtre trop strict ou mauvais champ                    |
| 500/Timeout      | Augmente `limit`, optimise requêtes, active le cache                               |

Active le log d’erreurs PHP en dev :

```ini
error_reporting = E_ALL
display_errors = On
```

---

## 🚀 Déploiement

### PHP built-in (dev)

```bash
php -S 0.0.0.0:8080 -t public
```

### Nginx (exemple)

```nginx
server {
    listen 80;
    server_name ip-ui.local;
    root /var/www/wazuh-ip-ui/public;

    index index.php;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## 📌 Roadmap / Idées

* Pagination et recherche client-side.
* Actions multiples (blocklist firewall, ticketing, etc.).
* Authentification utilisateur (LDAP, SSO) et RBAC.
* Historique des IP déjà vues / stats.
* Passage à un micro-framework (Slim/Lumen) si le projet grossit.

---

## 📜 Licence

Choisis ta licence (MIT, Apache-2.0, interne…). Exemple :

```
MIT License

Copyright (c) 2025 ...
```

---

## 🙌 Contribuer

1. Fork / branche feature.
2. PR avec description claire.
3. Linters/tests si mis en place.

---

**Questions / besoin d’aide ?** Ouvre une issue ou ping-moi 😉
