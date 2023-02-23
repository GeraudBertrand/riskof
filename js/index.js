
var container1;
var container2;
var container3;
var container4;
var container5;
var container6;
var container7;
var container8;

window.addEventListener('load', async function () {

    try {
      getContainers();
      const response = await fetch('https://riskof.gerios.fr/api/items.php?classification=all');
      items = await response.json();
      items.forEach(item => {
        //switch createcard to the right container on the rarity value of the item
        switch (item.rarity) {
          case 1:
            createCard(container1, item);
            break;
          case 2:
            createCard(container2, item);
            break;
          case 3:
            createCard(container3, item);
            break;
          case 4:
            createCard(container4, item);
            break;
          case 5:
            createCard(container5, item);
            break;
          case 6:
            createCard(container6, item);
            break;
          case 7:
            createCard(container7, item);
            break;
        }

      });
    } catch (error) {
      console.error(error);
    }
  });



/**
 * Create a card for the item.
 * A card is composed of a div with the class "card" and inside a image with the class "card-image" and item.image as src.
 * @param {Element} container 
 * @param {JSON} item 
 */
function createCard(container, item) {
    let card = document.createElement("div");
    card.classList.add("card");
    let image = document.createElement("img");
    image.classList.add("card-image");
    image.src = "data:image/jpeg;base64," +item.image;
    image.alt = item.name;
    card.appendChild(image);
    //tooltip from span
    let tooltip = createTooltip(item);
    card.appendChild(tooltip);

    container.appendChild(card);
}


/**
 * Get all card-container elements. Use switch of data-rarity number to set it in the right variable.
 */
function getContainers() {
    let containers = document.querySelectorAll(".card-container");
    containers.forEach(container => {
        switch (container.dataset.rarity) {
            case "1":
                container1 = container;
                break;
            case "2":
                container2 = container;
                break;
            case "3":
                container3 = container;
                break;
            case "4":
                container4 = container;
                break;
            case "5":
                container5 = container;
                break;
            case "6":
                container6 = container;
                break;
            case "7":
                container7 = container;
                break;
            case "8":
                container8 = container;
                break;
        }
    });
}

/**
 * Create a tooltip for the item.
 * A tooltip is composed of a span with multiple paragraph inside
 * Each paragraph is a properties of the item.
 * @param {JSON} item
 * @returns {Element} tooltip
 */
function createTooltip(item) {
    let tooltip = document.createElement("span");

    tooltip.classList.add("tooltiptext");
    let name = document.createElement("p");
    name.classList.add("tooltip-name");
    name.innerHTML = item.name;
    tooltip.appendChild(name);

    let description = document.createElement("p");
    description.classList.add("tooltip-description");
    description.innerHTML = item.description;
    tooltip.appendChild(description);

    let effect = document.createElement("p");
    effect.classList.add("tooltip-effect");
    effect.innerHTML = item.effect;
    effect.innerHTML = searchAndReplace(effect.innerHTML,"base1",item);
    effect.innerHTML = searchAndReplace(effect.innerHTML,"stack1",item);
    effect.innerHTML = searchAndReplace(effect.innerHTML,"base2",item);
    effect.innerHTML = searchAndReplace(effect.innerHTML,"stack2",item);
    effect.innerHTML = searchAndReplace(effect.innerHTML,"base3",item);
    effect.innerHTML = searchAndReplace(effect.innerHTML,"stack3",item);
    tooltip.appendChild(effect);

    return tooltip;
}

/**
 * Search specific word in the text and replace it with a span with the class "color" and the value of the specific word from the item
 * @param {String} text
 * @param {JSON} item
 * @returns {String} text
 */
function searchAndReplace(text, search, item) {
    let tosearch = "%" + search +"%";
    let matches = text.match(tosearch);
    if (matches != null) {
        matches.forEach(match => {
            if(item[search] < 1){
                item[search] = item[search] * 100;
            }
            text = text.replace(match, "<span class='color-" + search + "'>" + item[search] + "</span>");
        });
    }
    return text;
}