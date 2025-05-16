# API de Génération d'Utilisateurs

Une API RESTful développée avec Laravel 12 permettant la génération et la gestion d'utilisateurs fictifs.

> **Note:** Cette API a été développée selon les spécifications demandées. Les fonctionnalités de base sont opérationnelles, mais certains tests sont encore en cours de développement.

## Fonctionnalités

- Génération d'utilisateurs fictifs téléchargeables en format JSON
- Import d'utilisateurs à partir d'un fichier JSON
- Authentification via JWT (JSON Web Tokens)
- Contrôle d'accès basé sur les rôles (admin/user)
- Documentation Swagger/OpenAPI intégrée
- Tests unitaires

## Endpoints de l'API

### Génération d'utilisateurs
- `GET /api/users/generate?count=10`
  - Génère un fichier JSON téléchargeable contenant des utilisateurs fictifs
  - Paramètre : `count` (nombre d'utilisateurs à générer)
  - Authentification : Non requise

### Import d'utilisateurs
- `POST /api/users/batch`
  - Importe des utilisateurs à partir d'un fichier JSON
  - Format : `multipart/form-data` avec champ `file`
  - Authentification : Non requise

### Authentification
- `POST /api/auth`
  - Authentifie un utilisateur et génère un token JWT
  - Format : `application/json` avec `username` et `password`
  - Le champ `username` accepte soit un email, soit un nom d'utilisateur

### Consultation du profil
- `GET /api/users/me`
  - Retourne les informations de l'utilisateur connecté
  - Authentification : Token JWT requis

### Consultation d'un profil utilisateur
- `GET /api/users/{username}`
  - Retourne les informations d'un utilisateur spécifique
  - Authentification : Token JWT requis
  - Restrictions : 
    - Les utilisateurs avec le rôle 'admin' peuvent voir tous les profils
    - Les utilisateurs avec le rôle 'user' ne peuvent voir que leur propre profil

## Structure des données utilisateur

```json
{
  "firstName": "string",
  "lastName": "string",
  "birthDate": "date au format ISO 8601",
  "city": "string",
  "country": "code ISO2 (ex: FR)",
  "avatar": "url valide d'une image",
  "company": "string",
  "jobPosition": "string",
  "mobile": "numéro de téléphone valide",
  "username": "identifiant unique",
  "email": "adresse email unique",
  "password": "mot de passe (6-10 caractères)",
  "role": "admin" ou "user"
}
```

## Installation

1. Cloner le dépôt :
   ```bash
   git clone [URL du dépôt]
   cd usersGenerator-app
   ```

2. Installer les dépendances :
   ```bash
   composer install
   ```

3. Configurer l'environnement :
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configurer la base de données SQLite :
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

5. Lancer l'application sur le port 9090 :
   ```bash
   php artisan serve --port=9090
   ```

## Documentation de l'API

La documentation Swagger/OpenAPI est disponible à l'URL suivante :
- Interface interactive : `/docs` ou directement à `/api/documentation`

## Tests

Pour exécuter les tests :
```bash
php artisan test
```

> Note: Certains tests sont actuellement skippés et seront à corriger dans une prochaine version.

## Choix techniques

- **Laravel 12** : Le framework PHP le plus récent, offrant une grande flexibilité et des outils modernes
- **SQLite** : Base de données légère, idéale pour ce type d'application sans besoin d'un SGBD séparé
- **php-open-source-saver/jwt-auth** : Gestion de l’authentification basée sur des JWT (JSON Web Tokens) dans une API Laravel.
- **L5-Swagger** : Intégration OpenAPI/Swagger pour documenter l'API
- **Faker** : Bibliothèque pour générer des données fictives réalistes
