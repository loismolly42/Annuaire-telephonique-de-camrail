# Camrail Directory — Guide d'installation

## Structure des fichiers

```
camrail/
├── login.php                  ← Page de connexion
├── dashboard.php              ← Tableau de bord (protégé)
├── directory.php              ← Répertoire des employés (protégé)
├── departments.php            ← Départements (protégé)
├── profile.php                ← Profil employé (protégé)
├── settings.php               ← Paramètres / modifier profil (protégé)
├── admin.php                  ← Administration (admin uniquement)
├── generate_hash.php          ← Utilitaire hash bcrypt (⚠️ supprimer en prod)
├── database_demo.sql          ← Base de données complète avec données de démo
│
├── backend/
│   ├── config.php             ← Configuration BDD + helpers
│   ├── session_guard.php      ← Protection des pages (sessions)
│   ├── auth.php               ← Authentification POST
│   ├── logout.php             ← Déconnexion
│   ├── api.php                ← API JSON REST (toutes les données)
│   └── export.php             ← Export CSV / PDF (admin)
│
├── includes/
│   ├── sidebar.php            ← Sidebar partagée
│   └── topbar.php             ← Topbar + recherche live
│
├── css/
│   ├── main.css  
    └──login.css     ← (votre fichier existant)
│
└── js/
    ├── login.js               ← (votre fichier existant)
    └── main.js                ← (votre fichier existant)
```

---

## Installation

### 1. Prérequis
- PHP 8.1+
- MySQL 5.7+ ou MariaDB 10.4+
- Serveur web (Apache/Nginx) avec mod_rewrite

### 2. Base de données

```bash
# Créer la base et importer les données de démo
mysql -u root -p < database_demo.sql
```

### 3. Configuration

Éditez `backend/config.php` :

```php
define('DB_HOST',  'localhost');
define('DB_NAME',  'camrail_annuaire');
define('DB_USER',  'root');
define('DB_PASS',  '');
```

### 4. Comptes de démo

| Login      | Mot de passe | Rôle          |
|------------|-------------|---------------|
| CR-000001  | admin123    | Administrateur|
| CR-000002  | user123     | Utilisateur   |
| CR-000003  | sarah456    | Utilisateur   |

> ⚠️ Les hash bcrypt dans `database_demo.sql` sont des exemples.
> Utilisez `generate_hash.php` pour régénérer des hash valides.

### 5. Régénérer les mots de passe (important)

```bash
# Via CLI
php generate_hash.php admin123
# → copiez le hash dans la table Utilisateurs

# Ou via navigateur (dev uniquement)
http://localhost/camrail_annuaire/generate_hash.php
```

Puis mettez à jour la BDD :
```sql
UPDATE Utilisateurs SET mot_de_passe = 'HASH_GENERE' WHERE login = 'CR-000001';
```

---

## API Reference

Toutes les routes : `GET|POST backend/api.php?action=xxx`
Session valide requise. Routes admin marquées 🔒.

| Action               | Méthode | Admin | Description                        |
|----------------------|---------|-------|------------------------------------|
| `employees`          | GET     |       | Liste paginée (q, dept, page)       |
| `employee`           | GET     |       | Fiche détaillée (?id=N)            |
| `departments`        | GET     |       | Départements + comptage            |
| `stats`              | GET     |       | KPI dashboard                      |
| `search`             | GET     |       | Recherche globale (?q=...)         |
| `favorites`          | GET     |       | Mes favoris                        |
| `add_favorite`       | POST    |       | Ajouter aux favoris                |
| `remove_favorite`    | POST    |       | Retirer des favoris                |
| `activity_log`       | GET     | 🔒    | Journal d'activité                 |
| `validation_queue`   | GET     | 🔒    | File de validation                 |
| `approve_request`    | POST    | 🔒    | Approuver une demande              |
| `reject_request`     | POST    | 🔒    | Refuser une demande                |
| `add_employee`       | POST    | 🔒    | Ajouter un employé                 |
| `delete_employee`    | POST    | 🔒    | Supprimer un employé               |
| `add_department`     | POST    | 🔒    | Ajouter un département             |
| `delete_department`  | POST    | 🔒    | Supprimer un département           |
| `update_profile`     | POST    |       | Modifier son profil                |
| `request_modification`| POST  |       | Soumettre une demande de modif     |
| `change_password`    | POST    |       | Changer son mot de passe           |

---

## Sécurité

- ✅ Sessions PHP avec régénération d'ID à la connexion
- ✅ Mots de passe hashés en bcrypt (password_hash)
- ✅ Anti-brute-force (5 tentatives → blocage 15 min)
- ✅ Préparation PDO (protection injection SQL)
- ✅ Vérification de rôle sur chaque route API
- ✅ Sliding session (renouvellement automatique)
- ✅ Journal d'activité dans la table Historique
- ✅ Workflow de validation pour les modifications

## À faire en production

- [ ] Activer `'secure' => true` dans les cookies (HTTPS)
- [ ] Supprimer `generate_hash.php`
- [ ] Changer les credentials BDD dans `config.php`
- [ ] Régénérer tous les mots de passe de démo
- [ ] Configurer les logs PHP vers un fichier dédié
- [ ] Ajouter un `.htaccess` pour protéger le dossier `backend/`