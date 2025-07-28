import { showSystemMessage, fetchData, renderNavbar , escapeHtml, shuffleArray} from './utils.js';

// Global state variables for index.php
let isGuest = window.appConfig.isGuest;
let userName = window.appConfig.userName;

//Elements 
const mainCenterDisplay = document.getElementById('mainCenterDisplay');

let currentDeckId = null; // ID of the currently selected deck
let decks = []; // Array of all decks
let categories = []; // Array of all categories
let cards = []; // Cards of the currently selected deck (for current deck view)
let studyMode = {
    active: false,
    deckId: null,
    cards: [], // Cards for the current study session
    currentIndex: 0
};


// --- UI Rendering Functions ---


async function renderDecksList() {
    const decksNameList = document.getElementById('decksNameList');
    if (!decksNameList) return; 

    decksNameList.innerHTML = '';
    
    // Remove initial message if it exists
    const noDecksMessage = document.getElementById('noDecksMessage');
    if (noDecksMessage) noDecksMessage.remove(); 

    if (isGuest) {
        decksNameList.innerHTML = '<p class="text-white-50 text-center mt-5" id="noDecksMessage">Please log in to manage your decks.</p>';
        return;
    }

    try {
        const data = await getDecks(); 
        decks = data.decks || []; 

        if (decks.length === 0) {
            decksNameList.innerHTML = '<p class="text-white-50 text-center mt-5" id="noDecksMessage">No decks found. Click "Add Deck" to create one!</p>';
            return;
        }
        decks.forEach(deck => {
            const deckItem = document.createElement('div');
            deckItem.className = 'deck-item d-flex justify-content-between align-items-center';
            deckItem.dataset.deckId = deck.id;
            deckItem.innerHTML = `
                <h6>${escapeHtml(deck.deckName)}</h6>
                
            `;
            decksNameList.appendChild(deckItem);
        });

        // If a deck was previously selected, re-select it
        if (currentDeckId) {
            const selectedDeckEl = document.querySelector(`.deck-item[data-deck-id="${currentDeckId}"]`);
            if (selectedDeckEl) {
                selectedDeckEl.classList.add('active');
            } else {
                currentDeckId = null; 
            }
        }
    } catch (error) {
        console.error('Failed to load decks:', error);
        decksNameList.innerHTML = '<p class="text-danger text-center mt-5" id="noDecksMessage">Failed to load decks. Please try again.</p>';
    }
}

function renderDeckSpecs(deckId) {
    const mainCenterDisplay = document.getElementById('mainCenterDisplay');
    if (!mainCenterDisplay) return;

    mainCenterDisplay.innerHTML = ''; 
    const deck = decks.find(d => d.id == deckId);
    console.log(deck)
    if (!deck) {
        mainCenterDisplay.innerHTML = '<p class="text-danger text-center mt-5">Deck not found.</p>';
        return;
    }

    const container = document.createElement('div');
    container.className = 'deck-spec-card card-animated';
    const categoryName = getDeckNameById(categories, deck.category_id)
    container.innerHTML = `
        <div class="deck-header">
            <div>
                <h2 class="deck-title">${deck.deckName}</h2>
                <p class="deck-category">${categoryName}</p>
            </div>
            <div class="deck-mastery">
                <span>${deck.mastery}%</span>
                <small>Mastery</small>
            </div>
        </div>

        <div class="deck-meta">
            <p><strong>Description:</strong><br>${deck.deckDescription?.replace(/\n/g, '<br>')}</p>
            <p><strong>Total Cards:</strong> ${deck.totalCards}</p>
            <p><strong>Created:</strong> ${deck.timeCreated}</p>
            <p><strong>Last Edited:</strong> ${deck.timeEdited}</p>
        </div>

        <div class="deck-actions">
            <div class="left">
                <button class="btn btn-glass text-info study-deck-btn" data-deck-id="${deck.id}">
                    <span class="material-symbols-outlined">school</span>
                    <span class="btn-text">Study</span>
                </button>
                <button class="btn btn-glass text-success quiz-deck-btn" data-deck-id="${deck.id}">
                    <span class="material-symbols-outlined">quiz</span>
                    <span class="btn-text">Exam</span>
                </button>
                <button class="btn btn-glass text-primary add-card-to-deck-btn" data-deck-id="${deck.id}">
                    <span class="material-symbols-outlined">note_add</span>
                    <span class="btn-text">Add Cards</span>
                </button>
            </div>
            <div class="right">
                <button class="btn btn-glass text-warning edit-deck-btn" data-deck-id="${deck.id}">
                    <span class="material-symbols-outlined">edit</span>
                    <span class="btn-text">Edit</span>
                </button>
                <button class="btn btn-glass text-danger delete-deck-btn" data-deck-id="${deck.id}">
                    <span class="material-symbols-outlined">delete</span>
                    <span class="btn-text">Delete</span>
                </button>
            </div>
        </div>
    `;

    mainCenterDisplay.appendChild(container);
}

async function renderCardsList(deckId, highlightCardId = null, initialCardId = null) {
    const mainCenterDisplay = document.getElementById('mainCenterDisplay');
    if (!mainCenterDisplay) return; 

    mainCenterDisplay.innerHTML = ''; 

    const deck = decks.find(d => d.id == deckId);
    if (!deck) {
        mainCenterDisplay.innerHTML = '<p class="text-danger text-center mt-5">Deck not found.</p>';
        return;
    }
    console.log(deck)
    // Header for cards list
    const cardListHeader = document.createElement('div');
    cardListHeader.className = 'd-flex justify-content-between align-items-center mb-3';
    cardListHeader.innerHTML = `
        <h4 class="text-info mb-0">${escapeHtml(deck.deckName)} Cards</h4>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary me-2 add-card-to-deck-btn" id="addCardBtn" data-deck-id="${deckId}">
                <span class="material-symbols-outlined">add</span> Add Card
            </button>
            <button type="button" class="btn btn-sm btn-primary study-deck-btn" id="startStudyBtn" data-deck-id="${deckId}">
                <span class="material-symbols-outlined">school</span> Start Study
        </div>
    `;
    mainCenterDisplay.appendChild(cardListHeader);

    const cardsContainer = document.createElement('div');
    cardsContainer.className = 'row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 overflow-auto flex-grow-1';
    cardsContainer.id = 'cardsContainer';
    mainCenterDisplay.appendChild(cardsContainer);

    try {
        const data = await fetchData(`/api/cards/get?deckId=${deckId}`);
        cards = data.cards || []; 

        if (cards.length === 0) {
            cardsContainer.innerHTML = '<p class="text-white-50 text-center w-100 mt-5">No cards in this deck. Click "Add Card" to create one!</p>';
            return;
        }

        cards.forEach(card => {
            const cardCol = document.createElement('div');
            cardCol.className = 'col';
            cardCol.innerHTML = `
                <div class="card card-fixed shadow-sm ">
                    <div class="card-body card-clickable card-interactive" ${highlightCardId == card.id ? 'highlighted-card' : ''}" card-id="${escapeHtml(card.cardId)}" card-fav="${escapeHtml(card.fav)}" card-difficulty="${escapeHtml(card.difficulty)}" card-correct-counter="${escapeHtml(card.correctCounter)}">
                           
                        <p class="card-text card-front text-white-50 mb-0">${escapeHtml(card.frontContent)}</p>
                        <p class="card-text card-back text-white-50 d-none mb-0">${escapeHtml(card.backContent)}</p>
                        
                    </div>
                </div>
            `;
            cardsContainer.appendChild(cardCol);
            
        });
        if (initialCardId) {
            const cardToOpen = cards.find(c => c.id == initialCardId);
            if (cardToOpen) {
                renderSingleCardView(cardToOpen, deck);
            } else {
                showSystemMessage('Specific card not found in this deck.', 'error', true);
            }
        }
        // Add scroll to and highlight logic
        if (highlightCardId) {
            const cardElementToHighlight = document.getElementById(`card-${highlightCardId}`);
            if (cardElementToHighlight) {
                cardElementToHighlight.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => {
                    cardElementToHighlight.classList.remove('highlighted-card');
                }, 3000);
            }
        }

        document.querySelectorAll('.card .card-body').forEach(cardBody => {
            cardBody.addEventListener('click', (event) => {
                const isButton = event.target.closest('.btn');
                if (isButton) return; 

                const front = cardBody.querySelector('.card-front');
                const back = cardBody.querySelector('.card-back');

                front.classList.toggle('d-none');
                back.classList.toggle('d-none');
            });
        });

        

    } catch (error) {
        console.error('Failed to load cards for deck:', error);
        cardsContainer.innerHTML = '<p class="text-danger text-center w-100 mt-5">Failed to load cards. Please try again.</p>';
    }
    
}

async function renderCategoriesList(modalBodyElement) {
    const categoryListUl = modalBodyElement.querySelector('#categoryList');
    
    if (!categoryListUl) { 
        console.error('Category list UL not found in modalBodyElement.');
        return;
    }

    try {
        const[customCategories,defaultCategories] = await getCategories()
        
        // Clear previous content
        categoryListUl.innerHTML = '';
                // Render Custom Categories section
                console.log(defaultCategories)
        if (customCategories.length > 0) {
            const customHeader = document.createElement('li');
            customHeader.className = 'list-group-item bg-dark text-info border-secondary mb-2 rounded fw-bold';
            customHeader.textContent = 'Your Custom Categories';
            categoryListUl.appendChild(customHeader);

            customCategories.forEach(cat => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center bg-secondary text-white border-secondary mb-2 rounded';
                li.innerHTML = `
                    <span>${escapeHtml(cat.name)}</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2 edit-category-btn" data-category-id="${cat.id}" data-category-name="${escapeHtml(cat.name)}" title="Edit Category">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-category-btn" data-category-id="${cat.id}" title="Delete Category">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                `;
                categoryListUl.appendChild(li);
            });
        } else {
            const noCustomMessage = document.createElement('li');
            noCustomMessage.className = 'list-group-item bg-secondary text-white-50 border-secondary mb-2 rounded';
            noCustomMessage.textContent = 'No custom categories yet.';
            categoryListUl.appendChild(noCustomMessage);
        }

        // Render Default Categories section
        if (defaultCategories.length > 0) {
            const defaultHeader = document.createElement('li');
            defaultHeader.className = 'list-group-item bg-dark text-info border-secondary mt-3 mb-2 rounded fw-bold';
            defaultHeader.textContent = 'Default Categories';
            categoryListUl.appendChild(defaultHeader);

            defaultCategories.forEach(cat => {
                const li = document.createElement('li');
                li.className = 'list-group-item bg-secondary text-white border-secondary mb-2 rounded';
                li.textContent = escapeHtml(cat.name); 
                li.setAttribute("data-category-id", cat.id);
                li.setAttribute("data-category-name", cat.name);
                categoryListUl.appendChild(li);
            });
        }

        // Handle case where both custom and default are empty
        if (customCategories.length === 0 && defaultCategories.length === 0) {
            categoryListUl.innerHTML = '<li class="list-group-item bg-secondary text-white-50 border-secondary">No categories found.</li>';
        }

    } catch (error) {
        console.error('Failed to load categories:', error);
        categoryListUl.innerHTML = '<p class="text-danger text-center w-100 mt-5">Failed to load categories. Please try again.</p>';
    }
}

function renderSingleCardView(card, deck) {
    const mainCenterDisplay = document.getElementById('mainCenterDisplay');
    if (!mainCenterDisplay) return;

    mainCenterDisplay.innerHTML = ''; 

    const singleCardUI = document.createElement('div');
    singleCardUI.className = 'single-card-container d-flex flex-column align-items-center justify-content-center flex-grow-1';
    singleCardUI.innerHTML = `
        <!-- Back to Deck Button -->
        <div class="w-100 text-start mb-3">
            <button type="button" class="btn btn-outline-secondary" id="backToDeckBtn" data-deck-id="${deck.id}">
                <span class="material-symbols-outlined">arrow_back</span> Back to ${escapeHtml(deck.deckName)}
            </button>
        </div>

        <!-- Single Card Display -->
        <div class="study-card shadow-lg p-4 mb-4 flex-grow-1 d-flex flex-column justify-content-center align-items-center" style="width: 100%; max-width: 600px;">
            <div class="study-card-inner">
                <div class="study-card-front">${escapeHtml(card.frontContent)}</div>
                <div class="study-card-back">
                    ${escapeHtml(card.backContent)}
                    <div class="card-actions-overlay">
                        <button type="button" class="btn btn-sm btn-outline-secondary edit-card-btn" data-card-id="${card.id}" data-deck-id="${deck.id}" title="Edit Card">
                            <span class="material-symbols-outlined">edit</span> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-card-btn" data-card-id="${card.id}" data-deck-id="${deck.id}" title="Delete Card">
                            <span class="material-symbols-outlined">delete</span> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Show Answer Button -->
        <button type="button" class="btn btn-primary mb-3" id="showAnswerBtn">Show Answer</button>
    `;
    mainCenterDisplay.appendChild(singleCardUI);

    const studyCardElement = singleCardUI.querySelector('.study-card');
    const studyCardInnerElement = singleCardUI.querySelector('.study-card-inner');
    const showAnswerBtn = singleCardUI.querySelector('#showAnswerBtn');

    // Initial state: card front visible
    studyCardInnerElement.classList.remove('flipped');
    showAnswerBtn.classList.remove('d-none');

    // Click on card to flip
    studyCardElement.addEventListener('click', function() {
        studyCardInnerElement.classList.toggle('flipped');
        if (studyCardInnerElement.classList.contains('flipped')) {
            showAnswerBtn.classList.add('d-none');
        } else {
            showAnswerBtn.classList.remove('d-none');
        }
    });

    // Click on "Show Answer" button to flip
    showAnswerBtn.addEventListener('click', function() {
        studyCardInnerElement.classList.add('flipped');
        showAnswerBtn.classList.add('d-none');
    });

    // Event listener for Back to Deck button
    singleCardUI.querySelector('#backToDeckBtn').addEventListener('click', async () => {
        await renderCardsList(deck.id);
    });

    // Event listeners for Edit/Delete buttons within this view (delegated)
    singleCardUI.addEventListener('click', async (event) => {
        if (event.target.closest('.edit-card-btn')) {
            const btn = event.target.closest('.edit-card-btn');
            const cardId = btn.dataset.cardId;
            const deckId = btn.dataset.deckId;
            const cardToEdit = cards.find(c => c.id == cardId); 
            if (cardToEdit) {
                openCardModal(deckId, cardToEdit);
            }
        }

        if (event.target.closest('.delete-card-btn')) {
            const btn = event.target.closest('.delete-card-btn');
            const cardId = btn.dataset.cardId;
            const deckId = btn.dataset.deckId;
            if (confirm('Are you sure you want to delete this card?')) {
                try {
                    const result = await fetchData('/api/cards/delete', 'POST', { id: cardId, deck_id: deckId });
                    if (result.success) {
                        showSystemMessage(result.message, 'info');
                        // After deleting, check if there are other cards in the deck
                        const remainingCards = cards.filter(c => c.id != cardId);
                        if (remainingCards.length > 0) {
                            // If cards remain, re-render the deck's card list
                            await renderCardsList(deckId);
                        } else {
                            // If no cards remain, go back to the deck list view
                            currentDeckId = null;
                            await renderDecksList();
                            document.getElementById('mainCenterDisplay').innerHTML = `
                                <div id="welcomeMessage" class="text-center text-white-50 mt-5">
                                    <h2 class="text-info">Welcome to Flashcards V1.0!</h2>
                                    <p class="lead">Select a deck from the left to view cards or start studying.</p>
                                    <p>Use the navigation bar to add new decks or categories.</p>
                                </div>
                            `;
                        }
                    } else {
                        showSystemMessage(result.message, 'error', false);
                    }
                } catch (error) {
                    // Handled by fetchData
                }
            }
        }
    });
}

// --- Modal Instances ---
let deckModal;
let cardModal;
let categoryModal;

// Initialize Bootstrap Modals 
document.addEventListener('DOMContentLoaded', () => {
    const deckModalElement = document.getElementById('deckModal');
    if (deckModalElement) {
        deckModal = new bootstrap.Modal(deckModalElement);
    }

    const cardModalElement = document.getElementById('cardModal');
    if (cardModalElement) {
        cardModal = new bootstrap.Modal(cardModalElement);
    }

    const categoryModalElement = document.getElementById('categoryModal');
    if (categoryModalElement) {
        categoryModal = new bootstrap.Modal(categoryModalElement);
    }
});


// Deck help functions

function getDeckNameById(data, id) {
  const item = data.find(item => item.id === id);
  return item ? item.name : null; 
}

async function getDeckById(deckId) {
    // Validate response structure
    try{
        const data = await fetchData(`/api/decks/get?deckId=${deckId}`);

        if (!data || !data.deck) {
            throw {
                type: 'INVALID_RESPONSE',
                message: 'Server returned an invalid deck format'
            };
        }

        // Check for empty deck
        if (data.deck.length === 0) {
            return {
               
                error: {
                    type: 'EMPTY_DECK',
                    message: 'No cards in this deck'
                },
            };
        }

        return data.deck[0]
      

    } catch (error) {
        console.error('Deck Loading Error:', error);
        
        // Handle different error types
        let userMessage = 'Failed to load deck';
        if (error.type === 'EMPTY_DECK') {
            userMessage = 'This deck is empty';
        } else if (error.type === 'API_ERROR') {
            userMessage = 'Server error: ' + error.message;
        }
        
    }
}

async function deleteDeckById(deckId) {
    // Validate response structure
    try{
        const data = await fetchData(`/api/decks/delete?deckId=${deckId}`); 

        

        // Check for success
        if (!data.success) {
            throw {
                type: 'Unsuccessfull attempt',
                message: 'Server could not delete the Deck'
            };
        }

        return {
            success: true,
            message: 'The Deck was successfully deleted.'
        };
      

    } catch (error) {
        console.error('Deck Delete Error:', error);
        
        // Handle different error types
        const userMessage = 'Failed to delete deck';
        showSystemMessage(`Error: ${userMessage}`, 'error', false);
        
    }
}

async function getCategories() {
    const data = await fetchData('/api/categories/get');
    if (!data) {
        throw {
                type: 'INVALID_REQUEST',
                message: 'User must be signed in to have access'
            };
    }
    const customCategories = data.categories.custom ?? [];
    const defaultCategories = data.categories.default ?? [];
    categories = [
        ...customCategories.map(c => ({ id: c.id, name: c.name })),
        ...defaultCategories.map(d => ({ id: d.id, name: d.name }))
    ];
    console.log(categories)

    return [customCategories, defaultCategories];
}

async function getDecks() {
    const deckslist = await fetchData('/api/decks/get');
    return deckslist
}
       
// --- Modal Handlers ---

function openDeckModal(deck = null) {
    if (isGuest) {
        showSystemMessage('Please log in to add/edit decks.', 'info', true);
        return;
    }
    const deckModalLabel = document.getElementById('deckModalLabel');
    const deckIdInput = document.getElementById('deckId');
    const deckNameInput = document.getElementById('deckName');
    const deckCategorySelect = document.getElementById('deckCategory');
    const deckDescriptionInput = document.getElementById('deckDescription');


    // Populate categories dropdown
    deckCategorySelect.innerHTML = categories.map(cat => `<option value="${cat.id}">${escapeHtml(cat.name)}</option>`).join('');
    if (categories.length === 0) {
        deckCategorySelect.innerHTML = '<option value="">No categories available. Add one first!</option>';
        deckCategorySelect.disabled = true;
        showSystemMessage('No categories found. Please add a category first.', 'info', false);
    } else {
        deckCategorySelect.disabled = false;
    }

    if (deck) {

        deckModalLabel.textContent = 'Edit Deck';
        deckIdInput.value = deck.id;
        deckNameInput.value = deck.deckName;
        deckDescriptionInput.value = deck.deckDescription
        deckCategorySelect.value = deck.category_id; 
    } else {
        deckModalLabel.textContent = 'Add New Deck';
        deckIdInput.value = '';
        deckNameInput.value = '';
        deckDescriptionInput.value = '';
        if (categories.length > 0) deckCategorySelect.value = categories[0].id;
    }
    deckModal.show();
}

function openCardModal(deckId, card = null) {
    if (isGuest) {
        showSystemMessage('Please log in to add/edit cards.', 'info', true);
        return;
    }
    const cardModalLabel = document.getElementById('cardModalLabel');
    const cardIdInput = document.getElementById('cardId');
    const cardDeckIdInput = document.getElementById('cardDeckId');
    const cardFrontInput = document.getElementById('cardFront');
    const cardBackInput = document.getElementById('cardBack');

    cardDeckIdInput.value = deckId; 

    if (card) {
        cardModalLabel.textContent = 'Edit Card';
        cardIdInput.value = card.id;
        cardFrontInput.value = card.frontContent;
        cardBackInput.value = card.backContent;
    } else {
        cardModalLabel.textContent = 'Add New Card';
        cardIdInput.value = '';
        cardFrontInput.value = '';
        cardBackInput.value = '';
    }
    cardModal.show();
}

function openCategoryModal() {
    if (isGuest) {
        showSystemMessage('Please log in to manage categories.', 'info', true);
        return;
    }
    // Corrected: Ensure the correct modal body element is passed
    renderCategoriesList(document.getElementById('categoryModal').querySelector('.modal-body'));
    categoryModal.show();
}

// --- Form Submission Handlers ---

document.getElementById('deckForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const deckId = document.getElementById('deckId').value;
    const deckName = document.getElementById('deckName').value;
    const deckCategory = document.getElementById('deckCategory').value;
    const deckDescription = document.getElementById('deckDescription').value;

    if (!deckName || !deckCategory) {
        showSystemMessage('Deck name and category are required.', 'error', true);
        return;
    }

    const deckData = {
        id: deckId || undefined, 
        name: deckName,
        category_id: deckCategory,
        description: deckDescription
    };

    let url = deckId ? '/api/decks/edit.php' : '/api/decks/create.php'; 

    try {
        const result = await fetchData(url, 'POST', deckData);
        if (result.success) {
            showSystemMessage(result.message, 'info');
            deckModal.hide();
            await renderDecksList(); 
            if (!deckId) { 
                const newDeck = result.deck; 
                if (newDeck) {
                    currentDeckId = newDeck.id;
                    document.querySelector(`.deck-item[data-deck-id="${currentDeckId}"]`)?.classList.add('active');
                    renderCardsList(currentDeckId); 
                }
            }
        } else {
            showSystemMessage(result.message, 'error', false);
        }
    } catch (error) {
        // Error handled by fetchData
    }
});

document.getElementById('cardForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const cardId = document.getElementById('cardId').value;
    const cardDeckId = document.getElementById('cardDeckId').value;
    const cardFront = document.getElementById('cardFront').value;
    const cardBack = document.getElementById('cardBack').value;

    if (!cardFront || !cardBack) {
        showSystemMessage('Card front and back content are required.', 'error', true);
        return;
    }

    const cardData = {
        id: cardId || undefined,
        deck_id: cardDeckId,
        frontContent: cardFront,
        backContent: cardBack
    };

    let url = cardId ? '/api/cards/edit.php' : '/api/cards/create.php'; 

    try {
        const result = await fetchData(url, 'POST', cardData);
        if (result.success) {
            showSystemMessage(result.message, 'info');
            cardModal.hide();
            if (currentDeckId) {
                await renderCardsList(currentDeckId); 
            }
        } else {
            showSystemMessage(result.message, 'error', false);
        }
    } catch (error) {
        // Error handled by fetchData
    }
});

document.getElementById('addCategoryForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const newCategoryNameInput = document.getElementById('newCategoryName');
    const newCategoryName = newCategoryNameInput.value.trim();

    if (!newCategoryName) {
        showSystemMessage('Category name cannot be empty.', 'error', true);
        return;
    }

    try {
        const result = await fetchData('/api/categories/create.php', 'POST', { name: newCategoryName }); 
        if (result.success) {
            showSystemMessage(result.message, 'info');
            newCategoryNameInput.value = '';
            await renderCategoriesList(document.getElementById('categoryModal').querySelector('.modal-body')); 
            // Also re-render deck modal categories if it's open
            if (document.getElementById('deckModal').classList.contains('show')) {
                const deckCategorySelect = document.getElementById('deckCategory');
                deckCategorySelect.innerHTML = categories.map(cat => `<option value="${cat.id}">${escapeHtml(cat.name)}</option>`).join('');
                deckCategorySelect.disabled = false;
            }
        } else {
            showSystemMessage(result.message, 'error', false);
        }
    } catch (error) {
        // Error handled by fetchData
    }
});

// --- Study Mode Functions ---

function renderStudyMode() {
    const mainCenterDisplay = document.getElementById('mainCenterDisplay');
    if (!mainCenterDisplay) return; 

    mainCenterDisplay.innerHTML = '';

    if (studyMode.cards.length === 0) {
        mainCenterDisplay.innerHTML = '<p class="text-white-50 text-center mt-5">No cards to study in this deck.</p>';
        return;
    }

    const currentCard = studyMode.cards[studyMode.currentIndex];
    const totalCards = studyMode.cards.length;
    const currentCardNumber = studyMode.currentIndex + 1;

    // Determine if Previous/Next buttons should be disabled
    const isPrevDisabled = studyMode.currentIndex === 0;
    const isNextDisabled = studyMode.currentIndex === totalCards - 1;


    const studyUI = document.createElement('div');
    studyUI.className = 'study-container d-flex flex-column align-items-center justify-content-between flex-grow-1';
    studyUI.innerHTML = `
        <!-- Card Counter -->
        <div class="card-counter text-white-50 mb-3">
            Card ${currentCardNumber} of ${totalCards}
        </div>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-between w-100 mb-3 px-md-5">
            <button type="button" class="btn btn-outline-info study-nav-btn" id="prevCardBtn" ${isPrevDisabled ? 'disabled' : ''}>
                <span class="material-symbols-outlined">arrow_back</span> Previous
            </button>
            <button type="button" class="btn btn-outline-info study-nav-btn" id="nextCardBtn" ${isNextDisabled ? 'disabled' : ''}>
                Next <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </div>

        <!-- Study Card -->
        <div class="study-card shadow-lg p-4 mb-4 flex-grow-1 d-flex flex-column justify-content-center align-items-center" style="width: 100%; max-width: 600px;">
            <div class="study-card-inner">
                <div class="study-card-front">${escapeHtml(currentCard.frontContent)}</div>
                <div class="study-card-back">
                    ${escapeHtml(currentCard.backContent)}
                    <div class="card-actions-overlay">
                        <button type="button" class="btn btn-sm btn-outline-secondary edit-card-btn" data-card-id="${currentCard.id}" data-deck-id="${studyMode.deckId}" title="Edit Card">
                            <span class="material-symbols-outlined">edit</span> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-card-btn" data-card-id="${currentCard.id}" data-deck-id="${studyMode.deckId}" title="Delete Card">
                            <span class="material-symbols-outlined">delete</span> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Show Answer Button (Initially visible) -->
        <button type="button" class="btn btn-primary mb-3" id="showAnswerBtn">Show Answer</button>

        <!-- Rating System (Hidden initially, shown after "Show Answer") -->
        <div class="rating-controls d-flex justify-content-center mb-3 d-none" id="ratingControls">
            <span class="text-white-50 me-2">How difficult was this card?</span>
            <button type="button" class="btn btn-outline-warning rating-btn me-1" data-rating="1"><span class="material-symbols-outlined">star</span></button>
            <button type="button" class="btn btn-outline-warning rating-btn me-1" data-rating="2"><span class="material-symbols-outlined">star</span></button>
            <button type="button" class="btn btn-outline-warning rating-btn me-1" data-rating="3"><span class="material-symbols-outlined">star</span></button>
            <button type="button" class="btn btn-outline-warning rating-btn me-1" data-rating="4"><span class="material-symbols-outlined">star</span></button>
            <button type="button" class="btn btn-outline-warning rating-btn" data-rating="5"><span class="material-symbols-outlined">star</span></button>
        </div>

        <!-- Exit Study Button -->
        <button type="button" class="btn btn-outline-secondary mt-3" id="exitStudyBtn">Exit Study</button>
    `;
    mainCenterDisplay.appendChild(studyUI);

    // Event listener for card flip (click anywhere on the card)
    const studyCardElement = studyUI.querySelector('.study-card');
    const studyCardInnerElement = studyUI.querySelector('.study-card-inner');
    const showAnswerBtn = studyUI.querySelector('#showAnswerBtn');
    const ratingControls = studyUI.querySelector('#ratingControls');

    // Initial state: back is hidden, rating controls are hidden
    studyCardInnerElement.classList.remove('flipped'); 
    showAnswerBtn.classList.remove('d-none');
    ratingControls.classList.add('d-none');

    // Click on card to flip
    studyCardElement.addEventListener('click', function() {
        studyCardInnerElement.classList.toggle('flipped');
        // If flipped to back, show rating controls and hide show answer button
        if (studyCardInnerElement.classList.contains('flipped')) {
            showAnswerBtn.classList.add('d-none');
            ratingControls.classList.remove('d-none');
        } else {
            // If flipped back to front, hide rating controls and show show answer button
            showAnswerBtn.classList.remove('d-none');
            ratingControls.classList.add('d-none');
        }
    });

    // Click on "Show Answer" button to flip
    showAnswerBtn.addEventListener('click', function() {
        studyCardInnerElement.classList.add('flipped');
        showAnswerBtn.classList.add('d-none');
        ratingControls.classList.remove('d-none');
    });


    // Event listeners for rating buttons (delegated to rating-controls container)
    ratingControls.addEventListener('click', async (event) => {
        const ratingBtn = event.target.closest('.rating-btn');
        if (ratingBtn) {
            const rating = parseInt(ratingBtn.dataset.rating, 10);
            await scoreCard(rating);
        }
    });

    // Event listeners for navigation buttons
    studyUI.querySelector('#prevCardBtn').addEventListener('click', () => {
        if (studyMode.currentIndex > 0) {
            studyMode.currentIndex--;
            renderStudyMode(); // Re-render for previous card
        }
    });

    // Event listeners for Edit/Delete buttons within study mode (delegated)
    studyUI.addEventListener('click', async (event) => {
        if (event.target.closest('.edit-card-btn')) {
            const btn = event.target.closest('.edit-card-btn');
            const cardId = btn.dataset.cardId;
            const deckId = btn.dataset.deckId;
            const cardToEdit = studyMode.cards.find(c => c.id == cardId); 
            if (cardToEdit) {
                openCardModal(deckId, cardToEdit);
            }
        }

        if (event.target.closest('.delete-card-btn')) {
            const btn = event.target.closest('.delete-card-btn');
            const cardId = btn.dataset.cardId;
            const deckId = btn.dataset.deckId;
            if (confirm('Are you sure you want to delete this card?')) {
                try {
                    const result = await fetchData('/api/cards/delete', 'POST', { id: cardId , deck_id: deckId});
                    if (result.success) {
                        showSystemMessage(result.message, 'info');
                        // Remove the card from studyMode.cards
                        studyMode.cards = studyMode.cards.filter(c => c.id != cardId);

                        if (studyMode.cards.length > 0) {
                            // Adjust index if current card was deleted and it was the last one
                            if (studyMode.currentIndex >= studyMode.cards.length) {
                                studyMode.currentIndex = studyMode.cards.length - 1;
                            }
                            renderStudyMode();
                        } else {
                            // If no cards left, exit study mode
                            showSystemMessage('All cards deleted from this deck. Exiting study mode.', 'info', false);
                            studyMode.active = false;
                            currentDeckId = null;
                            await renderDecksList(); 
                            if (document.getElementById('mainCenterDisplay')) {
                                document.getElementById('mainCenterDisplay').innerHTML = `
                                    <div id="welcomeMessage" class="text-center text-white-50 mt-5">
                                        <h2 class="text-info">Welcome to Flashcards V1.0!</h2>
                                        <p class="lead">Select a deck from the left to view cards or start studying.</p>
                                        <p>Use the navigation bar to add new decks or categories.</p>
                                    </div>
                                `;
                            }
                        }
                    } else {
                        showSystemMessage(result.message, 'error', false);
                    }
                } catch (error) {
                    // Handled by fetchData
                }
            }
        }
    });

    studyUI.querySelector('#nextCardBtn').addEventListener('click', () => {
        if (studyMode.currentIndex < studyMode.cards.length - 1) {
            studyMode.currentIndex++;
            renderStudyMode(); 
        }
    });
}

async function startStudyMode(deckId) {
    if (isGuest) {
        showSystemMessage('Please log in to use study mode.', 'info', true);
        return;
    }
    try {
        const data = await fetchData(`/api/cards/get?deckId=${deckId}`);
        studyMode.cards = data.cards || [];
        studyMode.deckId = deckId;
        studyMode.currentIndex = 0;
        studyMode.active = true;

        if (studyMode.cards.length === 0) {
            showSystemMessage('This deck has no cards to study.', 'info', true);
            return;
        }

        // Shuffle cards if desired (original script had a shuffle button)
        studyMode.cards = shuffleArray(studyMode.cards);

        renderStudyMode();
    } catch (error) {
        console.error('Failed to start study mode:', error);
    }
}

async function scoreCard(rating) {
    const currentCard = studyMode.cards[studyMode.currentIndex];
    if (!currentCard) return;

    try {
        const result = await fetchData('/api/cards/rate', 'POST', {
            card_id: currentCard.id,
            rating: rating
        });
        if (result.success) {
            showSystemMessage('Card scored!', 'info', true);
        }
    } catch (error) {
        // Error handled by fetchData
    }

    studyMode.currentIndex++;
    if (studyMode.currentIndex < studyMode.cards.length) {
        renderStudyMode(); // Render next card
    } else {
        showSystemMessage('You have completed this deck!', 'info', false);
        studyMode.active = false;
        currentDeckId = null; // Reset current deck
        if (document.getElementById('mainCenterDisplay')) { // Check if element exists
            document.getElementById('mainCenterDisplay').innerHTML = `
                <div id="welcomeMessage" class="text-center text-white-50 mt-5">
                    <h2 class="text-info">Study Session Complete!</h2>
                    <p class="lead">Great job! Select another deck or create new cards.</p>
                </div>
            `;
        }
    }
}

async function startExam(deckId) {
    try{
        const deck = decks.find(d => d.id == deckId);
        if (!deck) return showSystemMessage('Deck not found.', 'error');

        const response = await fetchData(`/api/cards/get?deckId=${deckId}`);
        if (!response.success) {
            throw {
                type: 'Unsuccessfull attempt',
                message: 'Could not get the cards.'
            };
        }
        
        const cards = response.cards;

        if (!cards || cards.length < 4) {
            return showSystemMessage('Not enough cards for an exam (min 4 needed).', 'error');
        }

        let index = 0;
        let score = 0;
        const total = cards.length;
        const recordBefore = deck.record || 0;

        // Shuffle the cards to randomize question order
        const shuffledCards = [...cards].sort(() => 0.5 - Math.random());

        const showQuestion = () => {
            const currentCard = shuffledCards[index];
            const correctAnswer = currentCard.backContent;

            // Get 3 incorrect answers from other cards
            const wrongAnswers = cards
                .filter(c => c.id !== currentCard.id)
                .map(c => c.backContent)
                .sort(() => 0.5 - Math.random())
                .slice(0, 3);
            console.log(wrongAnswers)

            const options = [...wrongAnswers, correctAnswer].sort(() => 0.5 - Math.random());
            console.log(options)
            // Render question
            mainCenterDisplay.innerHTML = `
                <div class="exam-question card-glass p-4 text-center">
                    <h4 class="text-info mb-4">Question ${index + 1} of ${total}</h4>
                    <p class="fs-4 mb-4">${escapeHtml(currentCard.frontContent)}</p>
                    <div class="d-grid gap-3">
                        ${options.map(option => `
                            <button class="btn btn-outline-light exam-option">${escapeHtml(option)}</button>
                        `).join('')}
                    </div>
                </div>
            `;

            // Add event listeners
            document.querySelectorAll('.exam-option').forEach(btn => {
                btn.addEventListener('click', () => {
                    const isCorrect = btn.textContent.trim() === correctAnswer.trim();
                    if (isCorrect) score++;
                    index++;
                    if (index < total) {
                        showQuestion();
                    } else {
                        showResults();
                    }
                });
            });
        };

        const showResults = async () => {
            const newRecord = Math.round((score / total) * 100);

            // Update record only if improved
            if (newRecord > recordBefore) {
                await fetchData('/api/decks/updateRecord', 'POST', {
                    deck_id: deckId,
                    record: newRecord
                });
            }

            mainCenterDisplay.innerHTML = `
                <div class="exam-results card-glass text-center p-4">
                    <h3 class="text-success">Exam Complete!</h3>
                    <p class="lead">Score: <strong>${score} / ${total}</strong></p>
                    <p>Previous Record: <strong>${recordBefore}%</strong></p>
                    <p>New Record: <strong>${Math.max(recordBefore, newRecord)}%</strong></p>
                    <button class="btn btn-outline-info mt-3" onclick="renderDeckSpecs('${deckId}')">
                        <span class="material-symbols-outlined">arrow_back</span> Back to Deck
                    </button>
                </div>
            `;
        };

        showQuestion();
    }catch(error){
        console.error('Exam failed:', error);
        showSystemMessage('Failed to start exam. Please try again.', 'error', true);
    }
}   

// --- Event Listeners ---

document.addEventListener('DOMContentLoaded', async () => {
    
    //Initial decks and categories population
    if (!isGuest) {
        await getCategories();
        await renderDecksList(); 
    }


    renderNavbar(isGuest,userName); // Initial navbar render

    
    // --- Navbar & Global Buttons ---
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    if (addCategoryBtn) addCategoryBtn.addEventListener('click', () => openCategoryModal());
    
    const manageCategoriesBtn = document.getElementById('manageCategoriesBtn');
    if (manageCategoriesBtn) manageCategoriesBtn.addEventListener('click', () => openCategoryModal());

    const addDeckBtn = document.getElementById('addDeckBtn');
    if (addDeckBtn) addDeckBtn.addEventListener('click', () => openDeckModal());

    const addDeckBtnSmall = document.getElementById('addDeckBtnSmall');
    if (addDeckBtnSmall) addDeckBtnSmall.addEventListener('click', () => openDeckModal()); 

    const manageDecksBtn = document.getElementById('manageDecksBtn');
    if (manageDecksBtn) manageDecksBtn.addEventListener('click', () => {
        showSystemMessage('Manage Decks functionality coming soon!', 'info', true);
    });
    
    const learnDeckBtn = document.getElementById('learnDeckBtn');
    if (learnDeckBtn) learnDeckBtn.addEventListener('click', () => {
        if (currentDeckId) {
            startStudyMode(currentDeckId);
        } else {
            showSystemMessage('Please select a deck from the left panel to start studying.', 'info', true);
        }
    });

    const takeTestBtn = document.getElementById('takeTestBtn');
    if (takeTestBtn) takeTestBtn.addEventListener('click', () => {
        if (currentDeckId) {
            startExam(currentDeckId);
        } else {
            showSystemMessage('Please select a deck from the left panel to start an exam.', 'info', true);
        }
    });

    // Logout button (delegated to body)
    document.body.addEventListener('click', async function(event) {
        if (event.target.classList.contains('logoutBtn')) {
            event.preventDefault();
            try {
              
                const result = await fetchData('/api/users/logout', 'POST'); 
                
                if (result.success) {
                    showSystemMessage(result.message, 'info');
                    isGuest = true;
                    userName = 'guest';
                    renderNavbar(isGuest, userName);
                    await renderDecksList(); 
                    if (document.getElementById('mainCenterDisplay')) {
                        document.getElementById('mainCenterDisplay').innerHTML = `
                            <div id="welcomeMessage" class="text-center text-white-50 mt-5">
                                <h2 class="text-info">Welcome to Flashcards V1.0!</h2>
                                <p class="lead">Select a deck from the left to view cards or start studying.</p>
                                <p>Use the navigation bar to add new decks or categories.</p>
                            </div>
                        `; 
                    }
                    currentDeckId = null;
                    window.location.href = 'index.php'; 
                } else {
                    showSystemMessage(result.message, 'error', false);
                }
            } catch (error) {
                console.error('Logout error:', error);
            }
        }
    });

    // --- Deck List Interactions (Delegation) ---
    const decksNameList = document.getElementById('decksNameList');
    if (decksNameList) {
        decksNameList.addEventListener('click', async (event) => {
            const deckItem = event.target.closest('.deck-item');
            if (!deckItem) return;

            const deckId = deckItem.dataset.deckId;

            // Handle deck selection (view cards)
            if (event.target.tagName === 'H6' || event.target.classList.contains('deck-item')) {
                // Remove active class from all other decks
                document.querySelectorAll('.deck-item').forEach(item => item.classList.remove('active'));
                // Add active class to the clicked deck
                deckItem.classList.add('active');

                currentDeckId = deckId;
                // await renderCardsList(deckId); // Render cards for the selected deck
                renderDeckSpecs(deckId);
            }

        });
    }


    // --- Middle Section Interactions (Delegation) ---
    const mainCenterDisplay = document.getElementById('mainCenterDisplay');
    if (mainCenterDisplay) {
        mainCenterDisplay.addEventListener('click', async (event) => {
            // Add Card Button
            if (event.target.closest('.add-card-to-deck-btn')) {
                const deckId = event.target.closest('.add-card-to-deck-btn').dataset.deckId;
                openCardModal(deckId);
            }

            // Start Study Button
            if (event.target.closest('.study-deck-btn')) {
                const deckId = event.target.closest('.study-deck-btn').dataset.deckId;
                startStudyMode(deckId);
            }

            // Start Exam Button
            if (event.target.closest('.quiz-deck-btn')) {
                const deckId = event.target.closest('.quiz-deck-btn').dataset.deckId;
                startExam(deckId);
            }

            // Handle Edit Deck button
            if (event.target.closest('.edit-deck-btn')) {
                const deckId = event.target.closest('.edit-deck-btn').dataset.deckId;
                const deckToEdit = await getDeckById(deckId);
                if (deckToEdit) {
                    openDeckModal(deckToEdit);
                }
            }

            // Handle Delete Deck button
            if (event.target.closest('.delete-deck-btn')) {
                if (confirm('Are you sure you want to delete this deck and all its cards?')) {
                    try {
                        const deckId = event.target.closest('.delete-deck-btn').dataset.deckId;
                        const result = await deleteDeckById(deckId)
                        if (result.success) {
                            //update decks
                            decks = decks.filter(deck => deck.deck_id !== deckId);
                            showSystemMessage(result.message, 'info');
                            await renderDecksList(); // Re-render decks
                            if (document.getElementById('mainCenterDisplay')) { // Check if element exists
                                document.getElementById('mainCenterDisplay').innerHTML = `
                                    <div id="welcomeMessage" class="text-center text-white-50 mt-5">
                                        <h2 class="text-info">Welcome to Flashcards V1.0!</h2>
                                        <p class="lead">Select a deck from the left to view cards or start studying.</p>
                                        <p>Use the navigation bar to add new decks or categories.</p>
                                    </div>
                                `; // Clear center display
                            }
                            currentDeckId = null;
                        } else {
                            showSystemMessage(result.message, 'error', false);
                        }
                    } catch (error) {
                        // Error handled by fetchData
                    }
                }
            }

            // Exit Study Button
            if (studyMode.active && event.target.id === 'exitStudyBtn') {
                studyMode.active = false;
                currentDeckId = null; 
                if (document.getElementById('mainCenterDisplay')) { 
                    document.getElementById('mainCenterDisplay').innerHTML = `
                        <div id="welcomeMessage" class="text-center text-white-50 mt-5">
                            <h2 class="text-info">Welcome to Flashcards V1.0!</h2>
                            <p class="lead">Select a deck from the left to view cards or start studying.</p>
                            <p>Use the navigation bar to add new decks or categories.</p>
                        </div>
                    `;
                }
                showSystemMessage('Study session ended.', 'info', true);
            }

            // Handle clicking on a search result deck
            if (event.target.closest('.search-result-deck')) {
                event.preventDefault(); 
                const clickedDeckElement = event.target.closest('.search-result-deck');
                const deckId = clickedDeckElement.dataset.deckId;

                const deckItemInLeftPanel = document.querySelector(`.deck-item[data-deck-id="${deckId}"]`);
                if (deckItemInLeftPanel) {
                    // Remove active class from all other decks
                    document.querySelectorAll('.deck-item').forEach(item => item.classList.remove('active'));
                    // Add active class to the clicked deck
                    deckItemInLeftPanel.classList.add('active');

                    currentDeckId = deckId;
                    await renderCardsList(deckId);
                } else {
                    showSystemMessage('Deck not found in your list. Displaying cards if available.', 'info', true);
                    currentDeckId = deckId;
                    await renderCardsList(deckId); 
                }
            }

            // Handle clicking on a search result card
            if (event.target.closest('.search-result-card')) {
                const clickedCardElement = event.target.closest('.search-result-card');
                const cardId = clickedCardElement.dataset.cardId;
                const deckId = clickedCardElement.dataset.deckId;

                // First, select the associated deck in the left panel
                const deckItemInLeftPanel = document.querySelector(`.deck-item[data-deck-id="${deckId}"]`);
                if (deckItemInLeftPanel) {
                    document.querySelectorAll('.deck-item').forEach(item => item.classList.remove('active'));
                    deckItemInLeftPanel.classList.add('active');
                    currentDeckId = deckId;
                    await renderCardsList(deckId); // Render all cards for that deck


                } else {
                    showSystemMessage('Associated deck not found in your list. Displaying cards for this deck.', 'info', true);
                    currentDeckId = deckId;
                    await renderCardsList(deckId);
                }
            }

            // Handle clicking on a card from the main cards list
            if (event.target.closest('.card-clickable')) {
                const clickedCardElement = event.target.closest('.card-clickable');
                const cardId = clickedCardElement.dataset.cardId;
                const deckId = clickedCardElement.dataset.deckId;
                const cardToOpen = cards.find(c => c.id == cardId);

                if (cardToOpen) {
                    const deck = decks.find(d => d.id == deckId);
                    if (deck) {
                        renderSingleCardView(cardToOpen, deck);
                    } else {
                        showSystemMessage('Associated deck not found for this card.', 'error', true);
                    }
                } 
            }

            // Handle clicking on a search result card (delegated to mainCenterDisplay)
            if (event.target.closest('.search-result-card')) {
                const clickedCardElement = event.target.closest('.search-result-card');
                const cardId = clickedCardElement.dataset.cardId;
                const deckId = clickedCardElement.dataset.deckId;

                const cardToOpen = cards.find(c => c.id == cardId); 
                const deck = decks.find(d => d.id == deckId); 

                if (cardToOpen && deck) {
                    // Directly render the single card view
                    renderSingleCardView(cardToOpen, deck);
                    // Also, select the associated deck in the left panel for visual consistency
                    const deckItemInLeftPanel = document.querySelector(`.deck-item[data-deck-id="${deckId}"]`);
                    if (deckItemInLeftPanel) {
                        document.querySelectorAll('.deck-item').forEach(item => item.classList.remove('active'));
                        deckItemInLeftPanel.classList.add('active');
                        currentDeckId = deckId;
                    }
                } else {
                    showSystemMessage('Associated deck or card data not found.', 'error', true);
                }
            }
        });
    }
    

    // --- Category Modal Interactions ---
    const categoryModalEl = document.getElementById('categoryModal');
    if (categoryModalEl) { 
        categoryModalEl.addEventListener('click', async (event) => {
            // Edit Category Button
            if (event.target.closest('.edit-category-btn')) {
                const btn = event.target.closest('.edit-category-btn');
                const categoryId = btn.dataset.categoryId;
                const categoryName = btn.dataset.categoryName;

                const newName = prompt(`Edit category "${categoryName}":`, categoryName);
                if (newName && newName.trim() !== categoryName) {
                    try {
                        const result = await fetchData('/api/categories/edit.php', 'POST', { id: categoryId, name: newName.trim() });
                        if (result.success) {
                            showSystemMessage(result.message, 'info');
                            await renderCategoriesList(document.getElementById('categoryModal').querySelector('.modal-body'));
                            await renderDecksList(); 
                        } else {
                            showSystemMessage(result.message, 'error', false);
                        }
                    } catch (error) {
                        // Handled by fetchData
                    }
                }
            }

            // Delete Category Button
            if (event.target.closest('.delete-category-btn')) {
                const categoryId = event.target.closest('.delete-category-btn').dataset.categoryId;
                
                if (confirm('Are you sure you want to delete this category? All decks in this category will become uncategorized.')) {
                    try {
                        const result = await fetchData('/api/categories/delete.php', 'POST', { id: categoryId });
                        if (result.success) {
                            showSystemMessage(result.message, 'info');
                            await renderCategoriesList(document.getElementById('categoryModal').querySelector('.modal-body'));
                            await renderDecksList(); 
                        } else {
                            showSystemMessage(result.message, 'error', false);
                        }
                    } catch (error) {
                        // Handled by fetchData
                    }
                }
            }
        });
    }

    // --- Search Functionality ---
    const deckSearchInput = document.getElementById('deckSearchInput');
    const searchCardsChkBx = document.getElementById('searchCardsChkBx');
    const searchBtn = document.getElementById('searchBtn');

    if (deckSearchInput && searchCardsChkBx && searchBtn) { 
        async function performSearch() {
            const query = deckSearchInput.value.trim();
            const searchCards = searchCardsChkBx.checked;

            if (query.length < 2 && query.length !== 0) { 
                showSystemMessage('Please enter at least 2 characters to search.', 'info', true);
                return;
            }
            
            if (query.length === 0) {
                await renderDecksList(); 
                if (document.getElementById('mainCenterDisplay')) { 
                    document.getElementById('mainCenterDisplay').innerHTML = `
                        <div id="welcomeMessage" class="text-center text-white-50 mt-5">
                            <h2 class="text-info">Welcome to Flashcards V1.0!</h2>
                            <p class="lead">Select a deck from the left to view cards or start studying.</p>
                            <p>Use the navigation bar to add new decks or categories.</p>
                        </div>
                    `;
                }
                return;
            }

            try {
                const data = await fetchData(`/api/search/get?query=${encodeURIComponent(query)}&searchCards=${searchCards}`);
                
                const foundDecks = data.decks || [];
                const foundCards = data.cards || [];

                const mainCenterDisplay = document.getElementById('mainCenterDisplay');
                if (!mainCenterDisplay) return; 

                mainCenterDisplay.innerHTML = '';

                // Add a main search results header
                const searchHeader = document.createElement('h4');
                searchHeader.className = 'text-info mb-3';
                searchHeader.textContent = `Search Results for "${escapeHtml(query)}"`;
                mainCenterDisplay.appendChild(searchHeader);

                // --- Render Decks Section ---
                const decksSection = document.createElement('div');
                decksSection.className = 'mb-4';
                decksSection.innerHTML = `
                    <h5 class="text-white-50 border-bottom pb-2 mb-3">Decks Found:</h5>
                    <div id="searchResultsDecks" class="list-group"></div>
                `;
                mainCenterDisplay.appendChild(decksSection);
                const searchResultsDecksContainer = decksSection.querySelector('#searchResultsDecks');

                if (foundDecks.length > 0) {
                    // Update global decks array with filtered decks for consistency
                    decks = foundDecks; 
                    renderDecksList(); 

                    foundDecks.forEach(deck => {
                        const deckItem = document.createElement('a');
                        deckItem.href = "#"; 
                        deckItem.className = 'list-group-item list-group-item-action bg-secondary text-white border-secondary mb-2 rounded search-result-deck';
                        deckItem.dataset.deckId = deck.id;
                        deckItem.innerHTML = `
                            <h6 class="mb-1 text-info">${escapeHtml(deck.deckName)}</h6>
                            <p class="mb-1 small text-white-50">${escapeHtml(deck.deckDescription || 'No description.')}</p>
                            <small class="text-muted">Cards: ${deck.totalCards}, Mastery: ${deck.mastery}%</small>
                        `;
                        searchResultsDecksContainer.appendChild(deckItem);
                    });
                } else {
                    searchResultsDecksContainer.innerHTML = '<p class="text-white-50">No decks found matching your search criteria.</p>';
                    decks = []; 
                    renderDecksList(); 
                }

                // --- Render Cards Section (only if searchCards is true) ---
                if (searchCards) {
                    const cardsSection = document.createElement('div');
                    cardsSection.innerHTML = `
                        <h5 class="text-white-50 border-bottom pb-2 mb-3 mt-4">Cards Found:</h5>
                        <div id="searchResultsCards" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3"></div>
                    `;
                    mainCenterDisplay.appendChild(cardsSection);
                    const searchResultsCardsContainer = cardsSection.querySelector('#searchResultsCards');

                    if (foundCards.length > 0) {
                        foundCards.forEach(card => {
                            const cardCol = document.createElement('div');
                            cardCol.className = 'col';
                            cardCol.innerHTML = `
                                <div class="card h-100 shadow-sm search-result-card" data-card-id="${card.id}" data-deck-id="${card.deckId}">
                                    <div class="card-body">
                                        <h5 class="card-title">${escapeHtml(card.frontContent)}</h5>
                                        <p class="card-text text-white-50">${escapeHtml(card.backContent)}</p>
                                        <small class="text-muted">From Deck: ${escapeHtml(foundDecks.find(d => d.id == card.deckId)?.deckName || 'N/A')}</small>
                                    </div>
                                </div>
                            `;
                            searchResultsCardsContainer.appendChild(cardCol);
                        });
                    } else {
                        searchResultsCardsContainer.innerHTML = '<p class="text-white-50 w-100">No cards found matching your search criteria.</p>';
                    }
                }

            } catch (error) {
                console.error('Search failed:', error);
                showSystemMessage('Search failed. Please try again.', 'error', true);
                if (document.getElementById('mainCenterDisplay')) { 
                    document.getElementById('mainCenterDisplay').innerHTML = `
                        <div id="welcomeMessage" class="text-center text-danger mt-5">
                            <h2 class="text-danger">Search Error!</h2>
                            <p class="lead">Failed to perform search. Please check your connection or try again later.</p>
                        </div>
                    `;
                }
                decks = []; 
                renderDecksList(); 
            }
        }

        deckSearchInput.addEventListener('input', performSearch); 
        searchBtn.addEventListener('click', performSearch); 
    }
});