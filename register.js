document.addEventListener("DOMContentLoaded", () => {
    const cityInput = document.getElementById("town");
    const suggestionsBoxTwo = document.getElementById("citySuggestions");
    const autocompleteService = new google.maps.places.AutocompleteService();

    cityInput.addEventListener("input", () => {
        const value = cityInput.value.trim();
        if (value.length < 2) {
            suggestionsBoxTwo.innerHTML = "";
            suggestionsBoxTwo.style.display = "none";
            return;
        }

        autocompleteService.getPlacePredictions({
            input: value,
            types: ["(cities)"],  // restrict to cities
            componentRestrictions: { country: "us" }  // optional
        }, (predictions, status) => {
            suggestionsBoxTwo.innerHTML = "";
            if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) {
                suggestionsBoxTwo.style.display = "none";
                return;
            }

            predictions.forEach(prediction => {
                const div = document.createElement("div");
                div.textContent = prediction.description;
                div.style.padding = "5px";
                div.style.cursor = "pointer";

                div.addEventListener("click", () => {
                    cityInput.value = prediction.description;
                    suggestionsBoxTwo.style.display = "none";
                });

                suggestionsBoxTwo.appendChild(div);
            });

            suggestionsBoxTwo.style.display = "block";
        });
    });

    // Optional: close suggestions on outside click
    document.addEventListener("click", (e) => {
        if (!suggestionsBoxTwo.contains(e.target) && e.target !== cityInput) {
            suggestionsBoxTwo.style.display = "none";
        }
    });
});