document.addEventListener('DOMContentLoaded', () => {
    const notesList = document.querySelector('[data-notes-list]');
    const emptyState = document.querySelector('[data-notes-empty]');
    const status = document.querySelector('[data-notes-status]');

    if (!notesList || !emptyState || !status) {
        return;
    }

    loadNotes(notesList, emptyState, status);
});

async function loadNotes(notesList, emptyState, status) {
    try {
        const response = await fetch('/api/notes', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('API odgovor nije uspješan.');
        }

        const payload = await response.json();
        const notes = Array.isArray(payload.data) ? payload.data : [];

        notesList.innerHTML = '';
        notes.forEach((note) => notesList.appendChild(createNoteCard(note)));

        notesList.hidden = notes.length === 0;
        emptyState.hidden = notes.length > 0;
        status.textContent = `Učitano bilješki: ${notes.length}`;
    } catch (error) {
        status.textContent = 'Bilješke nije moguće učitati preko API-ja.';
        status.title = error.message;
    }
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
        category.style.setProperty('--category-color', note.kategorija_boja);
    }

    const user = document.createElement('span');
    user.textContent = note.korisnik_ime || 'Nepoznat korisnik';

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
