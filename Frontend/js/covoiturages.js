import { getToken, handle401 } from "./auth/auth.js";

export function initCovoituragesPage() {
    const container = document.getElementById("resultats-trajets");
    const formFiltres = document.getElementById("form-filtres");
    const modalBody = document.getElementById("modal-detail-body");

    const API_URL = "http://127.0.0.1:8000/api";

    // 🔎 Params de la barre d'adresse (venant de la home)
    const url = new URL(window.location.href);
    const baseParams = {
        depart: url.searchParams.get("depart") || "",
        arrivee: url.searchParams.get("arrivee") || "",
        date: url.searchParams.get("date") || ""
    };

    // 🧰 Construit l'URL /api/trajets avec base + filtres
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

    // 🗂️ Rendu d'une ligne (inclut data-bs-* pour ouvrir la modale sans JS)
    function renderRow(trajet) {
        const villeDepart = trajet.villeDepart ?? trajet.depart ?? "-";
        const villeArrivee = trajet.villeArrivee ?? trajet.arrivee ?? "-";
        const dateHeure = trajet.dateDepart ?? trajet.date ?? "";
        const [date = "-", heure = "-"] = String(dateHeure).split(" ");
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
          <button
            class="btn btn-info btn-sm btn-detail"
            data-id="${trajet.id}"
            data-bs-toggle="modal"
            data-bs-target="#modalDetail"
          >Détail</button>
        </td>
      </tr>
    `;
    }

    // 📥 Charge et affiche la liste
    async function chargerTrajets(extra = {}) {
        container.innerHTML = `<tr><td colspan="9">Chargement...</td></tr>`;
        try {
            const res = await fetch(buildApiUrl(extra), {
                headers: { Authorization: `Bearer ${getToken()}` }, // ok si route publique aussi
                credentials: "include",
            });

            if (handle401(res)) return;
            if (!res.ok) {
                const preview = (await res.text()).slice(0, 200);
                console.warn("Erreur liste trajets:", res.status, preview);
                container.innerHTML = `<tr><td colspan="9" class="text-danger">Erreur ${res.status} lors du chargement</td></tr>`;
                return;
            }

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

            // ℹ️ Détail (le data-bs-* ouvre la modale; ici on ne fait que remplir le contenu)
            document.querySelectorAll(".btn-detail").forEach((btn) => {
                btn.addEventListener("click", () => chargerDetail(btn));
            });

        } catch (err) {
            console.error("Erreur fetch liste:", err);
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

    // 🔎 Détail (remplissage de la modale)
    async function chargerDetail(btn) {
        const trajetId = btn.dataset.id;
        if (!trajetId) return;
        if (modalBody) modalBody.textContent = "Chargement...";

        try {
            const res = await fetch(`${API_URL}/trajets/${trajetId}`, {
                headers: { Authorization: `Bearer ${getToken()}` },
                credentials: "include",
            });

            if (handle401(res)) return;

            // ⚠️ Garde: ne pas parser JSON si pas OK (ex: 403)
            if (!res.ok) {
                const preview = (await res.text()).slice(0, 200);
                console.warn("Détail non accessible:", res.status, preview);
                if (modalBody) {
                    modalBody.innerHTML = `❌ Accès refusé au détail (HTTP ${res.status}).`;
                }
                alert("Erreur lors du chargement des détails");
                return;
            }

            const t = await res.json();
            const chauffeur = t.chauffeur ?? {};
            const vehicule = t.vehicule ?? {};
            const dateHeure = t.dateDepart ?? t.date ?? "";
            const [date = "-", heure = "-"] = String(dateHeure).split(" ");

            if (modalBody) {
                modalBody.innerHTML = `
          <div>
            <h5>${t.villeDepart ?? t.depart ?? "-"} → ${t.villeArrivee ?? t.arrivee ?? "-"}</h5>
            <p><strong>Date :</strong> ${date}</p>
            <p><strong>Heure départ :</strong> ${heure}</p>
            <p><strong>Conducteur :</strong> ${chauffeur.pseudo ?? chauffeur.nom ?? "—"} ${chauffeur.note ? `(${chauffeur.note}⭐)` : ""}</p>
            <p><strong>Véhicule :</strong> ${vehicule.marque ?? "?"} ${vehicule.modele ?? ""} — ${vehicule.energie ?? "?"}</p>
            <p><strong>Fumeur accepté :</strong> ${t.fumeur ? "Oui" : "Non"}</p>
            <p><strong>Animaux acceptés :</strong> ${t.animaux ? "Oui" : "Non"}</p>
            <hr/>
            <h6>Avis</h6>
            <ul>
              ${(t.avis ?? []).map(a => `<li>${a.note}⭐ — ${a.commentaire}</li>`).join("") || "<em>Aucun avis</em>"}
            </ul>
          </div>
        `;
            }
        } catch (e) {
            console.error(e);
            if (modalBody) modalBody.textContent = "❌ Erreur lors du chargement des détails.";
        }
    }

    // 🧪 Filtres → recharge la liste
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

    // 🚀 Premier chargement (avec params URL)
    chargerTrajets();
}

export default initCovoituragesPage;
