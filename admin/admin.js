const form = document.querySelector('#item-form');
const list = document.querySelector('#item-list');
const search = document.querySelector('#item-search');
const status = document.querySelector('#status');
const imageInput = document.querySelector('#image');
const imagePreview = document.querySelector('#image-preview');
const previewEmpty = document.querySelector('#preview-empty');
const fileName = document.querySelector('#file-name');
const fields = [...form.querySelectorAll('input, textarea, select')];
const effectField = document.querySelector('#effect');
let items = [];
let selectedId = null;
const originLabels = {
    1: 'Jeu de base',
    2: 'Jeu de base (réserver)',
    3: 'Survivors of the Void',
    4: 'Seekers of the Storm',
    5: 'Alloy Collective',
    6: 'Hallowed Concepts'
};

function showStatus(message, type = 'success') {
    status.textContent = message;
    status.className = `status show ${type}`;
}

function clearStatus() {
    status.textContent = '';
    status.className = 'status';
}

function imageUrl(item) {
    return `../Content/image/item/${encodeURIComponent(item.image || '')}`;
}

function insertToken(token) {
    const start = effectField.selectionStart;
    const end = effectField.selectionEnd;
    const value = effectField.value;
    effectField.value = value.slice(0, start) + token + value.slice(end);
    effectField.focus();
    effectField.setSelectionRange(start + token.length, start + token.length);
    effectField.dispatchEvent(new Event('input', { bubbles: true }));
}

function renderList() {
    const query = search.value.trim().toLowerCase();
    const filtered = items.filter(item => (item.name || '').toLowerCase().includes(query));
    list.replaceChildren();

    if (!filtered.length) {
        const empty = document.createElement('p');
        empty.className = 'empty-list';
        empty.textContent = 'Aucun item trouvé.';
        list.appendChild(empty);
        return;
    }

    filtered.forEach(item => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `item-row${selectedId === Number(item.id) ? ' active' : ''}`;
        button.dataset.id = item.id;

        const image = document.createElement('img');
        image.src = imageUrl(item);
        image.alt = '';
        image.onerror = () => { image.hidden = true; };
        const copy = document.createElement('span');
        const name = document.createElement('strong');
        name.textContent = item.name || '(sans nom)';
        const meta = document.createElement('small');
        meta.textContent = `Rareté ${item.rarity} · ${originLabels[item.orig] || `Origine ${item.orig}`}`;
        copy.append(name, meta);
        button.append(image, copy);
        button.addEventListener('click', () => selectItem(Number(item.id)));
        list.appendChild(button);
    });
}

function setField(name, value) {
    const field = form.elements[name];
    if (field) field.value = value ?? '';
}

function selectItem(id) {
    const item = items.find(entry => Number(entry.id) === id);
    if (!item) return;
    selectedId = id;
    fields.forEach(field => { field.disabled = false; });
    ['id', 'name', 'rarity', 'orig', 'description', 'image_name', 'effect', 'base1', 'stack1', 'base2', 'stack2', 'base3', 'stack3']
        .forEach(name => setField(name, item[name]));
    document.querySelector('#form-mode').textContent = 'MODIFICATION';
    document.querySelector('#form-title').textContent = item.name;
    document.querySelector('#item-id').textContent = `ID ${item.id}`;
    document.querySelector('#delete-item').hidden = false;
    imageInput.value = '';
    fileName.textContent = 'Aucun nouveau fichier';
    setPreview(item.image ? imageUrl(item) : '');
    clearStatus();
    renderList();
}

function resetForm() {
    selectedId = null;
    form.reset();
    setField('rarity', '1');
    setField('orig', '1');
    document.querySelector('#form-mode').textContent = 'NOUVELLE ENTREE';
    document.querySelector('#form-title').textContent = 'Ajouter un item';
    document.querySelector('#item-id').textContent = 'Nouveau';
    document.querySelector('#delete-item').hidden = true;
    fileName.textContent = 'Aucun nouveau fichier';
    setPreview('');
    clearStatus();
    renderList();
}

function setPreview(source) {
    if (!source) {
        imagePreview.hidden = true;
        imagePreview.removeAttribute('src');
        previewEmpty.hidden = false;
        return;
    }
    imagePreview.src = source;
    imagePreview.onload = () => {
        imagePreview.hidden = false;
        previewEmpty.hidden = true;
    };
    imagePreview.onerror = () => {
        imagePreview.hidden = true;
        previewEmpty.hidden = false;
    };
}

async function loadItems() {
    try {
        const response = await fetch('../api/admin_items.php');
        if (!response.ok) throw new Error('Impossible de charger les items');
        items = await response.json();
        items.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
        renderList();
    } catch (error) {
        showStatus(`${error.message}. Vérifie l'authentification admin.`, 'error');
    }
}

form.addEventListener('submit', async event => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const data = new FormData(form);
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    clearStatus();
    try {
        const response = await fetch('../api/admin_items.php', { method: 'POST', body: data });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Enregistrement impossible');
        showStatus(result.message);
        await loadItems();
        if (result.id) selectItem(Number(result.id));
    } catch (error) {
        showStatus(error.message, 'error');
    } finally {
        button.disabled = false;
    }
});

document.querySelector('#delete-item').addEventListener('click', async () => {
    if (!selectedId || !window.confirm('Supprimer cet item ?')) return;
    const data = new URLSearchParams({ id: String(selectedId) });
    try {
        const response = await fetch('../api/admin_items.php', { method: 'DELETE', body: data });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Suppression impossible');
        resetForm();
        await loadItems();
        showStatus(result.message);
    } catch (error) {
        showStatus(error.message, 'error');
    }
});

document.querySelector('#new-item').addEventListener('click', resetForm);
document.querySelector('#reset-form').addEventListener('click', resetForm);
search.addEventListener('input', renderList);
document.querySelectorAll('.token-button').forEach(button => {
    button.addEventListener('click', () => insertToken(button.dataset.token));
});
imageInput.addEventListener('change', () => {
    const file = imageInput.files[0];
    if (!file) return;
    fileName.textContent = file.name;
    setField('image_name', file.name);
    setPreview(URL.createObjectURL(file));
});

resetForm();
loadItems();
