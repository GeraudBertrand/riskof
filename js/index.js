var containers = [];
var items;

// Liste des extensions chargées depuis /api/extensions.php
//   { id, name, short_code, icon }
var extensions = [];

// Visibilité par extension id : { 1: true, 3: true, 4: true, ... }
// Vanilla (sans icon) est toujours visible.
var origVisible = {};

// equipments → raretés 7 / 8 / 9 dans le front-end
const EQUIP_RARITY_MAP = {
    'Equipment'       : 7,
    'Lunar Equipment' : 8,
    'Elite Equipment' : 9,
};

const API_BASE = 'https://riskof.gerios.fr/api';

window.addEventListener('load', async function () {
    try {
        getContainers();
    } catch (error) {
        console.error(error);
    }

    try {
        const [extRes, itemsRes, equipRes] = await Promise.all([
            fetch(`${API_BASE}/extensions.php?all=1`),
            fetch(`${API_BASE}/items.php?classification=all`),
            fetch(`${API_BASE}/equipments.php`),
        ]);

        // --- Extensions ---
        const allExt = extRes.ok ? (await extRes.json() || []) : [];
        extensions = allExt.filter(e => e.icon); // ceux qui ont un icon (= DLC)

        // Init visibilité : toutes les extensions visibles par défaut
        allExt.forEach(e => { origVisible[e.id] = true; });
        buildExtensionSelectors();

        // --- Items ---
        if (!itemsRes.ok) {
            const err = await itemsRes.json().catch(() => ({}));
            throw new Error(err.error ?? `items HTTP ${itemsRes.status}`);
        }
        const itemsData = await itemsRes.json() || [];
        const equipData = equipRes.ok ? (await equipRes.json() || []) : [];

        equipData.forEach(e => {
            e.rarity = EQUIP_RARITY_MAP[e.rarity_label] ?? 7;
            if (!e.effect && e.description_html) e.effect = e.description_html;
        });

        items = [...itemsData, ...equipData];

        if (items.length > 0) {
            items.sort((a, b) => a.name.localeCompare(b.name));
            items.forEach(item => {
                const container = containers[parseInt(item.rarity) - 1];
                if (container) createCard(container, item);
            });
        }
    } catch (error) {
        console.error(error);
    }
});


/**
 * Génère un toggle pour chaque DLC (extensions avec icon).
 */
function buildExtensionSelectors() {
    const host = document.getElementById('extension-selectors');
    if (!host) return;
    host.innerHTML = '';

    extensions.forEach(ext => {
        const wrap = document.createElement('div');
        wrap.classList.add('selector');

        const label = document.createElement('span');
        label.classList.add('label');
        label.textContent = ext.name;
        wrap.appendChild(label);

        const sw = document.createElement('label');
        sw.classList.add('switch');

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.checked = true;
        input.setAttribute('aria-label', (ext.short_code || ext.name) + ' switch');
        input.dataset.extId = String(ext.id);
        input.addEventListener('change', () => {
            origVisible[ext.id] = input.checked;
            applyVisibility();
        });
        sw.appendChild(input);

        const slider = document.createElement('span');
        slider.classList.add('slider', 'round');
        sw.appendChild(slider);

        wrap.appendChild(sw);
        host.appendChild(wrap);
    });
}


function applyVisibility() {
    if (!items) return;
    items.forEach(item => {
        if (!item.card) return;
        // Si l'extension de l'item n'est pas dans origVisible (Vanilla, etc.),
        // on considère qu'elle est toujours visible.
        const visible = origVisible[item.orig] !== false;
        item.card.classList.toggle('show', !visible);
    });
}


function createCard(container, item) {
    let card = document.createElement("div");
    card.classList.add("card");
    card.setAttribute("data-name", item.name);
    card.setAttribute("data-orig", item.orig);

    if (origVisible[item.orig] === false) {
        card.classList.add("show");
    }

    let image = document.createElement("img");
    image.classList.add("card-image");
    if (item.image) {
        const filename = /\.(webp|png|jpg|jpeg|gif)$/i.test(item.image)
            ? item.image
            : item.image + ".webp";
        image.src = "/Content/image/item/" + filename;
    }
    image.alt = item.name;
    card.appendChild(image);

    // Badge extension piloté par la table `extension`
    const ext = extensions.find(e => e.id == item.orig);
    if (ext && ext.icon) {
        let extImg = document.createElement("img");
        extImg.classList.add("card-ext");
        extImg.src = "/Content/image/" + ext.icon;
        extImg.alt = "extension " + (ext.short_code || ext.name);
        card.appendChild(extImg);
    }

    let tooltip = createTooltip(item);
    card.appendChild(tooltip);

    container.appendChild(card);
    item.card = card;
}


function getContainers() {
    containers = document.querySelectorAll(".card-container");
}

function getCards() {
    return document.querySelectorAll(".card");
}


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
    description.innerHTML = item.description ?? '';
    tooltip.appendChild(description);

    let line = document.createElement("p");
    line.classList.add("tooltip-line");
    tooltip.appendChild(line);

    let effect = document.createElement("p");
    effect.classList.add("tooltip-effect");

    if (item.description_html) {
        effect.innerHTML = item.description_html;
    } else if (item.effect) {
        let html = item.effect;
        html = searchAndReplace(html, "base1", item);
        html = searchAndReplace(html, "stack1", item);
        html = searchAndReplace(html, "base2", item);
        html = searchAndReplace(html, "stack2", item);
        html = searchAndReplace(html, "base3", item);
        html = searchAndReplace(html, "stack3", item);
        effect.innerHTML = html;
    }
    tooltip.appendChild(effect);

    return tooltip;
}


function searchAndReplace(text, search, item) {
    let tosearch = "%" + search + "%";
    let matches = text.match(tosearch);
    if (matches != null) {
        matches.forEach(match => {
            let value = item[search];
            if (typeof value === 'number' && value < 1) {
                value = parseFloat((value * 100).toFixed(2));
            }
            text = text.replace(match, "<span class='color-" + search + "'>" + value + "</span>");
        });
    }
    return text;
}


function search(value) {
    var cards = getCards();
    var filteredItem = items.filter(item => item.name.toLowerCase().includes(value.toLowerCase()));
    cards.forEach(card => {
        const cardName = card.getAttribute("data-name").toLowerCase();
        const isVisible = filteredItem.some(item => item.name.toLowerCase() == cardName);

        if (isVisible) {
            card.classList.remove("hidden");
        } else {
            card.classList.add("hidden");
        }
    });
}
