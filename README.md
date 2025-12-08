# Notes

Notes est une application qui permet la gestion des notes pour une classe de primaire
Cette application est utilisée pour une école de CP.

---

## 🚀 Fonctionnalités

- Gestion des matières, des classes, des enfants
- L'utilisateur pourra créer des evaluations afin de rentrer des notes pour chaque élève.

---

## 🧰 Stack technique

- **Backend** : Symfony (PHP)
- **Frontend** : Twig
- **Base de données** : MariaDB

---

## ⚙️ Installation

### Prérequis

- PHP >= 8.2
- Composer
- Serveur MariaDB
- Symfony CLI (optionnel mais recommandé)

### Étapes

1. Clonez le projet :
   ```bash
   git clone https://github.com/thomaroger/notes.git
   cd notes
   ```

2. Copiez le fichier d’environnement :
   ```bash
   cp .env.dist .env
   ```

3. Modifiez le fichier `.env` pour y renseigner :
   - `APP_SECRET`
   - `DATABASE_URL` : en fonction du login, mot de passe, hôte et port de votre serveur MariaDB.

   Exemple :
   ```
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/notes"
   APP_SECRET=un_secret_aleatoire
   ```

4. Installez les dépendances PHP :
   ```bash
   composer install
   ```

5. Créez la base de données et lancez les migrations :
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

6. (Optionnel) Chargez des données de test :

```bash
   php bin/console doctrine:fixtures:load
   ```

7. Lancez le serveur de développement :
   ```bash
   symfony server:start
   ```

---

## 🛠️ Configuration

### Variables d’environnement importantes

| Variable       | Description                              |
|----------------|------------------------------------------|
| `APP_SECRET`   | Clé secrète de l’application Symfony     |
| `DATABASE_URL` | URL de connexion à la base de données    |

---

## 📁 Structure rapide du projet

```
.
├── config/         # Configuration Symfony
├── public/         # Point d’entrée web
├── src/            # Code PHP (contrôleurs, entités, etc.)
├── templates/      # Fichiers Twig
├── migrations/     # Migrations de base de données
├── .env            # Variables d’environnement
└── ...
```

---

## 📸 Captures d'écran


---

## 👤 Auteur

- Thomas Roger 

---

## 📄 Licence

Projet privé – non redistribuable.

---

##  TODO
check partie enfants
Refactoring (Service, Repository)
Sécurité
Format