import { getToken, handle401 } from "./auth/auth.js";

export function initCovoituragesPage() {
    const container = document.getElementById("resultats-trajets");
    const API_URL = "http://127.0.0.1:8000/api";

    container.innerHTML = `<tr><td colspan="9">Chargement...</td></tr>`;

    fetch(`${API_URL}/trajets`, {
        headers: { Authorization: `Bearer ${getToken()}` }
    })
        .then(res => {
            if (!res.ok) throw new Error("Erreur API");
            return res.json();
        })
        .then(data => {
            container.innerHTML = "";

            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = `<tr><td colspan="9">Aucun covoiturage trouvé.</td></tr>`;
                return;
            }

            data.forEach(trajet => {
                const [date, heure] = trajet.dateDepart.split(" ");
                const row = document.createElement("tr");

                row.innerHTML = `
  <td>${trajet.villeDepart}</td>
  <td>${trajet.villeArrivee}</td>
  <td>${date}</td>
  <td>${heure}</td>
  <td>${trajet.nbPlaces}</td>
  <td>${trajet.prix} crédits</td>
  <td>${trajet.ecologique ? "🌱" : "🚗"}</td>
  <td>${trajet.chauffeur?.email ?? "?"}</td>
  <td>
    <button class="btn btn-success btn-sm btn-reserver" data-id="${trajet.id}">Réserver</button>
    <button class="btn btn-info btn-sm btn-detail" data-id="${trajet.id}">Détail</button>
  </td>
`;

                container.appendChild(row);
            });

            // Gestion clics sur "Réserver"
            document.querySelectorAll(".btn-reserver").forEach(btn => {
                btn.addEventListener("click", async () => {
                    const trajetId = btn.dataset.id;
                    try {
                        const res = await fetch(`${API_URL}/reservations`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Authorization": `Bearer ${getToken()}`
                            },
                            body: JSON.stringify({ trajetId })
                        });

                        const raw = await res.text();
                        console.log("Réponse brute (debug) :", raw);

                        let result;
                        try {
                            result = JSON.parse(raw);
                        } catch (e) {
                            result = { message: raw };
                        }

                        if (handle401(res)) return;

                        if (!res.ok) {
                            alert("❌ Réservation impossible : " + (result.message ?? "Erreur inconnue"));
                            return;
                        }

                        alert("✅ Réservation confirmée !");
                        btn.classList.replace("btn-success", "btn-secondary");
                        btn.textContent = "Réservé";
                        btn.disabled = true;

                    } catch (e) {
                        console.error(e);
                        alert("❌ Erreur technique");
                    }
                });
            });

            document.querySelectorAll(".btn-detail").forEach(btn => {
                btn.addEventListener("click", async () => {
                    const trajetId = btn.dataset.id;
                    try {
                        const res = await fetch(`${API_URL}/trajets/${trajetId}`, {
                            headers: {
                                Authorization: `Bearer ${getToken()}`
                            }
                        });

                        if (handle401(res)) return;

                        const trajet = await res.json();
                        const chauffeur = trajet.chauffeur ?? {};

                        const html = `
              <p><strong>Nom :</strong> ${chauffeur.nom ?? "Non renseigné"}</p>
              <p><strong>Email :</strong> ${chauffeur.email ?? "Non renseigné"}</p>
              <p><strong>Fumeur accepté :</strong> ${trajet.fumeur ? "Oui" : "Non"}</p>
              <p><strong>Animaux acceptés :</strong> ${trajet.animaux ? "Oui" : "Non"}</p>
            `;

                        document.getElementById("modal-detail-body").innerHTML = html;

                        const modal = new bootstrap.Modal(document.getElementById("modalDetail"));
                        modal.show();

                    } catch (e) {
                        console.error(e);
                        alert("❌ Erreur lors du chargement des détails");
                    }
                });
            });
        })
        .catch(err => {
            console.error("Erreur fetch :", err);
            container.innerHTML = `<tr><td colspan="9" class="text-danger text-center">Erreur lors du chargement</td></tr>`;
        });
}

export default initCovoituragesPage;

