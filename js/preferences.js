import { getToken } from './auth/auth.js';

export function initPreferencesPage() {
    console.log("✅ initPreferencesPage chargé !");

    const form = document.getElementById("preferences-form");

    if (!form) {
        console.error("❌ Formulaire non trouvé");
        return;
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const data = {
            fumeur: parseInt(document.getElementById("fumeur").value),
            animaux: parseInt(document.getElementById("animaux").value),
            musique: parseInt(document.getElementById("musique").value),
            autres: document.getElementById("autres").value
        };

        try {
            const res = await fetch("http://localhost:8000/api/preferences", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${getToken()}`
                },
                body: JSON.stringify(data)
            });

            const text = await res.text();
            let result;

            try {
                result = JSON.parse(text);
            } catch {
                result = { error: text };
            }

            if (res.ok) {
                alert("✅ Préférences enregistrées !");
            } else {
                alert("❌ " + (result.error || "Erreur inconnue"));
            }

        } catch (err) {
            console.error("❌ Erreur envoi préférences :", err);
            alert("❌ Impossible d’enregistrer les préférences.");
        }
    });
}

export default initPreferencesPage;
