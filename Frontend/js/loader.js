
export function showLoader(message = "Veuillez patienter...") {
    let loader = document.getElementById("global-loader");
    if (!loader) {
        loader = document.createElement("div");
        loader.id = "global-loader";
        loader.style.position = "fixed";
        loader.style.top = "0";
        loader.style.left = "0";
        loader.style.width = "100%";
        loader.style.height = "100%";
        loader.style.background = "rgba(255,255,255,0.8)";
        loader.style.display = "flex";
        loader.style.alignItems = "center";
        loader.style.justifyContent = "center";
        loader.style.fontSize = "1.5rem";
        loader.style.fontWeight = "bold";
        loader.style.color = "#333";
        loader.style.zIndex = "9999";
        document.body.appendChild(loader);
    }
    loader.textContent = message;
    loader.style.display = "flex";
}

export function hideLoader() {
    const loader = document.getElementById("global-loader");
    if (loader) loader.style.display = "none";
}
