# EcoRide – Projet de plateforme de covoiturage

**EcoRide** est une application web permettant à des utilisateurs de proposer ou réserver des trajets.  
Ce projet a été réalisé dans le cadre de la formation **Développeur Web et Web Mobile**.

---

##  Objectifs

- Réduire les frais de déplacement
- Favoriser les trajets partagés
- Proposer un site simple, responsive et accessible

---

##  Fonctionnalités principales

- Inscription et connexion des utilisateurs
- Création et réservation de trajets
- Rôles : passager, chauffeur, employé, administrateur
- Consultation des trajets et des réservations
- Tableau de bord administrateur avec statistiques
- Authentification sécurisée avec JWT
- Autocomplétion des adresses (API adresse.data.gouv.fr)

---

##  Technologies utilisées

- **Frontend** : HTML, CSS, JavaScript, Bootstrap
- **Backend** : Symfony (PHP)
- **Base de données** : MySQL (PHPMyAdmin)
- **Sécurité** : JWT
- **Outils** : Postman, GitHub, VS Code

---

##  Installation du projet

###  Backend Symfony

```bash
cd Backend
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
