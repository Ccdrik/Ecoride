import { getToken } from "./auth/auth.js";

export default async function () {
    const container = document.getElementById("tableau-trajets");
    if (!container) return;
    const token = getToken();
    try {

        const res = await fetch("http://127.0.0.1:8000/api/mes-trajets", {
            headers: { Authorization: `Bearer ${token}` }
        });

        if (!res.ok) throw new Error("Erreur récupération trajets");

        const trajets = await res.json();
        container.innerHTML = "";

        trajets.forEach(t => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${t.depart}</td>
                <td>${t.arrivee}</td>
                <td>${t.date_depart}</td>
                <td>${t.heure_depart}</td>
                <td>${t.nb_places}</td>
                <td>${t.prix ?? '-'}</td>
                <td>🚗</td>
                <td>
                    <button class="btn btn-primary btn-sm btn-start" data-id="${t.id}"> Démarrer</button>
                    <button class="btn btn-success btn-sm btn-end" data-id="${t.id}"> Terminer</button>
                    <button class="btn btn-danger btn-sm btn-delete" data-id="${t.id}"> Supprimer</button>
                </td>
            `;
            container.appendChild(row);
        });

        document.querySelectorAll(".btn-end").forEach(btn => {
            btn.addEventListener("click", async () => {
                const id = btn.dataset.id;
                const res = await fetch(`http://127.0.0.1:8000/api/trajets/${id}/finish`, {
                    method: "PATCH",
                    headers: { Authorization: `Bearer ${token}` }
                });

                if (res.ok) {
                    btn.closest("tr").remove();
                }
            });
        });

    } catch (err) {
        console.error(err);
        container.innerHTML = `<tr><td colspan="8">Erreur de chargement</td></tr>`;
    }

    document.querySelectorAll(".btn-delete").forEach(btn => {
        btn.addEventListener("click", async () => {
            const id = btn.dataset.id;
            const confirmation = confirm("Voulez-vous vraiment supprimer ce trajet ?");

            if (!confirmation) return;

            try {
                const res = await fetch(`http://127.0.0.1:8000/api/trajets/${id}`, {
                    method: "DELETE",
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                });

                if (res.ok) {
                    btn.closest("tr").remove();
                } else {
                    alert("Erreur lors de la suppression.");
                }
            } catch (e) {
                console.error("Erreur de suppression :", e);
                alert("Erreur lors de la suppression.");
            }
        });
    });
}
