function getStatusString(status) {
    "use strict";
    let stringName = "";
    if (status == 0) {
        stringName = "bestellt";
    } else if (status == 1) {
        stringName = "im Ofen";
    } else if (status == 2) {
        stringName = "auslieferbereit";
    } else if (status == 3) {
        stringName = "unterwegs";
    } else if (status == 4) {
        stringName = "geliefert";
    }
    return stringName;
}

function process(response) {
    "use strict";

    let jsonarray = JSON.parse(response);
    let uls = document.getElementById("bestellungen");
    while (uls.firstChild) {
        uls.removeChild(uls.firstChild);
    }
    let uList = document.createElement("ul");
    uList.id = "customerOrderList";
    uls.appendChild(uList);
    let place;

    for (place in jsonarray) {
        let name = jsonarray[place].name;
        let statusString = getStatusString(jsonarray[place].status);
        let uListElement = document.createElement("li");
        uListElement.className = "customerItem";
        let uListElementText = document.createTextNode(name + ": " + statusString);
        uListElement.appendChild(uListElementText);
        uList.appendChild(uListElement);
    }
}

function processData() {
    "use strict";
    if (request.readyState === 4) { // Uebertragung = DONE
        if (request.status === 200) { // HTTP-Status = OK
            if (request.responseText !== null) {
                process(request.responseText); // Daten verarbeiten
            } else {
                console.error("Dokument ist leer");
            }
        } else {
            console.error("Uebertragung fehlgeschlagen");
        }
    } else; // Uebertragung laeuft noch
}

let request = new XMLHttpRequest();
function requestData() { // fordert die Daten asynchron an
    "use strict";
    //request.open("GET", url + "?" + parms);
    request.open("GET", "KundenStatus.php"); // URL für HTTP-GET
    request.onreadystatechange = processData; //Callback-Handler zuordnen
    request.send(null); // Request abschicken
}

function startPolling() {
    requestData();
    window.setInterval(requestData, 2000);
}