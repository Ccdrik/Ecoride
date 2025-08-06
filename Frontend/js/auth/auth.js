// === CONSTANTES ===
export const tokenCookieName = "token";
export const roleCookieName = "role";

// === GESTION DES COOKIES ===
export function setCookie(name, value, days = 1) {
    const expires = new Date(Date.now() + days * 86400000).toUTCString();
    document.cookie = `${name}=${value}; expires=${expires}; path=/`;
}

export function getCookie(name) {
    const cookie = document.cookie
        .split(";")
        .map(c => c.trim())
        .find(c => c.startsWith(`${name}=`));
    return cookie ? decodeURIComponent(cookie.split("=")[1]) : null;
}

export function deleteCookie(name) {
    document.cookie = `${name}=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT;`;
}

// === TOKEN ===
export function setToken(token) {
    setCookie(tokenCookieName, token);
}
export function getToken() {
    return getCookie(tokenCookieName);
}
export function deleteToken() {
    deleteCookie(tokenCookieName);
}

// === RÔLE ===
export function setRole(role) {
    setCookie(roleCookieName, role);
}
export function getRole() {
    return getCookie(roleCookieName);
}
export function deleteRole() {
    deleteCookie(roleCookieName);
}

// === ÉTAT DE CONNEXION ===
export function isConnected() {
    return !!getToken();
}

// === AFFICHAGE DES BOUTONS SELON LE RÔLE / ÉTAT ===
export function showAndHideElementsForRoles() {
    const role = getRole();
    const connected = isConnected();

    document.querySelectorAll("[data-show]").forEach(elem => {
        const condition = elem.getAttribute("data-show");

        const shouldShow =
            (condition === "connected" && connected) ||
            (condition === "disconnected" && !connected) ||
            (condition === role);

        elem.style.display = shouldShow ? "" : "none";
    });
}

// === DÉCONNEXION GLOBALE ===
export function clearAuthCookies() {
    deleteToken();
    deleteRole();
}

// === ERREUR 401 (non connecté) ===
export function handle401(response) {
    if (response.status === 401) {
        alert("Session expirée. Merci de vous reconnecter.");
        clearAuthCookies();
        window.history.pushState({}, "", "/signin");
        dispatchEvent(new PopStateEvent("popstate"));
        return true;
    }
    return false;
}
