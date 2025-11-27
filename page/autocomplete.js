// Fonction de debounce pour limiter les appels API
function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}


function setupAutocomplete(inputId, suggestionsId, latId, lonId) {
    const input = document.getElementById(inputId);
    const suggestions = document.getElementById(suggestionsId);
    const latField = document.getElementById(latId);
    const lonField = document.getElementById(lonId);

    const runSearch = async () => {
        const query = input.value.trim();

        if (query.length < 3) {
            suggestions.innerHTML = '';
            return;
        }

        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`;

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
                    input.value = place.display_name;
                    latField.value = place.lat;
                    lonField.value = place.lon;
                    suggestions.innerHTML = '';
                };
                suggestions.appendChild(div);
            });
        } catch (error) {
            console.error("Erreur fetch Nominatim :", error);
        }
    };

    input.addEventListener('input', debounce(runSearch, 300)); // ⬅️ évite le blocage
}


// Initialiser pour départ et destination
setupAutocomplete('depart', 'suggestions-depart', 'depart_lat', 'depart_lon');
setupAutocomplete('destination', 'suggestions-destination', 'destination_lat', 'destination_lon');
