import Route from "./Route.js";
import { allRoutes, websiteName } from "./allRoutes.js";
import { showAndHideElementsForRoles } from "../js/auth/auth.js";

if (window.location.pathname === "/index.html") {
    history.replaceState({}, "", "/");
}

const route404 = new Route("/404", "Page introuvable", "/pages/404.html");

const getRouteByUrl = (url) => allRoutes.find(route => route.url === url) || route404;

const LoadContentPage = async () => {
    const rawPath = window.location.pathname;
    const cleanedPath = window.location.pathname.replace("/Frontend", "") || "/";

    console.log("➡️ URL demandée :", rawPath);
    console.log("🧼 Chemin nettoyé :", cleanedPath);

    const actualRoute = getRouteByUrl(cleanedPath);
    console.log("🧭 Route trouvée :", actualRoute);

    try {
        const res = await fetch(actualRoute.pathHtml);
        if (!res.ok) throw new Error("Page HTML introuvable");

        const html = await res.text();
        const container = document.getElementById("main-page");
        if (!container) throw new Error("Conteneur #main-page introuvable");

        container.innerHTML = html;
        document.title = `${actualRoute.title} - ${websiteName}`;

        showAndHideElementsForRoles();

        if (actualRoute.pathJS) {
            try {
                // Support 1 ou plusieurs scripts par route
                const jsFiles = Array.isArray(actualRoute.pathJS) ? actualRoute.pathJS : [actualRoute.pathJS];

                for (const jsFile of jsFiles) {
                    const module = await import(`../${jsFile}`);
                    console.log("📦 Module chargé :", jsFile);

                    // Priorité aux points d'entrée nommés si présents
                    if (typeof module.initSigninPage === "function") module.initSigninPage();
                    else if (typeof module.initSignupPage === "function") module.initSignupPage();
                    else if (typeof module.initAccountPage === "function") module.initAccountPage();
                    else if (typeof module.initHomePage === "function") module.initHomePage();
                    else if (typeof module.initCovoituragesPage === "function") module.initCovoituragesPage();
                    else if (typeof module.initVoiturePage === "function") module.initVoiturePage();
                    else if (typeof module.initRecherchePage === "function") module.initRecherchePage(); // 👈 utile si ton module exporte cette fonction
                    else if (typeof module.default === "function") module.default(); // fallback sur export default
                    else console.warn("⚠️ Aucun point d’entrée JS trouvé dans :", jsFile);
                }
            } catch (err) {
                console.error("❌ Erreur import JS :", err);
            }
        }

    } catch (err) {
        console.error("❌ Erreur de chargement :", err);
        document.getElementById("main-page").innerHTML = `
      <div class="text-danger text-center py-5">
        <h1>Erreur 404</h1>
        <p>Page introuvable ou inaccessible.</p>
      </div>`;
    }
};

// Navigation SPA
document.addEventListener("click", (e) => {
    const link = e.target.closest("a[data-link]");
    if (link) {
        e.preventDefault();
        window.history.pushState({}, "", link.getAttribute("href"));
        LoadContentPage();
    }
});

window.onpopstate = LoadContentPage;

// Chargement initial
LoadContentPage();
