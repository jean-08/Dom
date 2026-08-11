# DomAssist

DomAssist est une application web de mise en relation entre clients et prestataires pour la gestion de demandes de services, la prise de rendez-vous, les diagnostics, les solutions et les interventions.

## Fonctionnalités principales

- Inscription et connexion utilisateur
- Profil client et profil prestataire
- Publication de demandes de services
- Réception de propositions de prestataires
- Sélection d’un prestataire
- Diagnostic, solution et intervention
- Messagerie interne et notifications
- Gestion des avis et réputation
- Interface d’administration

## Stack technique

- PHP 8+
- PostgreSQL
- HTML / CSS / JavaScript
- Architecture MVC simple

## Prérequis

- PHP 8+
- PostgreSQL
- Serveur web local (Apache, Nginx ou PHP built-in server)
- Extension PHP PDO PostgreSQL

## Installation

1. Cloner le dépôt :
   ```bash
   git clone <url-du-repo>
   cd DomAssist
   ```

2. Importer la base de données :
   ```bash
   sudo -iu postgres psql
   create database domassist owner <user>;
   exit;
   psql -U <user> -d domassist -f DomAssist.sql;
   ```

3. Configurer la connexion PostgreSQL :
   - Ouvrir le fichier [config/database.php](config/database.php)
   - Adapter les paramètres `host`, `port`, `dbname`, `user` et `password`

4. Démarrer l’application :
   ```bash
   php -S 127.0.0.1:8000
   ```

5. Ouvrir l’application dans votre navigateur :
   ```text
   http://127.0.0.1:8000/index.php
   ```

## Structure du projet

- [controllers/](controllers/) : contrôleurs HTTP
- [models/](models/) : accès aux données et logique métier
- [views/](views/) : templates HTML
- [utils/](utils/) : helpers utilitaires
- [public/](public/) : assets publics et uploads
- [config/](config/) : configuration de l’application
- [tests/](tests/) : tests de validation

## Comptes de démonstration

Un utilisateur administrateur est initialement créé via le seed SQL :

- Email : admin@domassist.com
- Mot de passe : Admin1234

## Développement

Le projet suit une logique MVC simple avec :
- contrôleurs pour la navigation et les actions métier
- modèles pour l’accès à PostgreSQL
- vues pour l’affichage HTML

## Notes

Le schéma SQL est fourni dans [DomAssist.sql](DomAssist.sql) et constitue la référence de la base de données.

## Licence

Ce projet est fourni à titre de démonstration et de développement interne.
