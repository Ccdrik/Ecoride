import { getToken } from "./auth/auth.js";

export function initAdministrateurPage() {
    const table = document.getElementById("admin-users-table");
    if (!table) return;

    fetch("http://127.0.0.1:8000/api/admin/users", {
        headers: { Authorization: `Bearer ${getToken()}` }
    })
        .then(res => {
            if (!res.ok) throw new Error("Accès refusé ou erreur serveur");
            return res.json();
        })
        .then(users => {
            table.innerHTML = "";
            users.forEach(user => {
                const row = document.createElement("tr");

                // Créer le bouton d'action
                const actionButton = document.createElement("button");
                actionButton.className = "btn btn-sm " + (user.actif ? "btn-danger" : "btn-success");
                actionButton.textContent = user.actif ? "Bloquer" : "Activer";
                actionButton.addEventListener("click", () => toggleUserStatus(user.id));

                row.innerHTML = `
                    <td>${user.pseudo}</td>
                    <td>${user.email}</td>
                    <td>${user.roles.join(', ')}</td>
                    <td id="statut-${user.id}">${user.actif ? "✅ Actif" : "❌ Inactif"}</td>
                    <td id="action-${user.id}"></td>
                `;
                table.appendChild(row);

                // Ajouter le bouton dans la cellule Action
                document.getElementById(`action-${user.id}`).appendChild(actionButton);
            });
        })
        .catch(err => {
            console.error("Erreur admin:", err);
            table.innerHTML = `<tr><td colspan="5" class="text-danger text-center">Erreur lors du chargement</td></tr>`;
        });
}

// ✅ Fonction qui appelle l’API pour basculer actif/inactif
function toggleUserStatus(userId) {
    const token = getToken();
    fetch(`http://127.0.0.1:8000/api/admin/users/${userId}/toggle`, {
        method: "PUT",
        headers: {
            Authorization: `Bearer ${token}`
        }
    })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert("Erreur : " + data.error);
                return;
            }

            // Met à jour la ligne visuellement
            const statutCell = document.getElementById(`statut-${userId}`);
            const actionCell = document.getElementById(`action-${userId}`);
            const isActive = data.actif;

            statutCell.textContent = isActive ? "✅ Actif" : "❌ Inactif";

            const btn = document.createElement("button");
            btn.className = "btn btn-sm " + (isActive ? "btn-danger" : "btn-success");
            btn.textContent = isActive ? "Bloquer" : "Activer";
            btn.addEventListener("click", () => toggleUserStatus(userId));

            actionCell.innerHTML = "";
            actionCell.appendChild(btn);
        })
        .catch(err => {
            console.error("Erreur toggle utilisateur:", err);
            alert("Erreur lors du changement de statut.");
        });
}
