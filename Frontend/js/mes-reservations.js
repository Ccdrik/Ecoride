import { getToken, handle401 } from './auth/auth.js';

export default function initReservationsPage() {
    const container = document.getElementById("tableau-reservations");
    if (!container) return;

    fetch("http://127.0.0.1:8000/api/mes-reservations", {
        headers: {
            Authorization: `Bearer ${getToken()}`
        }
    })
        .then(res => {
            if (handle401(res)) return;
            return res.json();
        })
        .then(data => {
            container.innerHTML = "";

            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = `<tr><td colspan="4">Aucune réservation trouvée.</td></tr>`;
                return;
            }

            data.forEach(reservation => {
                const trajet = reservation.trajet;
                const row = document.createElement("tr");

                const date = new Date(trajet.dateDepart).toLocaleDateString("fr-FR");

                row.innerHTML = `
                <td>${trajet.villeDepart}</td>
                <td>${trajet.villeArrivee}</td>
                <td>${date}</td>
                <td>${reservation.nbPlacesReservees}</td>
            `;
                container.appendChild(row);
            });
        })
        .catch(err => {
            console.error("Erreur de chargement :", err);
            container.innerHTML = `<tr><td colspan="4">Erreur lors du chargement</td></tr>`;
        });
}
