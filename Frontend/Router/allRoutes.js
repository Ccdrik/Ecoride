import Route from "./Route.js";

export const allRoutes = [
    new Route("/", "Accueil", "/pages/home.html", [], "js/home-trajets.js"),

    // Authentification
    new Route("/signin", "Connexion", "/pages/auth/signin.html", [], "js/auth/signin.js"),
    new Route("/signup", "Inscription", "/pages/auth/signup.html", [], "js/auth/signup.js"),
    new Route("/account", "Mon Compte", "/pages/auth/account.html", ["passager", "chauffeur", "employe", "admin"], "js/auth/account.js"),
    new Route("/editPassword", "Modifier mon mot de passe", "/pages/auth/editPassword.html", ["passager", "chauffeur", "employe", "admin"], "js/auth/editPassword.js"),

    // Trajets
    new Route("/covoiturages", "Résultats", "/pages/covoiturages.html", ["passager", "chauffeur"], "js/covoiturages.js"),
    new Route("/creertrajet", "Créer un trajet", "/pages/creertrajet.html", ["chauffeur"], "js/creer-trajet.js"),
    new Route("/startstoptrajet", "Etat de mon trajet", "/pages/startstoptrajet.html", ["chauffeur"], "js/mes_trajets.js"),
    new Route("/mestrajets", "Mes trajets", "/pages/mestrajets.html", ["chauffeur", "passager"], "js/mes_trajets.js"),
    new Route("/detail", "Détail trajet", "/pages/detail.html", ["chauffeur", "passager"], "js/detail.js"),

    // Réservations
    new Route("/reservations", "Mes réservations", "/pages/reservations.html", ["passager", "chauffeur"], "js/mes-reservations.js"),

    //  Véhicule 
    new Route("/voiture", "Ajouter un véhicule", "/pages/voiture.html", ["chauffeur"], "js/voiture.js"),

    // Admin
    new Route("/administrateur", "Administrateur", "/pages/administrateur.html", ["admin"], "js/administrateur.js"),
    new Route("/admincreeruser", "Créer un utilisateur", "/pages/admincreeruser.html", ["admin"], "js/admincreeruser.js"),

    // Employé
    new Route("/employe", "Espace Employés", "/pages/employe.html", ["employe"], "js/employe.js"),

    // Utilisateur
    new Route("/utilisateur", "Espace Utilisateur", "/pages/utilisateur.html", ["passager"], "js/utilisateur.js"),
    new Route("/preferences", "Préférences", "/pages/preferences.html", ["passager", "chauffeur"], "js/preferences.js"),

    // Autres
    new Route("/contact", "Contact", "/pages/contact.html", [], "js/contact.js"),
    new Route("/mentionslegales", "Mentions légales", "/pages/mentionslegales.html", []),

    // 404
    new Route("/404", "Page introuvable", "/pages/404.html", [])
];

export const websiteName = "EcoRide";
