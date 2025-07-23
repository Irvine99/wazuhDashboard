# Wazuh IP UI (Dashboard-only)

Interface interne en PHP/Tailwind qui récupère les adresses IP depuis **Wazuh Dashboard (5601)** via son proxy OpenSearch (`/api/console/proxy`) et génère automatiquement un bouton par IP. Un clic ouvre le Discover filtré sur l’IP dans un nouvel onglet.

> **Contexte** : pas d’accès au Wazuh Manager (port 55000). Nous utilisons uniquement le Dashboard.

---

## ✨ Fonctionnalités

* Récupération des IP par agrégation Terms directement dans OpenSearch via le proxy du dashboard.
* Cache local (JSON) pour limiter les appels.
* Rafraîchissement automatique (JS) toutes les X secondes.
* UI rapide avec Tailwind (CDN par défaut).
* Ouverture d’un onglet Discover pré-filtré (query kuery) sur l’IP choisie.

---

## 🧱 Stack & Prérequis

* **PHP ≥ 8.0** (extensions cURL & JSON activées).
* **Composer** (autoload PSR-4).
* Accès HTTP(S) au **Wazuh Dashboard 5601** (utilisateur + mdp valides).
* (Optionnel) Node/npm si tu veux builder Tailwind en local.

---

## 🌲 Arborescence

```
wazuh-ip-ui/
├── app/
│   ├── Config/config.php
│   ├── Contracts/IpProvider.php
│   ├── Controllers/IpController.php
│   ├── Services/DashboardProxyClient.php
│   └── Views/
│       ├── layout.php
│       └── ip/list.php
├── public/
│   ├── index.php
│   └── assets/js/app.js
├── storage/cache/{ips.json,error.log}
├── composer.json
└── vendor/  (créé par Composer)
```

---

## 🚀 Installation rapide

```bash
# 1. Cloner / créer le dossier
git clone <repo> wazuh-ip-ui && cd wazuh-ip-ui

# 2. Installer l'autoload Composer
composer install

# 3. Lancer le serveur PHP (dev)
php -S 0.0.0.0:8080 -t public

# 4. Ouvrir dans le navigateur
http://localhost:8080/
```

---

## ⚙️ Configuration

Édite `app/Config/config.php` :

```php
return [
    'dashboard' => [
        'url'        => 'https://192.168.196.227:5601',
        'user'       => 'admin',
        'password'   => 'admin',
        'verify_ssl' => false // true en prod
    ],
    'cache_ttl'          => 60, // secondes
    'dashboard_base_url' => 'https://192.168.196.227:5601'
];
```

> Si tu veux `.env`, on pourra ajouter Dotenv plus tard.

---

## 🔄 Comment ça marche

1. **Front** appelle `/index.php?ajax=1`.
2. **IpController** vérifie le cache, sinon interroge `DashboardProxyClient`.
3. **DashboardProxyClient** envoie une requête `_search` avec une aggregation Terms (`srcip.keyword` etc.) via `/api/console/proxy`.
4. Retour JSON → liste d’IP uniques → cache → renvoi au front.
5. JS génère un bouton par IP. Clic ⇒ `window.open()` vers Discover avec un filtre kuery sur l’IP.

---

## 🔧 Personnalisation

* **Champ IP différent ?** Modifie :

  * `fieldsToTry` dans `DashboardProxyClient.php` (ex: `source.ip.keyword`).
  * `buildDiscoverUrl()` dans `public/assets/js/app.js` pour le kuery.
* **Période temporelle Discover** : change `from:now-24h` / `to:now` dans l’URL.
* **Intervalle de refresh** : `setInterval(loadIPs, 60000)` (60s) dans `app.js`.
* **Actions avancées** : remplace `window.open()` par une modale, un whois, un POST vers un firewall, etc.

---

## 🔐 Sécurité

* Utilise HTTPS et `verify_ssl=true` si tu as des certs valides (CA interne possible).
* Stocke login/mot de passe dashboard dans un coffre ou un `.env` non versionné.
* Restreins l’accès à l’UI (VPN, auth interne).

---

## 🐞 Dépannage

| Symptôme                              | Piste                                                                                             |
| ------------------------------------- | ------------------------------------------------------------------------------------------------- |
| Page blanche                          | Activer `display_errors`, regarder la console PHP (`php -S`), vérifier `/storage/cache/error.log` |
| `/index.php?ajax=1` → 500             | Mauvaise auth dashboard, champ IP inexistant, SSL… Regarder `error.log`                           |
| JSON vide                             | Pas d’IP dans l’index, mauvais champ, période trop restreinte                                     |
| Boutons s’affichent mais Discover 404 | URL Discover/paramètres `_a`/`_g` incorrects, adapter la fonction JS                              |

### Test sans backend

```bash
printf '["192.0.2.1","198.51.100.10"]' > storage/cache/ips.json
```

---

## 🛣️ Roadmap

* Build Tailwind local + purge.
* UI plus riche (filtres, recherche, pagination).
* Auth utilisateur (LDAP/SSO) + rôles.
* Dockerfile / docker-compose.
* Tests unitaires (PHPUnit) + CI/CD.

---

## 📜 Licence

Projet interne — choisis la licence qui convient (MIT, Apache-2.0, interne…).

---

## 🙌 Contribuer

1. Fork / branche feature
2. PR avec description claire
3. Respecter la structure & les normes de code

---

