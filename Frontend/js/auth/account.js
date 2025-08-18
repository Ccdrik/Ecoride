import { getToken, showAndHideElementsForRoles, handle401 } from "./auth.js";
import { showLoader, hideLoader } from "../loader.js";

export function initAccountPage() {
    console.log("✅ initAccountPage appelé !");
    showAndHideElementsForRoles();

    const token = getToken();
    if (!token) {
        alert("Vous devez être connecté.");
        window.location.href = "/signin";
        return;
    }

    showLoader("Chargement des informations...");

    fetch("http://127.0.0.1:8000/api/me", {
        headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
        }
    })
        .then(res => {
            if (handle401(res)) return;
            if (!res.ok) throw new Error("Erreur API");
            return res.json();
        })
        .then(user => {
            console.log("👤 Données utilisateur :", user);
            const container = document.getElementById("user-info");
            if (container) {
                container.innerHTML = `
          <h2>Bonjour <strong>${user.pseudo || user.nom || "utilisateur"}</strong> 👋</h2>
          <h2>Rôle : ${user.roles?.[0]?.replace("ROLE_", "") || "-"}</h2>
          <h2>Crédits disponibles : <strong>${user.credits ?? "-"}</strong></h2>
        `;
            }

            // 👉 Charger la liste des voitures
            loadCars();
        })
        .catch(err => {
            console.error("Erreur chargement utilisateur :", err);
        })
        .finally(() => {
            hideLoader();
        });
}

function renderCars(list) {
    const wrap = document.getElementById("cars-container");
    if (!wrap) return;

    if (!list.length) {
        wrap.innerHTML = `<div class="text-muted p-3">Aucun véhicule pour le moment.</div>`;
        return;
    }

    wrap.innerHTML = `
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Marque</th><th>Modèle</th><th>Immat.</th><th>Énergie</th>
          <th>1ère immat.</th><th>Places</th><th>Couleur</th><th style="width:1%"></th>
        </tr>
      </thead>
      <tbody>
        ${list.map(v => `
          <tr data-id="${v.id}">
            <td>${v.marque}</td>
            <td>${v.modele}</td>
            <td>${v.immatriculation}</td>
            <td>${v.energie}</td>
            <td>${v.premiereImmat}</td>
            <td>${v.places}</td>
            <td>${v.couleur ?? "-"}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-danger js-del">Supprimer</button>
            </td>
          </tr>
        `).join("")}
      </tbody>
    </table>
  `;

    // boutons Supprimer
    wrap.querySelectorAll(".js-del").forEach(btn => {
        btn.addEventListener("click", async (e) => {
            const tr = e.target.closest("tr");
            const id = tr?.dataset.id;
            if (!id) return;

            if (!confirm("Supprimer ce véhicule ?")) return;

            showLoader("Suppression du véhicule...");

            try {
                const res = await fetch(`http://127.0.0.1:8000/api/voiture/${id}`, {
                    method: "DELETE",
                    headers: { Authorization: `Bearer ${getToken()}` }
                });
                if (res.status === 204) {
                    tr.remove();
                    if (!wrap.querySelector("tbody tr")) renderCars([]);
                } else {
                    const err = await res.json().catch(() => ({}));
                    alert(err.error || "Suppression impossible");
                }
            } catch (err) {
                console.error(err);
                alert("Erreur réseau");
            } finally {
                hideLoader();
            }
        });
    });
}

async function loadCars() {
    const token = getToken();
    const wrap = document.getElementById("cars-container");

    showLoader("Chargement des véhicules...");

    try {
        const res = await fetch("http://127.0.0.1:8000/api/mes-voitures", {
            headers: { Authorization: `Bearer ${token}` }
        });
        if (!res.ok) throw new Error("Erreur API");
        const cars = await res.json();
        renderCars(cars);
    } catch (err) {
        console.error("Erreur chargement véhicules :", err);
        if (wrap) wrap.innerHTML = `<div class="text-danger p-3">Erreur de chargement.</div>`;
    } finally {
        hideLoader();
    }
}

export default initAccountPage;
