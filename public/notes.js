const TOKEN_KEY = 'noteapp_jwt_token';
const USER_KEY = 'noteapp_user';

document.addEventListener('DOMContentLoaded', () => {
    const elements = {
        notesList: document.querySelector('[data-notes-list]'),
        emptyState: document.querySelector('[data-notes-empty]'),
        emptyMessage: document.querySelector('[data-empty-message]'),
        status: document.querySelector('[data-notes-status]'),
        loginForm: document.querySelector('[data-login-form]'),
        registerForm: document.querySelector('[data-register-form]'),
        authGrid: document.querySelector('.auth-grid'),
        authStatus: document.querySelector('[data-auth-status]'),
        authMessage: document.querySelector('[data-auth-message]'),
        logoutButton: document.querySelector('[data-logout]'),
        adminPanel: document.querySelector('[data-admin-panel]'),
        adminUsers: document.querySelector('[data-admin-users]'),
        adminNotes: document.querySelector('[data-admin-notes]'),
    };

    if (!elements.notesList || !elements.emptyState || !elements.status) {
        return;
    }

    bindAuthForm(elements.loginForm, '/api/auth/login', elements);
    bindAuthForm(elements.registerForm, '/api/auth/register', elements);

    elements.logoutButton?.addEventListener('click', () => {
        clearAuth();
        showLoggedOutState(elements);
    });

    refreshAuthenticatedView(elements);
});

function bindAuthForm(form, endpoint, elements) {
    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        try {
            const payload = Object.fromEntries(new FormData(form));
            const result = await sendAuthRequest(endpoint, payload);

            saveAuth(result.data.token, result.data.user);
            form.reset();
            refreshAuthenticatedView(elements);
        } catch (error) {
            elements.status.textContent = error.message;
        }
    });
}

async function sendAuthRequest(endpoint, data) {
    const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    });

    const payload = await response.json();

    if (!response.ok) {
        throw new Error(payload.error || 'Autentikacijski zahtjev nije uspio.');
    }

    return payload;
}

function refreshAuthenticatedView(elements) {
    const token = localStorage.getItem(TOKEN_KEY);
    const user = readStoredUser();

    if (!token || !user) {
        showLoggedOutState(elements);
        return;
    }

    showLoggedInState(elements, user);
    loadNotes(elements, token);

    if (user.uloga === 'admin') {
        loadAdminPanel(elements, token);
    }
}

function showLoggedOutState(elements) {
    elements.authGrid.hidden = false;
    elements.authStatus.hidden = true;
    elements.adminPanel.hidden = true;
    elements.notesList.innerHTML = '';
    elements.notesList.hidden = true;
    elements.emptyState.hidden = false;
    elements.emptyMessage.textContent = 'Prijavite se kako biste učitali svoje bilješke.';
    elements.status.textContent = 'Prijavite se ili registrirajte za pristup bilješkama.';
}

function showLoggedInState(elements, user) {
    elements.authGrid.hidden = true;
    elements.authStatus.hidden = false;
    elements.authMessage.textContent = `Prijavljeni ste kao ${user.ime} (${user.uloga}).`;
    elements.adminPanel.hidden = user.uloga !== 'admin';
    elements.status.textContent = 'Učitavanje vaših bilješki...';
}

async function loadNotes(elements, token) {
    try {
        const response = await fetch('/api/notes', {
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${token}`,
            },
        });

        const payload = await response.json();

        if (response.status === 401) {
            clearAuth();
            showLoggedOutState(elements);
            throw new Error(payload.error || 'Prijava je istekla.');
        }

        if (!response.ok) {
            throw new Error(payload.error || 'API odgovor nije uspješan.');
        }

        const notes = Array.isArray(payload.data) ? payload.data : [];

        elements.notesList.innerHTML = '';
        notes.forEach((note) => elements.notesList.appendChild(createNoteCard(note)));

        elements.notesList.hidden = notes.length === 0;
        elements.emptyState.hidden = notes.length > 0;
        elements.emptyMessage.textContent = 'Za prijavljenog korisnika nema bilješki.';
        elements.status.textContent = `Učitano bilješki: ${notes.length}`;
    } catch (error) {
        elements.status.textContent = 'Bilješke nije moguće učitati preko API-ja.';
        elements.status.title = error.message;
    }
}

async function loadAdminPanel(elements, token) {
    try {
        const [usersPayload, notesPayload] = await Promise.all([
            fetchAdminResource('/api/admin/users', token),
            fetchAdminResource('/api/admin/notes', token),
        ]);

        renderAdminUsers(elements.adminUsers, usersPayload.data || []);
        renderAdminNotes(elements.adminNotes, notesPayload.data || []);
    } catch (error) {
        elements.adminUsers.textContent = 'Admin podatke nije moguće učitati.';
        elements.adminNotes.textContent = error.message;
    }
}

async function fetchAdminResource(endpoint, token) {
    const response = await fetch(endpoint, {
        headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
        },
    });
    const payload = await response.json();

    if (!response.ok) {
        throw new Error(payload.error || 'Admin zahtjev nije uspio.');
    }

    return payload;
}

function renderAdminUsers(container, users) {
    container.innerHTML = '';

    if (users.length === 0) {
        container.textContent = 'Nema korisnika.';
        return;
    }

    users.forEach((user) => {
        const item = document.createElement('p');
        item.textContent = `${user.ime} (${user.uloga}) - bilješke: ${user.broj_biljeski}, kategorije: ${user.broj_kategorija}`;
        container.appendChild(item);
    });
}

function renderAdminNotes(container, notes) {
    container.innerHTML = '';

    if (notes.length === 0) {
        container.textContent = 'Nema bilješki.';
        return;
    }

    notes.forEach((note) => {
        const item = document.createElement('p');
        item.textContent = `${note.naslov} - ${note.korisnik_ime}`;
        container.appendChild(item);
    });
}

function createNoteCard(note) {
    const card = document.createElement('article');
    card.className = 'note-card';

    const meta = document.createElement('div');
    meta.className = 'note-meta';

    const category = document.createElement('span');
    category.className = note.kategorija_naziv ? 'category' : 'category muted';
    category.textContent = note.kategorija_naziv || 'Bez kategorije';

    if (note.kategorija_boja) {
        category.style.setProperty('--category-color', normalizeColor(note.kategorija_boja));
    }

    const user = document.createElement('span');
    user.textContent = note.korisnik_ime || 'Moja bilješka';

    const title = document.createElement('h2');
    title.textContent = note.naslov || 'Bez naslova';

    const content = document.createElement('p');
    content.textContent = shorten(note.sadrzaj || '', 140);

    const link = document.createElement('a');
    link.href = `/notes/${note.id}`;
    link.textContent = 'Otvori detalje';

    meta.append(category, user);
    card.append(meta, title, content, link);

    return card;
}

function shorten(value, maxLength) {
    return value.length > maxLength ? `${value.slice(0, maxLength)}...` : value;
}

function normalizeColor(value) {
    return /^#[0-9a-fA-F]{6}$/.test(value) ? value : '#777777';
}

function saveAuth(token, user) {
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user));
}

function clearAuth() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
}

function readStoredUser() {
    try {
        return JSON.parse(localStorage.getItem(USER_KEY));
    } catch {
        clearAuth();
        return null;
    }
}
