import { getToken } from "./auth/auth.js";

export function initVoiturePage() {
    const form = document.querySelector("form");

    if (!form) {
        console.error("❌ Formulaire voiture non trouvé");
        return;
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const data = {
            marque: document.getElementById("marque").value,
            modele: document.getElementById("modele").value,
            couleur: document.getElementById("couleur").value,
            immatriculation: document.getElementById("immatriculation").value,
            energie: document.getElementById("energie").value,
            places: parseInt(document.getElementById("places").value),
        };

        try {
            const res = await fetch("http://localhost:8000/api/voiture", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${getToken()}`,
                },
                body: JSON.stringify(data),
            });

            if (res.ok) {
                alert("✅ Véhicule ajouté !");
                form.reset();
            } else {
                const err = await res.json();
                alert("❌ Erreur : " + (err.error || "Erreur serveur"));
                console.error(err);
            }
        } catch (err) {
            console.error("❌ Erreur JS :", err);
            alert("Erreur lors de la requête");
        }
    });
}

// Export SPA
export default initVoiturePage;
