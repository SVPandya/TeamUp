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
                        var address = document.getElementById("location").value;
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






// FOR HOURLY FORECASTS
// function tryFetchWeather() {
//     if (!selectedLat || !selectedLng || !selectedDate) {
//         // if (!selectedLat || !selectedLng) {
//         console.log("still null");
//         console.log(`selectedLat: ${selectedLat}`);
//         console.log(`selectedLng: ${selectedLng}`);
//         console.log(`selectedDate: ${selectedDate}`);
//         return;
//     }
//     console.log('not if');
//     fetch(`weatherProxy.php?lat=${selectedLat}&lng=${selectedLng}`)
//         .then(res => res.json())
//         .then(data => {
//             // console.log("Status Code:", data.status);
//             // console.log("Weather Response:", data.response);


//             const hours = data.response.forecastHours;

//             if (Array.isArray(hours)) {
//                 const userDate = document.getElementById("date").value;
//                 console.log("userDate: ", userDate);

//                 const filtered = hours.filter(hour => hour.interval.startTime.startsWith(userDate));
//                 if (filtered.length === 0) {
//                     console.log("No forecast data for this date.");
//                     return;
//                 }

//                 filtered.forEach((hour, index) => {
//                     const temp = hour.temperature.degrees;
//                     const precip = hour.precipitation.probability.percent;
//                     // const time = hour.interval.startTime;
//                     const startTime = new Date(hour.interval.startTime).toLocaleString(undefined, {
//                         hour: '2-digit',
//                         minute: '2-digit',
//                         hour12: true,
//                         month: 'short',
//                         day: 'numeric',
//                     });
//                     const endTime = new Date(hour.interval.endTime).toLocaleString(undefined, {
//                         hour: '2-digit',
//                         minute: '2-digit',
//                         hour12: true,
//                         month: 'short',
//                         day: 'numeric',
//                     });
//                     const dewPoint = hour.dewPoint.degrees;
//                     console.log(`Dew Point: ${dewPoint}`);
//                     console.log(`Hour ${index + 1} (${startTime} - ${endTime}):`);
//                     console.log(`Temperature: ${temp}°F`);
//                     console.log(`Precipitation Chance: ${precip}%`);
//                     var hourlyWeather = document.querySelector("#hourlyWeather");
//                     const gif = document.createElement('img');
//                     gif.src = 'icons8-sun.gif';
//                     if (precip >= 30) {
//                         gif.src = 'icons8-rain.gif';
//                     }
//                     gif.alt = 'Chance of Rain';
//                     gif.style.width = '20px';
//                     gif.style.width = '20px';
//                     const li = document.createElement("li");

//                     var splitStartDateTime = startTime.split(", ");
//                     var splitEndDateTime = endTime.split(", ");
//                     li.innerHTML = `${splitStartDateTime[1]} - ${splitEndDateTime[1]}<br>${temp}°F<br>${precip}%`;
//                     li.appendChild(gif);
//                     hourlyWeather.appendChild(li);

//                 });
//             } else {
//                 console.warn("Hourly data not available.");
//             }
//         })
//         .catch(err => console.error("Weather Proxy Error:", err));
// }
//FOR DAILY FORECASTS
function tryFetchWeather() {
    if (!selectedLat || !selectedLng) {
        console.log("still null");
        console.log(`selectedLat: ${selectedLat}`);
        console.log(`selectedLng: ${selectedLng}`);
        return;
    }
    console.log('not if');
    fetch(`weatherProxy.php?lat=${selectedLat}&lng=${selectedLng}`)
        .then(res => res.json())
        .then(data => {
            // console.log("Status Code:", data.status);
            // console.log("Weather Response:", data.response);


            const days = data.response.forecastDays;

            if (Array.isArray(days)) {
                // const userDate = document.getElementById("date").value;
                // console.log("userDate: ", userDate);

                // const filtered = hours.filter(hour => hour.interval.startTime.startsWith(userDate));
                // if (filtered.length === 0) {
                //     console.log("No forecast data for this date.");
                //     return;
                // }
                const dailyWeather = document.querySelector("#hourlyWeather");
                dailyWeather.innerHTML = "";

                days.forEach((day, index) => {
                    const date = day.displayDate.month + "/" + day.displayDate.day;
                    const maxTemp = day.maxTemperature.degrees;
                    const minTemp = day.minTemperature.degrees;
                    const precip = day.daytimeForecast.precipitation.probability.percent;
                    // const time = hour.interval.startTime;
                    // const startTime = new Date(day.interval.startTime).toLocaleString(undefined, {
                    //     hour: '2-digit',
                    //     minute: '2-digit',
                    //     hour12: true,
                    //     month: 'short',
                    //     day: 'numeric',
                    // });
                    // const endTime = new Date(hour.interval.endTime).toLocaleString(undefined, {
                    //     hour: '2-digit',
                    //     minute: '2-digit',
                    //     hour12: true,
                    //     month: 'short',
                    //     day: 'numeric',
                    // });

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
                    // var splitStartDateTime = startTime.split(", ");
                    // var splitEndDateTime = endTime.split(", ");
                    // li.innerHTML = `${splitStartDateTime[1]} - ${splitEndDateTime[1]}<br>${temp}°F<br>${precip}%`;
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