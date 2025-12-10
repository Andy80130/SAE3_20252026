// Fonction de debounce pour limiter les appels API
function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}


/**
 * Configure l'autocomplétion et met à jour les champs de coordonnées dans le DIV (pour la map)
 * ET les champs cachés du formulaire de création (pour la soumission).
 */
function setupAutocomplete(inputId, suggestionsId, latId, lonId) {
    const input = document.getElementById(inputId);
    const suggestions = document.getElementById(suggestionsId);

    // Champs cachés dans le DIV (pour lecture par refreshMap)
    const latField = document.getElementById(latId);
    const lonField = document.getElementById(lonId);

    // Champs cachés dans le formulaire de création (pour soumission PHP)
    const valueField = document.getElementById(inputId + '_value');
    const latValueField = document.getElementById(latId + '_value');
    const lonValueField = document.getElementById(lonId + '_value');


    const runSearch = async () => {
        const query = input.value.trim();

        if (query.length < 3) {
            suggestions.innerHTML = '';
            return;
        }

        // On réinitialise les coordonnées si l'utilisateur modif. l'entrée
        latField.value = '';
        lonField.value = '';
        if (valueField) valueField.value = query;
        if (latValueField) latValueField.value = '';
        if (lonValueField) lonValueField.value = '';

        const url = `../includes/nominatim-proxy.php?q=${encodeURIComponent(query)}&limit=5`;

        try {
            const response = await fetch(url);

            if (!response.ok) {
                console.log("Erreur Nominatim :", response.status);
                suggestions.innerHTML = '<div class="suggestion-item">Rate limit, réessayez...</div>';
                return;
            }

            const results = await response.json();
            suggestions.innerHTML = '';

            results.forEach(place => {
                const div = document.createElement('div');
                div.textContent = place.display_name;
                div.classList.add('suggestion-item');
                div.onclick = () => {
                    // MAJ des champs
                    input.value = place.display_name;

                    // Maj des champs cachés.
                    latField.value = place.lat;
                    lonField.value = place.lon;


                    if (valueField) valueField.value = place.display_name;
                    if (latValueField) latValueField.value = place.lat;
                    if (lonValueField) lonValueField.value = place.lon;

                    suggestions.innerHTML = '';

                    //Affiche la carte après selection
                    refreshMap();
                };
                suggestions.appendChild(div);
            });
        } catch (error) {
            console.error("Erreur fetch Nominatim :", error);
        }
    };

    input.addEventListener('input', debounce(runSearch, 1200));
}


// Initialiser pour départ et destination
setupAutocomplete('depart', 'suggestions-depart', 'depart_lat', 'depart_lon');
setupAutocomplete('destination', 'suggestions-destination', 'destination_lat', 'destination_lon');