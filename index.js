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




function showPostForm(){
    const form = document.querySelector("#postForm");
    const overlay = document.getElementById("formPopupOverlay");
    if (form.style.display == "block"){
        form.style.display = "none";
        overlay.style.display = "none";
    }
    else{
        form.style.display = "block";
        overlay.style.display = "block";
    }
}

// function showPostForm() {
//     // document.getElementById("formPopupOverlay").hidden = false;
//   }
  document.getElementById("formPopupOverlay").addEventListener("click", function(e) {
    if (e.target.id === "formPopupOverlay") {
      closePostForm();
    }
  });
  

  function closePostForm() {
    document.getElementById("formPopupOverlay").style.display = "none";
    console.log("close");
  }
