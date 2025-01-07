var containers = [];

window.addEventListener('load', async function () {
    try {
      getContainers();
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
    image.src = "/Content/image/item/" +item.image+".webp";
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
    containers = document.querySelectorAll(".card-container");
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

    let smallLine = document.createElement("p");
    smallLine.classList.add("tooltip-small-line");
    tooltip.appendChild(smallLine);

    let description = document.createElement("p");
    description.classList.add("tooltip-description");
    description.innerHTML = item.description;
    tooltip.appendChild(description);

    let line = document.createElement("p");
    line.classList.add("tooltip-line");
    tooltip.appendChild(line);

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
                item[search] = parseFloat((item[search] * 100).toFixed(2));
            }
            text = text.replace(match, "<span class='color-" + search + "'>" + item[search] + "</span>");
        });
    }
    return text;
}