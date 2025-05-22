import { getToken, showAndHideElementsForRoles, handle401 } from "./auth/auth.js";

export default function () {
    console.log("✅ JS créer-trajet chargé");
    showAndHideElementsForRoles();

    const form = document.getElementById("createTrajetForm");
    const errorDiv = document.getElementById("error-message");

    if (!form) {
        console.error("❌ Formulaire introuvable");
        return;
    }

    const departInput = document.getElementById("depart");
    const arriveeInput = document.getElementById("arrivee");
    const dateInput = document.getElementById("date");
    const heureInput = document.getElementById("heure");
    const placesInput = document.getElementById("places");
    const prixInput = document.getElementById("prix"); // ✅ champ prix
    const ecologiqueInput = document.getElementById("ecologique");
    const btnCreate = document.getElementById("btn-create-trajet");

    btnCreate.addEventListener("click", async () => {
        const token = getToken();
        if (!token) {
            alert("Vous devez être connecté pour créer un trajet.");
            return;
        }


        if (!departInput.value || !arriveeInput.value || !dateInput.value || !heureInput.value || !placesInput.value || !prixInput.value) {
            showError("Tous les champs doivent être remplis.");
            return;
        }

        const dateDepart = `${dateInput.value}T${heureInput.value}:00`;
        const trajet = {
            villeDepart: departInput.value,
            villeArrivee: arriveeInput.value,
            dateDepart: dateDepart,
            nbPlaces: parseInt(placesInput.value),
            prix: parseFloat(prixInput.value),
            ecologique: ecologiqueInput.checked
        };

        try {
            const res = await fetch("http://127.0.0.1:8000/api/trajets", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${token}`
                },
                body: JSON.stringify(trajet)
            });

            if (handle401(res)) return;

            if (!res.ok) {
                const errorText = await res.text();
                showError("Erreur : " + errorText);
                return;
            }

            alert("✅ Trajet créé !");
            window.location.href = "/mestrajets";

        } catch (e) {
            console.error("Erreur API :", e);
            showError("Une erreur est survenue, réessayez.");
        }
    });

    function showError(message) {
        errorDiv.textContent = message;
        errorDiv.classList.remove("d-none");
    }
}
