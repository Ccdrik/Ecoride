import { getToken, handle401 } from "./auth/auth.js";

export function initAdminCreateUserPage() {
    const form = document.getElementById("admin-create-user-form");
    const message = document.getElementById("create-user-message");

    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const userData = {
            nom: document.getElementById("nom").value.trim(),
            prenom: document.getElementById("prenom").value.trim(),
            email: document.getElementById("email").value.trim(),
            pseudo: document.getElementById("pseudo").value.trim(),
            motdepasse: document.getElementById("motdepasse").value,
            roles: document.getElementById("role").value.split(" ")
        };

        console.log("Données envoyées :", userData);

        try {
            const res = await fetch("http://127.0.0.1:8000/api/signup", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${getToken()}`
                },
                body: JSON.stringify(userData)
            });

            const raw = await res.text();
            let result;

            try {
                result = JSON.parse(raw);
            } catch (e) {
                result = { message: raw };
            }

            console.log("Réponse API :", result);

            if (handle401(res)) return;

            if (!res.ok) {
                message.innerHTML = `<div class="alert alert-danger">${result.message ?? "Erreur lors de la création du compte."}</div>`;
                return;
            }

            message.innerHTML = `<div class="alert alert-success">✅ Compte créé avec succès !</div>`;
            form.reset();

        } catch (err) {
            console.error(err);
            message.innerHTML = `<div class="alert alert-danger">❌ Erreur technique</div>`;
        }
    });
}

export default initAdminCreateUserPage;
