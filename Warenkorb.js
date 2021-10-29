let totalCost = 0;

function checkInput() {
    "use strict";
    let address = document.getElementById("adresse").value;
    let waren = document.getElementById("warenkorb");
    let button = document.getElementById("bestellen");

    if (address !== "" && waren.length > 0) {
        button.disabled = false;
    } else {
        button.disabled = true;
    }
}

function updateTotalPrice() {
    "use strict";
    let sumText = document.getElementById("price");
    sumText.firstChild.nodeValue = "Gesamtpreis: " + totalCost.toFixed(2) + " €";
    checkInput();
}

function addPizzaToCart(pizzaName, pizzaPrice) {
    "use strict";
    let korb = document.getElementById("warenkorb");
    let newElement = document.createElement("option");
    newElement.text = pizzaName;
    newElement.setAttribute("price", pizzaPrice);
    korb.options[korb.length] = newElement;
    totalCost = parseFloat(totalCost) + parseFloat(pizzaPrice);
    updateTotalPrice();
}

function deleteSelected() {
    "use strict";
    let korb = document.getElementById("warenkorb");
    let toDelete = 0;
    let i = 0;
    for (i = korb.length - 1; i >= 0; i -= 1) {
        if (korb.options[i].selected === true) {
            toDelete = korb[i].getAttribute("price");
            totalCost = totalCost - parseFloat(toDelete);
            korb.options[i].remove();
        }
    }
    updateTotalPrice();
}

function deleteAll() {
    "use strict";
    document.getElementById("warenkorb").options.length = 0;
    totalCost = 0;
    updateTotalPrice();
}

function sendOrder() {
    "use strict";
    let waren = document.getElementById("warenkorb");
    let i;
    for (i = 0; i < waren.length; i += 1) {
        if (waren.options[i].selected === false) {
            waren.options[i].selected = true;
        }
    }
}