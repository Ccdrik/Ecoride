// js/home-trajets.js

// ——— utils ———
const $ = (s) => document.querySelector(s);
const debounce = (fn, d = 250) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), d); }; };

async function fetchAdresseSuggestions(q) {
    if (!q || q.trim().length < 3) return [];
    const res = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(q)}&limit=5`);
    if (!res.ok) return [];
    const data = await res.json();
    return (data.features || []).map(f => ({
        label: f.properties.label,
        lat: f.geometry.coordinates[1], // [lon, lat]
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
    if (!input || !list) return;

    // vider coords dès que l’utilisateur tape
    input.addEventListener("input", () => {
        if (latInput) latInput.value = "";
        if (lngInput) lngInput.value = "";
    });

    const update = debounce(async () => {
        const q = input.value.trim();
        if (q.length < 3) {
            list.innerHTML = "";
            return;
        }
        const items = await fetchAdresseSuggestions(q);
        list.innerHTML = items.map(r => `
      <button type="button" class="list-group-item list-group-item-action" data-lat="${r.lat}" data-lng="${r.lng}">
        ${r.label}
      </button>
    `).join("");
    }, 250);

    input.addEventListener("input", update);

    // choisir une suggestion
    list.addEventListener("click", (e) => {
        const btn = e.target.closest("button[data-lat]");
        if (!btn) return;
        input.value = btn.textContent.trim();
        if (latInput) latInput.value = btn.dataset.lat;
        if (lngInput) lngInput.value = btn.dataset.lng;
        list.innerHTML = "";
    });

    // fermer si clic à l’extérieur
    document.addEventListener("click", (e) => {
        if (!e.target.closest(`#${list.id}`) && e.target !== input) list.innerHTML = "";
    });
}

// ——— point d’entrée ———
export default function initRecherchePage() {
    console.log("✅ Home: recherche + autocomplétion");

    const form = $("#form-recherche");
    const listeTrajets = $("#liste-trajets");
    const messageAucun = $("#message-aucun-resultat");

    if (!form) return;

    // activer l’autocomplétion
    mountAutocomplete({
        input: $("#depart"),
        list: $("#home_depart_suggestions"),
        latInput: $("#home_depart_lat"),
        lngInput: $("#home_depart_lng"),
    });
    mountAutocomplete({
        input: $("#arrivee"),
        list: $("#home_arrivee_suggestions"),
        latInput: $("#home_arrivee_lat"),
        lngInput: $("#home_arrivee_lng"),
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // champs
        const depart = $("#depart")?.value?.trim();
        const arrivee = $("#arrivee")?.value?.trim();
        const date = $("#date")?.value;

        const departLat = $("#home_depart_lat")?.value;
        const departLng = $("#home_depart_lng")?.value;
        const arriveeLat = $("#home_arrivee_lat")?.value;
        const arriveeLng = $("#home_arrivee_lng")?.value;

        if (!depart || !arrivee || !date) {
            alert("⚠️ Merci de remplir Départ, Arrivée et Date.");
            return;
        }

        // fallback géocodage si pas cliqué sur une suggestion
        try {
            if (!departLat || !departLng) {
                const g = await geocodeOnce(depart);
                if (g) { $("#home_depart_lat").value = g.lat; $("#home_depart_lng").value = g.lng; }
            }
            if (!arriveeLat || !arriveeLng) {
                const g2 = await geocodeOnce(arrivee);
                if (g2) { $("#home_arrivee_lat").value = g2.lat; $("#home_arrivee_lng").value = g2.lng; }
            }
        } catch (_) { /* non bloquant */ }

        try {
            // Appel API back — adapte si besoin à ta route exacte
            const response = await fetch("http://127.0.0.1:8000/api/trajets/search", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    depart,
                    arrivee,
                    date,
                    // Si ton backend veut les coords, ils sont prêts :
                    // departLat: $("#home_depart_lat").value || null,
                    // departLng: $("#home_depart_lng").value || null,
                    // arriveeLat: $("#home_arrivee_lat").value || null,
                    // arriveeLng: $("#home_arrivee_lng").value || null,
                }),
            });

            const result = await response.json();

            // reset affichage
            if (listeTrajets) listeTrajets.innerHTML = "";
            if (messageAucun) messageAucun.classList.add("d-none");

            if (!response.ok || !Array.isArray(result) || result.length === 0) {
                if (messageAucun) messageAucun.classList.remove("d-none");
                return;
            }

            // afficher en cards (adapté aux champs renvoyés par ton API)
            result.forEach((t) => {
                const card = document.createElement("div");
                card.className = "col-md-4";
                // essaie d’abord tes noms EcoRide (villeDepart, villeArrivee, dateDepart, nbPlaces)
                const villeDepart = t.villeDepart ?? t.depart ?? "-";
                const villeArrivee = t.villeArrivee ?? t.arrivee ?? "-";
                const dateTxt = t.dateDepart ?? t.date ?? "-";
                const heureTxt = t.heureDepart ?? t.heure ?? "-";
                const places = t.nbPlaces ?? t.places ?? "-";
                const prix = t.prix ?? "-";
                const id = t.id;

                card.innerHTML = `
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title">${villeDepart} → ${villeArrivee}</h5>
              <p class="card-text">
                <strong>Date :</strong> ${dateTxt}<br>
                <strong>Heure :</strong> ${heureTxt}<br>
                <strong>Places :</strong> ${places}<br>
                <strong>Prix :</strong> ${prix} €
              </p>
              <a href="/detail?id=${id}" class="btn btn-success" data-link>
                Réserver
              </a>
            </div>
          </div>
        `;
                listeTrajets.appendChild(card);
            });
        } catch (err) {
            console.error("❌ Erreur recherche :", err);
            alert("Erreur serveur ou connexion.");
        }
    });
}
