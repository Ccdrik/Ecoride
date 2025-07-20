import { setToken, setRole } from "./auth.js";


export function initSignupPage() {
    const form = document.getElementById("formulaire-inscription");
    const messageDiv = document.getElementById("signup-message");

    if (!form || !messageDiv) {
        console.error(" Formulaire ou message non trouvé");
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
        const role = document.getElementById("RoleSelect").value;

        if (!nom || !prenom || !pseudo || !email || !password || !confirm || !role) {
            return showMessage("Tous les champs sont obligatoires");
        }

        if (password !== confirm) {
            return showMessage("Les mots de passe ne correspondent pas");
        }

        const roles = role === "passager,chauffeur"
            ? ["ROLE_PASSAGER", "ROLE_CHAUFFEUR"]
            : [`ROLE_${role.toUpperCase()}`];

        const payload = {
            nom,
            prenom,
            pseudo,
            email,
            motdepasse: password,
            confirmationpassword: confirm,
            roles
        };

        try {
            const res = await fetch("http://127.0.0.1:8000/api/signup", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const raw = await res.text(); // On prend le texte brut pour déboguer si besoin
            console.log(" Réponse brute signup :", raw);

            const result = JSON.parse(raw);

            if (res.ok && result.token) {
                setToken(result.token);
                setRole(role.includes("chauffeur") ? "chauffeur" : "passager");
                showMessage("Inscription réussie, redirection...", "success");

                setTimeout(() => {
                    window.location.href = "/";
                }, 1000);
            } else {
                showMessage(result.error || "Erreur lors de l'inscription");
            }

        } catch (e) {
            console.error(" Erreur réseau ou serveur :", e);
            showMessage("Erreur serveur, veuillez réessayer plus tard.");
        }
    });

    function showMessage(text, type = "danger") {
        messageDiv.className = `alert alert-${type} text-center`;
        messageDiv.textContent = text;
        messageDiv.classList.remove("d-none");
    }
}

// Appelle immédiate
initSignupPage();
