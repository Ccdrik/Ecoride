import { setToken, setRole, clearAuthCookies, showAndHideElementsForRoles } from "./auth.js";
import { showLoader, hideLoader } from "../loader.js"; //  loader global

export function initSigninPage() {
    const form = document.getElementById("signin-form"); //  on capte le submit du formulaire
    const mailInput = document.getElementById("EmailInput");
    const passwordInp = document.getElementById("PasswordInput");
    const btnSignin = document.getElementById("btnSignin");
    const messageDiv = document.getElementById("signin-message");

    if (!mailInput || !passwordInp || !btnSignin || !messageDiv) {
        console.error("❌ Éléments du formulaire manquants");
        return;
    }

    const showMessage = (text, type = "danger") => {
        messageDiv.className = `alert alert-${type} text-center`;
        messageDiv.textContent = text;
        messageDiv.classList.remove("d-none");
    };

    const doSignin = async () => {
        const email = mailInput.value.trim();
        const password = passwordInp.value; // 👈 pas de trim sur mdp

        if (!email || !password) {
            showMessage("Veuillez remplir tous les champs.");
            return;
        }

        // ✅ loader + désactivation bouton
        showLoader("Connexion en cours...");
        btnSignin.disabled = true;

        try {
            const res = await fetch("http://127.0.0.1:8000/api/signin", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email, password }) // ✅ clé correcte (pas motdepasse)
            });

            const raw = await res.text();
            let result = {};
            try { result = raw ? JSON.parse(raw) : {}; } catch { /* ignore */ }

            if (!res.ok) {
                showMessage(result.error || result.message || "Connexion échouée.");
                return;
            }

            if (!result.token) {
                showMessage("Erreur : aucun token reçu.");
                return;
            }

            setToken(result.token);

            // Récupère les rôles
            const meRes = await fetch("http://127.0.0.1:8000/api/me", {
                headers: { Authorization: `Bearer ${result.token}` }
            });
            const user = await meRes.json();

            const roles = user.roles || [];
            let role = "utilisateur";
            if (roles.includes("ROLE_ADMIN")) role = "admin";
            else if (roles.includes("ROLE_EMPLOYEE") || roles.includes("ROLE_EMPLOYE")) role = "employe";
            else if (roles.includes("ROLE_CHAUFFEUR") && roles.includes("ROLE_PASSAGER")) role = "chauffeur";
            else if (roles.includes("ROLE_CHAUFFEUR")) role = "chauffeur";
            else if (roles.includes("ROLE_PASSAGER")) role = "passager";

            setRole(role);
            showAndHideElementsForRoles();
            showMessage("Connexion réussie !", "success");

            // Redirection SPA (sans reload)
            setTimeout(() => {
                window.history.pushState({}, "", "/");
                dispatchEvent(new PopStateEvent("popstate"));
            }, 900);

        } catch (err) {
            console.error("Erreur JS:", err);
            showMessage("Erreur réseau ou serveur.");
        } finally {
            // ✅ on remet propre quoi qu’il arrive
            hideLoader();
            btnSignin.disabled = false;
        }
    };

    // ✅ empêchons le submit natif du formulaire
    if (form) {
        form.addEventListener("submit", (e) => { e.preventDefault(); doSignin(); });
    }
    // ✅ et on garde aussi le clic bouton
    btnSignin.addEventListener("click", (e) => { e.preventDefault(); doSignin(); });
}

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("signin-form")) {
        initSigninPage();
    }
});
