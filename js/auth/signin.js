import { setToken, setRole, clearAuthCookies, showAndHideElementsForRoles } from "./auth.js";

export function initSigninPage() {
    const mailInput = document.getElementById("EmailInput");
    const passwordInput = document.getElementById("PasswordInput");
    const btnSignin = document.getElementById("btnSignin");
    const messageDiv = document.getElementById("signin-message");

    if (!mailInput || !passwordInput || !btnSignin || !messageDiv) {
        console.error("❌ Éléments du formulaire manquants");
        return;
    }

    const showMessage = (text, type = "danger") => {
        messageDiv.className = `alert alert-${type} text-center`;
        messageDiv.textContent = text;
        messageDiv.classList.remove("d-none");
    };

    btnSignin.addEventListener("click", async () => {
        const email = mailInput.value.trim();
        const password = passwordInput.value.trim();

        if (!email || !password) {
            showMessage("Veuillez remplir tous les champs.");
            return;
        }

        try {
            const res = await fetch("http://127.0.0.1:8000/api/signin", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email, motdepasse: password })
            });

            if (!res.ok) {
                showMessage("Connexion échouée.");
                return;
            }

            const result = await res.json();
            if (!result.token) {
                showMessage("Erreur : aucun token reçu");
                return;
            }

            setToken(result.token);

            // Appel à /api/me pour récupérer les rôles
            const meRes = await fetch("http://127.0.0.1:8000/api/me", {
                headers: { Authorization: `Bearer ${result.token}` }
            });

            const user = await meRes.json();

            const roles = user.roles || [];
            let role = "utilisateur";
            if (roles.includes("ROLE_ADMIN")) role = "admin";
            else if (roles.includes("ROLE_EMPLOYE")) role = "employe";
            else if (roles.includes("ROLE_CHAUFFEUR") && roles.includes("ROLE_PASSAGER")) role = "chauffeur"; // ou autre logique
            else if (roles.includes("ROLE_CHAUFFEUR")) role = "chauffeur";
            else if (roles.includes("ROLE_PASSAGER")) role = "passager";

            setRole(role);
            showAndHideElementsForRoles(); // ✅ met à jour les boutons dans la navbar
            showMessage("Connexion réussie !", "success");

            // Redirection SPA (sans recharger la page)
            setTimeout(() => {
                window.history.pushState({}, "", "/");
                dispatchEvent(new PopStateEvent("popstate"));
            }, 1000);

        } catch (err) {
            console.error("Erreur JS:", err);
            showMessage("Erreur de connexion.");
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("signin-form")) {
        initSigninPage();
    }
});
