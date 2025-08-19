// Frontend/js/covoiturages.js
import { getToken, handle401 } from "./auth/auth.js";

export function initCovoituragesPage() {
    const container = document.getElementById("resultats-trajets");
    const formFiltres = document.getElementById("form-filtres");
    const modalBody = document.getElementById("modal-detail-body");

    // ⚙️ Backend
    const API_URL = "http://127.0.0.1:8000/api";

    // 🔎 Params de la barre d'adresse (venant de la home)
    const url = new URL(window.location.href);
    const baseParams = {
        depart: url.searchParams.get("depart") || "",
        arrivee: url.searchParams.get("arrivee") || "",
        date: url.searchParams.get("date") || ""
    };

    // 🧰 Construit l'URL d'appel en combinant base + filtres
    function buildApiUrl(extra = {}) {
        const u = new URL(`${API_URL}/trajets`);
        const full = { ...baseParams, ...extra };
        Object.keys(full).forEach((k) => {
            const v = full[k];
            if (v !== null && v !== undefined && String(v).trim() !== "") {
                u.searchParams.append(k, v);
            }
        });
        return u.toString();
    }

    // 🗂️ Rendu d'une ligne
    function renderRow(trajet) {
        // compat nommages
        const villeDepart = trajet.villeDepart ?? trajet.depart ?? "-";
        const villeArrivee = trajet.villeArrivee ?? trajet.arrivee ?? "-";
        const dateHeure = trajet.dateDepart ?? trajet.date ?? "";
        const [date = "-", heure = "-"] = dateHeure.split(" ");
        const nbPlaces = trajet.nbPlaces ?? trajet.places ?? trajet.placesDisponibles ?? "-";
        const prix = trajet.prix ?? trajet.prixParPassager ?? "-";
        const ecolo = trajet.ecologique ? "🌱" : "🚗";
        const chauffeur = trajet.chauffeur?.email ?? trajet.chauffeur?.pseudo ?? "?";

        return `
      <tr>
        <td>${villeDepart}</td>
        <td>${villeArrivee}</td>
        <td>${date}</td>
        <td>${heure}</td>
        <td>${nbPlaces}</td>
        <td>${prix} ${typeof prix === "number" ? "crédits" : ""}</td>
        <td>${ecolo}</td>
        <td>${chauffeur}</td>
        <td>
          <button class="btn btn-success btn-sm btn-reserver" data-id="${trajet.id}">Réserver</button>
          <button class="btn btn-info btn-sm btn-detail" data-id="${trajet.id}">Détail</button>
        </td>
      </tr>
    `;
    }

    // 📥 Charge et affiche la liste
    async function chargerTrajets(extra = {}) {
        container.innerHTML = `<tr><td colspan="9">Chargement...</td></tr>`;
        try {
            const res = await fetch(buildApiUrl(extra), {
                headers: { Authorization: `Bearer ${getToken()}` },
                credentials: "include",
            });

            if (handle401(res)) return;
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const data = await res.json();
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = `<tr><td colspan="9">Aucun covoiturage trouvé. Essayez de modifier la date ou les filtres.</td></tr>`;
                return;
            }

            container.innerHTML = data.map(renderRow).join("");

            // 🔘 Réserver
            document.querySelectorAll(".btn-reserver").forEach((btn) => {
                btn.addEventListener("click", () => reserver(btn));
            });

            // ℹ️ Détail
            document.querySelectorAll(".btn-detail").forEach((btn) => {
                btn.addEventListener("click", () => ouvrirDetail(btn));
            });

        } catch (err) {
            console.error("Erreur fetch :", err);
            container.innerHTML = `<tr><td colspan="9" class="text-danger text-center">Erreur lors du chargement</td></tr>`;
        }
    }

    // ✅ Réservation
    async function reserver(btn) {
        const trajetId = btn.dataset.id;
        try {
            const res = await fetch(`${API_URL}/reservations`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${getToken()}`,
                },
                body: JSON.stringify({ trajetId }),
            });

            const raw = await res.text();
            let result;
            try { result = JSON.parse(raw); } catch { result = { message: raw }; }

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
    }

    // 🔎 Détail trajet (modale)
    async function ouvrirDetail(btn) {
        const trajetId = btn.dataset.id;
        try {
            modalBody.innerHTML = "Chargement...";
            const res = await fetch(`${API_URL}/trajets/${trajetId}`, {
                headers: { Authorization: `Bearer ${getToken()}` },
                credentials: "include",
            });

            if (handle401(res)) return;
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const trajet = await res.json();
            const chauffeur = trajet.chauffeur ?? {};
            const vehicule = trajet.vehicule ?? {};

            modalBody.innerHTML = `
        <div>
          <h5>${trajet.villeDepart ?? trajet.depart ?? "-"} → ${trajet.villeArrivee ?? trajet.arrivee ?? "-"}</h5>
          <p><strong>Date :</strong> ${(trajet.dateDepart ?? trajet.date ?? "").split(" ")[0] || "-"}</p>
          <p><strong>Heure départ :</strong> ${(trajet.dateDepart ?? trajet.date ?? "").split(" ")[1] || "-"}</p>
          <p><strong>Conducteur :</strong> ${chauffeur.pseudo ?? chauffeur.nom ?? "—"} ${chauffeur.note ? `(${chauffeur.note}⭐)` : ""}</p>
          <p><strong>Véhicule :</strong> ${vehicule.marque ?? "?"} ${vehicule.modele ?? ""} — ${vehicule.energie ?? "?"}</p>
          <p><strong>Fumeur accepté :</strong> ${trajet.fumeur ? "Oui" : "Non"}</p>
          <p><strong>Animaux acceptés :</strong> ${trajet.animaux ? "Oui" : "Non"}</p>
          <hr/>
          <h6>Avis</h6>
          <ul>
            ${(trajet.avis ?? [])
                    .map((a) => `<li>${a.note}⭐ — ${a.commentaire}</li>`)
                    .join("") || "<em>Aucun avis</em>"}
          </ul>
        </div>
      `;

            const modal = new bootstrap.Modal(document.getElementById("modalDetail"));
            modal.show();

        } catch (e) {
            console.error(e);
            modalBody.innerHTML = "❌ Erreur lors du chargement des détails.";
        }
    }

    // 🧪 Soumission des filtres
    formFiltres?.addEventListener("submit", (e) => {
        e.preventDefault();
        const payload = {
            ecolo: document.getElementById("filtre-ecolo")?.value || "",
            prixMax: document.getElementById("filtre-prix")?.value || "",
            dureeMax: document.getElementById("filtre-duree")?.value || "",
            noteMin: document.getElementById("filtre-note")?.value || "",
        };
        chargerTrajets(payload);
    });

    // 🚀 Premier chargement avec params de la barre d'adresse
    chargerTrajets();
}

export default initCovoituragesPage;
