import { setToken, setRole } from "./auth.js";
import { showLoader, hideLoader } from "../loader.js"; // ✅ loader global

export function initSignupPage() {
    const form = document.getElementById("formulaire-inscription");
    const messageDiv = document.getElementById("signup-message");
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;

    if (!form || !messageDiv) {
        console.error("Formulaire ou message non trouvé");
        return;
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const nom = document.getElementById("NomInput").value.trim();
        const prenom = document.getElementById("PrenomInput").value.trim();
        const pseudo = document.getElementById("PseudoInput").value.trim();
        const email = document.getElementById("EmailInput").value.trim();
        const password = document.getElementById("PasswordInput").value;
        const confirm = document.getElementById("ValidatePasswordInput").value;
        const roleSel = document.getElementById("RoleSelect").value; // ex: "passager", "chauffeur", "passager,chauffeur"

        // ✅ validations front
        if (!nom || !prenom || !pseudo || !email || !password || !confirm || !roleSel) {
            return showMessage("Tous les champs sont obligatoires");
        }
        if (password !== confirm) {
            return showMessage("Les mots de passe ne correspondent pas");
        }

        // ✅ map des rôles vers le format attendu par l’API
        let roles = [];
        if (roleSel === "passager,chauffeur") {
            roles = ["ROLE_PASSAGER", "ROLE_CHAUFFEUR"];
        } else {
            roles = [`ROLE_${roleSel.toUpperCase()}`]; // "passager" -> "ROLE_PASSAGER"
        }

        // ✅ payload EXACT attendu par le backend
        const payload = { nom, prenom, pseudo, email, password, roles };
        console.log("payload signup =>", payload);

        // Loader + verrou
        showLoader("Inscription en cours...");
        if (submitBtn) submitBtn.disabled = true;

        try {
            const res = await fetch("http://127.0.0.1:8000/api/signup", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            // on lit en texte d'abord pour déboguer facilement
            const raw = await res.text();
            console.log("Réponse brute signup:", raw);

            let data = {};
            try { data = raw ? JSON.parse(raw) : {}; } catch { /* JSON invalide */ }

            if (!res.ok) {
                // affiche le message renvoyé par le backend s’il existe
                return showMessage(data.error || data.message || "Erreur lors de l'inscription");
            }

            // Deux cas possibles côté backend :
            // 1) il renvoie un token directement
            // 2) il renvoie un message de succès sans token (et tu fais ensuite un /signin)
            if (data.token) {
                setToken(data.token);
                setRole(roleSel.includes("chauffeur") ? "chauffeur" : "passager");
                showMessage("Inscription réussie, redirection...", "success");
                setTimeout(() => (window.location.href = "/"), 900);
            } else {
                // pas de token -> on affiche succès et on invite à se connecter
                showMessage("Compte créé, vous pouvez vous connecter.", "success");
                setTimeout(() => (window.location.href = "/signin"), 1200);
            }
        } catch (err) {
            console.error("Erreur réseau ou serveur :", err);
            showMessage("Erreur serveur, veuillez réessayer plus tard.");
        } finally {
            hideLoader();
            if (submitBtn) submitBtn.disabled = false;
        }
    });

    function showMessage(text, type = "danger") {
        messageDiv.className = `alert alert-${type} text-center`;
        messageDiv.textContent = text;
        messageDiv.classList.remove("d-none");
    }
}

// Appel immédiat
initSignupPage();
