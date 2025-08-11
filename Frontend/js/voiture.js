import { getToken } from "./auth/auth.js";

function show(el, text, type = "danger") {
    el.className = `alert alert-${type}`;
    el.textContent = text;
    el.classList.remove("d-none");
}

export default function initVoiturePage() {
    const form = document.getElementById("voiture-form");
    if (!form) return;

    let msg = document.getElementById("car-msg");
    if (!msg) {
        msg = document.createElement("div");
        msg.id = "car-msg";
        msg.className = "alert d-none";
        form.prepend(msg);
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    let sending = false;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (sending) return;
        sending = true;
        submitBtn?.setAttribute("disabled", "disabled");

        const token = typeof getToken === "function" ? getToken() : null;
        if (!token) { show(msg, "Vous devez être connecté."); sending = false; submitBtn?.removeAttribute("disabled"); return; }

        const marque = document.getElementById("marque")?.value.trim();
        const modele = document.getElementById("modele")?.value.trim();
        const plaque = document.getElementById("plaque")?.value.trim();
        const couleur = document.getElementById("couleur")?.value.trim() || null;
        const energie = document.getElementById("energie")?.value;
        const premiereImmat = document.getElementById("premiereImmat")?.value; // YYYY-MM-DD
        const nbPlaces = Number(document.getElementById("nbPlaces")?.value);

        if (!marque || !modele || !plaque || !energie || !premiereImmat || !nbPlaces) {
            show(msg, "Tous les champs requis doivent être remplis."); sending = false; submitBtn?.removeAttribute("disabled"); return;
        }
        if (!/^\d{4}-\d{2}-\d{2}$/.test(premiereImmat)) {
            show(msg, "Format de date invalide (attendu : YYYY-MM-DD)."); sending = false; submitBtn?.removeAttribute("disabled"); return;
        }
        if (Number.isNaN(nbPlaces) || nbPlaces <= 0) {
            show(msg, "Nombre de places invalide."); sending = false; submitBtn?.removeAttribute("disabled"); return;
        }

        const payload = {
            marque,
            modele,
            immatriculation: plaque,
            couleur,                // nullable côté entité
            energie,
            premiereImmat,          // le contrôleur fera new \DateTime()
            places: nbPlaces
        };

        try {
            const res = await fetch("http://127.0.0.1:8000/api/voiture", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                show(msg, data.error || data.message || "Erreur lors de l'enregistrement");
            } else {
                show(msg, "Véhicule enregistré ✅", "success");
                // console.log("Créé:", data);
            }
        } catch (err) {
            console.error(err);
            show(msg, "Erreur réseau/serveur.");
        } finally {
            sending = false;
            submitBtn?.removeAttribute("disabled");
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("voiture-form")) {
        initVoiturePage();
    }
});
