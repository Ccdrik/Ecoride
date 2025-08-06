import { getToken, showAndHideElementsForRoles, handle401 } from "./auth.js";

export function initAccountPage() {
    console.log("✅ initAccountPage appelé !");

    showAndHideElementsForRoles();

    const token = getToken();
    if (!token) {
        alert("Vous devez être connecté.");
        window.location.href = "/signin";
        return;
    }

    fetch("http://localhost:8000/api/me", {
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
            if (!container) return;

            container.innerHTML = `
    <h2>Bonjour <strong>${user.pseudo || user.nom}</strong> 👋</h2>
    <h2>Rôle : ${user.roles[0].replace("ROLE_", "")}</h2>
    <h2>Crédits disponibles : <strong>${user.credits}</strong></h2>
`;
        })
        .catch(err => {
            console.error("Erreur chargement utilisateur :", err);
        });
}

export default initAccountPage;
