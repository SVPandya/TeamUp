// function searchPlaces() {
//     const todoLocation = document.querySelector("#userInputTodoLocation").value;
//     const service = new google.maps.places.PlacesService(document.createElement("div"));
//     const secondResults = document.querySelector("#secondResults");
//     const list = document.querySelector("#list");

//     const request = {
//         query: todoLocation,
//     };

//     service.textSearch(request, (results, status) => {
//         const resultsDiv = document.getElementById("results");
//         resultsDiv.innerHTML = '';
//         list.innerHTML = '';

//         if (status === google.maps.places.PlacesServiceStatus.OK) {
//             results = results.slice(0, 5);
//             results.forEach(place => {
//                 const name = place.name;
//                 const address = place.formatted_address;
//                 // resultsDiv.innerHTML += `<p><strong>${name}</strong><br>${address}</p>`;
//                 const listItem = document.createElement("li")
//                 const link = document.createElement("a")
//                 // listItem.innerHTML = `<p><strong>${name}</strong><br>${address}HELLOOOOO</p>`
//                 link.innerHTML = `<p><strong>${name}</strong><br>${address}</p>`
//                 link.href = `https://www.google.com`;
//                 listItem.appendChild(link);
//                 // listItem.innerHTML = `<p><strong>${name}</strong><br>${address}</p>`
//                 list.appendChild(listItem);
//             });
//         } else {
//             resultsDiv.innerHTML = `<p>Error: ${status}</p>`;
//         }
//     });
// }
// function searchPlaces(){


let selectedLat = null;
let selectedLng = null;
let selectedDate = null;
let address = null;




document.addEventListener("DOMContentLoaded", () => {
    const sportInput = document.getElementById("sport");
    const locationInput = document.getElementById("location");
    const suggestionsBox = document.getElementById("suggestions");

    const service = new google.maps.places.PlacesService(document.createElement("div"));

    locationInput.addEventListener("input", () => {
        const sport = sportInput.value.trim();
        const location = locationInput.value.trim();
        const query = `${sport} in ${location}`;

        if (location.length < 2 || sport === "") {
            suggestionsBox.style.display = "none";
            return;
        }

        const request = {
            query: query
        };

        service.textSearch(request, (results, status) => {
            suggestionsBox.innerHTML = '';
            if (status === google.maps.places.PlacesServiceStatus.OK) {
                results = results.slice(0, 5);
                results.forEach(place => {
                    const div = document.createElement("div");
                    div.style.padding = "5px";
                    div.style.cursor = "pointer";
                    div.textContent = `${place.name}, ${place.formatted_address}`;

                    div.addEventListener("click", () => {
                        locationInput.value = place.formatted_address;
                        suggestionsBox.style.display = "none";
                        var geocoder = new google.maps.Geocoder();
                        address = document.getElementById("location").value;
                        var body = document.body;
                        geocoder.geocode({ 'address': locationInput.value }, function (results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                // results[0].geometry.location.latitude;
                                // results[0].geometry.location.longitude;
                                var lat = results[0].geometry.location.lat();
                                var lng = results[0].geometry.location.lng();
                                console.log(lat, ", ", lng);
                                selectedLat = lat;
                                selectedLng = lng;
                                tryFetchWeather();










                                // fetch(`weatherProxy.php?lat=${lat}&lng=${lng}`)
                                //     .then(res => res.json())
                                //     .then(data => {
                                //         console.log("Full Weather Response:", data);
                                //     })
                                //     .catch(err => console.error("Weather Proxy Error:", err));



                            }
                        });

                    });
                    suggestionsBox.appendChild(div);
                });
                suggestionsBox.style.display = "block";
            } else {
                suggestionsBox.style.display = "none";
            }
        });
    });

    // Optional: hide on click outside
    document.addEventListener("click", (e) => {
        if (!suggestionsBox.contains(e.target) && e.target !== locationInput) {
            suggestionsBox.style.display = "none";
        }

    });
});
// }




function showPostForm() {
    const form = document.querySelector("#postForm");
    const overlay = document.getElementById("formPopupOverlay");
    if (form.style.display == "block") {
        form.style.display = "none";
        overlay.style.display = "none";
    }
    else {
        form.style.display = "block";
        overlay.style.display = "block";
    }
}

// function showPostForm() {
//     // document.getElementById("formPopupOverlay").hidden = false;
//   }
document.getElementById("formPopupOverlay").addEventListener("click", function (e) {
    if (e.target.id === "formPopupOverlay") {
        closePostForm();
    }
});


function closePostForm() {
    document.getElementById("formPopupOverlay").style.display = "none";
    console.log("close");
}




async function tryFetchWeather() {
    if (!selectedLat || !selectedLng) {
        console.log("still null");
        console.log(`selectedLat: ${selectedLat}`);
        console.log(`selectedLng: ${selectedLng}`);
        return;
    }

    const maps3d = await google.maps.importLibrary("maps3d");
    // const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
    const Map3DElement = maps3d.Map3DElement;
    const MapMode = maps3d.MapMode;
    const Marker3DElement = maps3d.Marker3DElement;


    const map3DElement = new Map3DElement({
        center: { lat: selectedLat, lng: selectedLng, altitude: 100 },
        range: 650,
        tilt: 65,
        heading: 0,
        mode: MapMode.SATELLITE,
    });
    const marker = new Marker3DElement({
        position: { lat: selectedLat, lng: selectedLng, altitude: 20 },
        label: address,
        altitudeMode: 'RELATIVE_TO_GROUND',
        extruded: true,
    });

    map3DElement.append(marker);

    console.log(`Selected vals: ${selectedLat}, ${selectedLng}`);

    map3DElement.style.width = "100%";
    map3DElement.style.height = "100%";

    const mapContainer = document.getElementById("map3d");
    if (mapContainer) {
        mapContainer.innerHTML = "";
        mapContainer.appendChild(map3DElement);

    }

    // const container = document.getElementById("map3d");
    // if (container) container.appendChild(map3DElement);

    console.log('not if');
    fetch(`weatherProxy.php?lat=${selectedLat}&lng=${selectedLng}`)
        .then(res => res.json())
        .then(data => {

            const days = data.response.forecastDays;

            if (Array.isArray(days)) {
                const dailyWeather = document.querySelector("#hourlyWeather");
                dailyWeather.innerHTML = "";

                days.forEach((day, index) => {
                    const date = day.displayDate.month + "/" + day.displayDate.day;
                    const maxTemp = day.maxTemperature.degrees;
                    const minTemp = day.minTemperature.degrees;
                    const precip = day.daytimeForecast.precipitation.probability.percent;

                    console.log(`Temperature: ${minTemp} - ${maxTemp}°F`);
                    console.log(`Precipitation Chance: ${precip}%`);

                    const gif = document.createElement('img');
                    gif.src = 'icons8-sun.gif';
                    if (precip >= 30) {
                        gif.src = 'icons8-rain.gif';
                    }
                    gif.alt = 'Chance of Rain';
                    gif.style.width = '50px';
                    gif.style.width = '50px';
                    const li = document.createElement("li");
                    li.classList.add("weatherList");
                    li.innerHTML = `<b>${date}</b><br>${minTemp}°F - ${maxTemp}°F<br>${precip}% Rain<br>`;
                    li.appendChild(gif);
                    dailyWeather.appendChild(li);

                });
            } else {
                console.warn("Hourly data not available.");
            }
        })
        .catch(err => console.error("Weather Proxy Error:", err));
}
