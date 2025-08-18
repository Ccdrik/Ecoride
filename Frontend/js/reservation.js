import { getToken } from "./auth/auth.js";

export function initRecherchePage() {
    console.log("✅ Page recherche de trajets chargée");

    const form = document.getElementById("searchTrajetForm");
    const btnSearch = document.getElementById("btn-search-trajet");
    const btnClear = document.getElementById("btn-clear-form");
    const resultsContainer = document.getElementById("results-container");

    if (!form || !btnSearch) return;

    // Action : recherche
    btnSearch.addEventListener("click", async () => {
        const depart = document.getElementById("depart").value.trim();
        const arrivee = document.getElementById("arrivee").value.trim();
        const date = document.getElementById("date").value;
        const heure = document.getElementById("heure").value;
        const places = document.getElementById("places").value;
        const ecologique = document.getElementById("ecologique").checked;

        // On peut vérifier que départ et arrivée sont remplis
        if (!depart || !arrivee) {
            alert("Merci de saisir un départ et une arrivée");
            return;
        }

        try {
            // Appel API Symfony
            let url = "http://127.0.0.1:8000/api/trajets/search";
            const params = new URLSearchParams();

            if (depart) params.append("depart", depart);
            if (arrivee) params.append("arrivee", arrivee);
            if (date) params.append("date", date);
            if (heure) params.append("heure", heure);
            if (places) params.append("places", places);
            if (ecologique) params.append("ecologique", "1");

            url += "?" + params.toString();

            const response = await fetch(url, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${getToken() || ""}`
                }
            });

            const trajets = await response.json();

            if (!response.ok) {
                resultsContainer.innerHTML = `<div class="alert alert-danger">Erreur : ${trajets.error || "Impossible de récupérer les trajets."}</div>`;
                return;
            }

            // Affichage tableau
            if (trajets.length === 0) {
                resultsContainer.innerHTML = `<div class="alert alert-warning">Aucun trajet trouvé 🚗</div>`;
                return;
            }

            let html = `
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Départ</th>
                            <th>Arrivée</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Places</th>
                            <th>Prix (€)</th>
                            <th>Éco</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            trajets.forEach(t => {
                html += `
                    <tr>
                        <td>${t.depart}</td>
                        <td>${t.arrivee}</td>
                        <td>${t.date || "-"}</td>
                        <td>${t.heure || "-"}</td>
                        <td>${t.places}</td>
                        <td>${t.prix ?? "0"}</td>
                        <td>${t.ecologique ? "🌱" : "❌"}</td>
                        <td>
                            <a href="/reservation?id=${t.id}" class="btn btn-sm btn-primary">
                                Réserver
                            </a>
                        </td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;
            resultsContainer.innerHTML = html;

        } catch (error) {
            console.error("Erreur recherche :", error);
            resultsContainer.innerHTML = `<div class="alert alert-danger">Erreur de connexion serveur</div>`;
        }
    });

    // Action : réinitialiser
    btnClear.addEventListener("click", () => {
        form.reset();
        resultsContainer.innerHTML = "";
    });
}

export default initRecherchePage;
