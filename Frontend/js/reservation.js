import { getToken } from './auth/auth.js';

export function initReservationPage() {
    console.log("✅ Page réservation chargée");

    const bouton = document.getElementById("btn-reserver");
    const urlParams = new URLSearchParams(window.location.search);
    const trajetId = urlParams.get("id");

    if (!bouton || !trajetId) return;

    bouton.addEventListener("click", async () => {
        const token = getToken();
        if (!token) {
            alert("❌ Vous devez être connecté pour réserver.");
            window.location.href = "/signin"; // SPA route, pas page complète
            return;
        }

        const confirmation = confirm("Souhaitez-vous utiliser vos crédits pour réserver ce trajet ?");
        if (!confirmation) return;

        try {
            const response = await fetch("http://127.0.0.1:8000/api/reservations", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${token}`
                },
                body: JSON.stringify({
                    trajetId: parseInt(trajetId), // Assure-toi que ton backend accepte bien ce champ
                    placesReservees: 1
                })
            });

            const result = await response.json();

            if (!response.ok) {
                alert(result.error || "Erreur lors de la réservation.");
                return;
            }

            alert("✅ Réservation confirmée !");
            window.location.href = "/mesreservations"; // redirige vers SPA
        } catch (error) {
            console.error("Erreur JS :", error);
            alert("Erreur de connexion ou serveur.");
        }
    });
}


export default initReservationPage;