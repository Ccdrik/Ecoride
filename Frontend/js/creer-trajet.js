import { getToken, showAndHideElementsForRoles, handle401 } from "./auth/auth.js";

export default function () {
    console.log("✅ JS créer-trajet chargé");
    showAndHideElementsForRoles();

    const form = document.getElementById("createTrajetForm");
    const errorDiv = document.getElementById("error-message");
    const btnCreate = document.getElementById("btn-create-trajet");

    if (!form || !btnCreate) {
        console.error("❌ Formulaire ou bouton introuvable");
        return;
    }

    // Champs visibles
    const departInput = document.getElementById("depart");
    const arriveeInput = document.getElementById("arrivee");
    const dateInput = document.getElementById("date");
    const heureInput = document.getElementById("heure");
    const placesInput = document.getElementById("places");
    const prixInput = document.getElementById("prix");
    const ecologiqueInput = document.getElementById("ecologique");

    // Champs cachés pour coords (à ajouter dans le HTML)
    const departLatInput = document.getElementById("depart_lat");
    const departLngInput = document.getElementById("depart_lng");
    const arriveeLatInput = document.getElementById("arrivee_lat");
    const arriveeLngInput = document.getElementById("arrivee_lng");

    // Conteneurs suggestions (à ajouter dans le HTML)
    const departList = document.getElementById("depart_suggestions");
    const arriveeList = document.getElementById("arrivee_suggestions");

    // ——————————————————————
    // Utils + API adresse
    // ——————————————————————
    const showError = (message) => {
        if (!errorDiv) return;
        errorDiv.textContent = message;
        errorDiv.classList.remove("d-none");
    };
    const clearError = () => {
        if (!errorDiv) return;
        errorDiv.textContent = "";
        errorDiv.classList.add("d-none");
    };
    const debounce = (fn, delay = 250) => {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
    };
    async function fetchAdresseSuggestions(q) {
        if (!q || q.trim().length < 3) return [];
        const res = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(q)}&limit=5`);
        if (!res.ok) return [];
        const data = await res.json();
        return (data.features || []).map(f => ({
            label: f.properties.label,
            lat: f.geometry.coordinates[1], // ⚠️ API: [lon, lat]
            lng: f.geometry.coordinates[0],
        }));
    }
    async function geocodeOnce(label) {
        if (!label || label.trim().length < 3) return null;
        const res = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(label)}&limit=1`);
        if (!res.ok) return null;
        const data = await res.json();
        const f = (data.features || [])[0];
        if (!f) return null;
        return {
            label: f.properties.label,
            lat: f.geometry.coordinates[1],
            lng: f.geometry.coordinates[0],
        };
    }
    function mountAutocomplete({ input, list, latInput, lngInput }) {
        if (!input || !list || !latInput || !lngInput) return;

        // On vide les coords si le texte change
        input.addEventListener("input", () => { latInput.value = ""; lngInput.value = ""; });

        const update = debounce(async () => {
            const q = input.value.trim();
            if (q.length < 3) { list.innerHTML = ""; return; }
            const items = await fetchAdresseSuggestions(q);
            list.innerHTML = items.map(r => `
        <button type="button" class="list-group-item list-group-item-action"
                data-lat="${r.lat}" data-lng="${r.lng}">
          ${r.label}
        </button>
      `).join("");
        }, 250);

        input.addEventListener("input", update);
        list.addEventListener("click", (e) => {
            const btn = e.target.closest("button[data-lat]");
            if (!btn) return;
            input.value = btn.textContent.trim();
            latInput.value = btn.dataset.lat;
            lngInput.value = btn.dataset.lng;
            list.innerHTML = "";
        });
        document.addEventListener("click", (e) => {
            if (!e.target.closest(`#${list.id}`) && e.target !== input) list.innerHTML = "";
        });
    }

    // ——————————————————————
    // Monter les autocomplétions
    // ——————————————————————
    mountAutocomplete({
        input: departInput, list: departList, latInput: departLatInput, lngInput: departLngInput
    });
    mountAutocomplete({
        input: arriveeInput, list: arriveeList, latInput: arriveeLatInput, lngInput: arriveeLngInput
    });

    // ——————————————————————
    // Création du trajet
    // ——————————————————————
    btnCreate.addEventListener("click", async (e) => {
        e.preventDefault();
        clearError();

        const token = getToken();
        if (!token) {
            alert("Vous devez être connecté pour créer un trajet.");
            return;
        }

        if (!departInput.value || !arriveeInput.value || !dateInput.value || !heureInput.value || !placesInput.value || !prixInput.value) {
            showError("Tous les champs doivent être remplis.");
            return;
        }

        // Si l’utilisateur n’a pas cliqué une suggestion → géocode au submit
        try {
            if (!departLatInput.value || !departLngInput.value) {
                const g = await geocodeOnce(departInput.value);
                if (g) { departLatInput.value = g.lat; departLngInput.value = g.lng; }
            }
            if (!arriveeLatInput.value || !arriveeLngInput.value) {
                const g2 = await geocodeOnce(arriveeInput.value);
                if (g2) { arriveeLatInput.value = g2.lat; arriveeLngInput.value = g2.lng; }
            }
        } catch (_) { /* non bloquant */ }

        const dateDepart = `${dateInput.value}T${heureInput.value}:00`;

        // ⚠️ Correspond exactement à ton Trajet.php actuel (pas de lat/lng)
        const trajet = {
            villeDepart: departInput.value,
            villeArrivee: arriveeInput.value,
            dateDepart: dateDepart,
            nbPlaces: parseInt(placesInput.value, 10),
            prix: parseFloat(prixInput.value),
            ecologique: !!ecologiqueInput?.checked
        };

        // 💡 Si plus tard tu ajoutes les colonnes côté backend :
        // trajet.departLat = departLatInput.value || null;
        // trajet.departLng = departLngInput.value || null;
        // trajet.arriveeLat = arriveeLatInput.value || null;
        // trajet.arriveeLng = arriveeLngInput.value || null;

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
            // redirection SPA — adapte si nécessaire
            window.location.href = "/mestrajets";
        } catch (e) {
            console.error("Erreur API :", e);
            showError("Une erreur est survenue, réessayez.");
        }
    });
}
