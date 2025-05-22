import {
    getToken,
    isConnected,
    getRole,
    showAndHideElementsForRoles
} from "./auth/auth.js";

import "./auth/signout.js";

export function initScriptPage() {
    console.log("✅ script.js chargé !");

    window.addEventListener("load", () => {
        try {
            showAndHideElementsForRoles();

            const form = document.getElementById("form-recherche");
            const departInput = document.getElementById("depart");
            const arriveeInput = document.getElementById("arrivee");
            const dateInput = document.getElementById("date");
            const container = document.getElementById("liste-trajets");
            const messageAucun = document.getElementById("message-aucun-resultat");

            // 🔁 1. Rechercher automatiquement si paramètres présents dans l'URL
            const params = new URLSearchParams(window.location.search);
            if (params.has("depart") && params.has("arrivee") && params.has("date")) {
                departInput.value = params.get("depart");
                arriveeInput.value = params.get("arrivee");
                dateInput.value = params.get("date");
                afficherTrajets(params.get("depart"), params.get("arrivee"), params.get("date"), container, messageAucun);
            }

            // 🔍 2. Formulaire de recherche
            if (form) {
                form.addEventListener("submit", async (e) => {
                    e.preventDefault();

                    const depart = departInput.value.trim();
                    const arrivee = arriveeInput.value.trim();
                    const date = dateInput.value;

                    if (!depart || !arrivee || !date) {
                        alert("Merci de remplir tous les champs !");
                        return;
                    }

                    // Mettre à jour l’URL avec les paramètres de recherche
                    const url = `?depart=${encodeURIComponent(depart)}&arrivee=${encodeURIComponent(arrivee)}&date=${encodeURIComponent(date)}`;
                    history.pushState({}, "", url);

                    afficherTrajets(depart, arrivee, date, container, messageAucun);
                });
            }
        } catch (e) {
            console.error("❌ Erreur dans initScriptPage :", e);
        }
    });
}

export default initScriptPage;

// 🔁 Fonction centrale d'affichage
function afficherTrajets(depart, arrivee, date, container, messageAucun) {
    console.log("🔍 Recherche de trajets lancée...");

    const url = `http://127.0.0.1:8000/api/trajets?depart=${encodeURIComponent(depart)}&arrivee=${encodeURIComponent(arrivee)}&date=${encodeURIComponent(date)}`;

    fetch(url, {
        headers: {
            "Authorization": `Bearer ${getToken()}`
        }
    })
        .then(res => {
            if (!res.ok) throw new Error("Erreur API");
            return res.json();
        })
        .then(trajets => {
            console.log("📥 Trajets reçus :", trajets);
            container.innerHTML = "";
            messageAucun.classList.add("d-none");

            if (trajets.length === 0) {
                messageAucun.classList.remove("d-none");
                return;
            }

            trajets.forEach(trajet => {
                console.log("🔁 Trajet affiché :", trajet);
                const [dateJour, heure] = trajet.dateDepart.split(" ");
                const card = document.createElement("div");
                card.className = "col-md-6 col-lg-4";

                card.innerHTML = `
                
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">${trajet.villeDepart} → ${trajet.villeArrivee}</h5>
                            <p class="card-text">
                                📅 ${dateJour} à ${heure}<br>
                                👥 ${trajet.nbPlaces} place(s)<br>
                                💰 ${trajet.prix} crédit(s)<br>
                                ${trajet.ecologique ? "🌱 Écologique" : "🚗 Standard"}<br>
                                🧑 Chauffeur : ${trajet.chauffeur?.email ?? "?"}
                            </p>
                            <button class="btn btn-sm btn-success" data-id="${trajet.id}">
                                Réserver
                            </button>
                        </div>
                    </div>
                
                `;
                container.appendChild(card);
            });
        })
        .catch(err => {
            console.error("Erreur API :", err);
            container.innerHTML = `<p class="text-danger text-center">Erreur lors du chargement des trajets.</p>`;
        });
}
