# EcoRide – Projet de plateforme de covoiturage

EcoRide est une application web qui permet à des utilisateurs de proposer ou réserver des trajets.  
Le projet a été réalisé dans le cadre de la formation Développeur Web et Web Mobile.

## Objectifs

- Réduire les frais de déplacement
- Favoriser les trajets partagés
- Proposer un site simple et responsive

## Fonctionnalités principales

- Inscription et connexion des utilisateurs
- Création et réservation de trajets
- Rôles différents : passager, chauffeur, employé, administrateur
- Consultation des trajets et des réservations
- Tableau de bord administrateur avec statistiques
- Système de sécurité avec token JWT
- Autocomplétion des adresses (API adresse.data.gouv.fr)

## Technologies utilisées

- **Frontend** : HTML, CSS, JavaScript, Bootstrap
- **Backend** : Symfony (PHP)
- **Base de données** : MySQL/PHPMyAdmin
- **Sécurité** : JWT 
- **Outils** : Postman, GitHub, VS Code

## Installation

### Backend Symfony


cd Front/backend/api
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start


## Frontend

Ouvrir le fichier index.html dans le dossier Front/ avec Live Server ou dans un navigateur.


## Données de test

    Email : admin@ecoride.fr
    Mot de passe : admin123



## Projet réalisé par Claire Cédric dans le cadre de la formation TP – Développeur Web et Web Mobile.