// Solution directe pour les problèmes de lag et de fonctionnalité
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation du gestionnaire direct des boutons');
    
    // Attacher les gestionnaires d'événements via délégation
    document.body.addEventListener('click', handleButtonClick);
    
    // Initialiser les sections
    showSection('overview');
    
    // Configurer les boutons principaux
    setupMainButtons();
});

// Gestionnaire principal pour tous les clics de boutons
function handleButtonClick(event) {
    // Trouver le bouton le plus proche avec data-action
    const button = event.target.closest('[data-action]');
    if (!button) return; // Pas un bouton d'action
    
    event.preventDefault();
    event.stopPropagation();
    
    // Récupérer les informations du bouton
    const action = button.getAttribute('data-action');
    const id = button.getAttribute('data-id');
    
    console.log(`Action déclenchée: ${action}, ID: ${id}`);
    
    // Désactiver le bouton pour éviter les clics multiples
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Gérer l'action en fonction de son type
    try {
        handleAction(action, id, button);
    } catch (error) {
        console.error(`Erreur lors de l'exécution de l'action ${action}:`, error);
        showNotification(`Erreur: ${error.message}`, 'error');
        
        // Restaurer le bouton
        button.disabled = false;
        button.innerHTML = originalHTML;
    }
}

// Traiter les différentes actions
async function handleAction(action, id, buttonElement) {
    switch (action) {
        case 'edit-user':
            try {
                const user = await fetchUserDetailsDirectly(id);
                showUserModal(user);
            } finally {
                restoreButton(buttonElement);
            }
            break;
            
        case 'delete-user':
            showConfirmModal(id, 'user');
            restoreButton(buttonElement);
            break;
            
        case 'edit-reservation':
            try {
                const reservation = await fetchReservationDetailsDirectly(id);
                showReservationModal(reservation);
            } finally {
                restoreButton(buttonElement);
            }
            break;
            
        case 'delete-reservation':
            showConfirmModal(id, 'reservation');
            restoreButton(buttonElement);
            break;
            
        case 'edit-emplacement':
            try {
                const emplacement = await fetchEmplacementDetailsDirectly(id);
                showEmplacementModal(emplacement);
            } finally {
                restoreButton(buttonElement);
            }
            break;
            
        case 'delete-emplacement':
            showConfirmModal(id, 'emplacement');
            restoreButton(buttonElement);
            break;
            
        case 'view-reservation':
            try {
                const resDetails = await fetchReservationDetailsDirectly(id);
                showReservationModal(resDetails, true);
            } finally {
                restoreButton(buttonElement);
            }
            break;
            
        default:
            console.warn(`Action inconnue: ${action}`);
            restoreButton(buttonElement);
    }
}

// Restaurer l'état d'un bouton
function restoreButton(buttonElement) {
    if (!buttonElement) return;
    
    // Récupérer l'icône d'origine en fonction de l'action
    const action = buttonElement.getAttribute('data-action');
    let icon = '';
    
    switch (action) {
        case 'edit-user':
        case 'edit-reservation':
        case 'edit-emplacement':
            icon = '<i class="fas fa-edit"></i>';
            break;
        case 'delete-user':
        case 'delete-reservation':
        case 'delete-emplacement':
            icon = '<i class="fas fa-trash-alt"></i>';
            break;
        case 'view-reservation':
            icon = '<i class="fas fa-eye"></i>';
            break;
        default:
            icon = buttonElement.getAttribute('data-original-icon') || '';
    }
    
    buttonElement.innerHTML = icon;
    buttonElement.disabled = false;
}

// Configurer les boutons principaux (non-dynamiques)
function setupMainButtons() {
    // Boutons d'ajout
    const addButtons = {
        'add-emplacement-btn': () => showEmplacementModal(),
        'add-user-btn': () => showUserModal(),
        'add-reservation-btn': () => showReservationModal()
    };
    
    // Boutons d'export CSV
    const exportButtons = {
        'export-emplacements-csv': () => exportTableToCSV('emplacements', 'emplacements_marsa_maroc.csv'),
        'export-users-csv': () => exportTableToCSV('users', 'utilisateurs_marsa_maroc.csv'),
        'export-reservations-csv': () => exportTableToCSV('reservations', 'reservations_marsa_maroc.csv')
    };
    
    // Configurer les boutons d'ajout
    Object.keys(addButtons).forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                addButtons[btnId]();
            };
        }
    });
    
    // Configurer les boutons d'export
    Object.keys(exportButtons).forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                exportButtons[btnId]();
            };
        }
    });
    
    // Boutons de recherche
    document.querySelectorAll('.search-input').forEach(input => {
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const section = this.closest('[id$="-section"]');
                if (section) {
                    const sectionId = section.id.replace('-section', '');
                    switch (sectionId) {
                        case 'emplacements':
                            loadEmplacements();
                            break;
                        case 'users':
                            loadUsers();
                            break;
                        case 'reservations':
                            loadReservations();
                            break;
                    }
                }
            }
        });
    });
    
    // Navigation
    document.querySelectorAll('.nav-item').forEach(item => {
        item.onclick = function() {
            const section = this.getAttribute('data-section');
            if (section) {
                showSection(section);
            }
        };
    });
    
    // Bouton de confirmation de suppression
    const confirmDeleteBtn = document.querySelector('#confirmModal .delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.onclick = confirmDelete;
    }
    
    // Boutons de fermeture des modals
    document.querySelectorAll('.close-modal').forEach(closeBtn => {
        closeBtn.onclick = function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        };
    });
    
    // Gestion des formulaires modaux
    setupFormSubmitEvents();
}

// Configurer les événements de soumission des formulaires
function setupFormSubmitEvents() {
    // Formulaire d'emplacement
    const emplacementForm = document.getElementById('add-emplacement-form');
    if (emplacementForm) {
        emplacementForm.onsubmit = async function(e) {
            e.preventDefault();
            handleEmplacementFormSubmit(this);
            return false;
        };
    }
    
    // Formulaire d'utilisateur
    const userForm = document.getElementById('user-form');
    if (userForm) {
        userForm.onsubmit = async function(e) {
            e.preventDefault();
            handleUserFormSubmit(this);
            return false;
        };
    }
    
    // Formulaire de réservation
    const reservationForm = document.getElementById('reservation-form');
    if (reservationForm) {
        reservationForm.onsubmit = async function(e) {
            e.preventDefault();
            handleReservationFormSubmit(this);
            return false;
        };
    }
}

// Récupération directe des détails d'un utilisateur
async function fetchUserDetailsDirectly(id) {
    const response = await fetch(`api/users-fixed.php?id=${id}`);
    if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
    }
    const data = await response.json();
    if (!data.success || !data.data) {
        throw new Error(data.message || 'Utilisateur non trouvé');
    }
    return data.data;
}

// Récupération directe des détails d'une réservation
async function fetchReservationDetailsDirectly(id) {
    const response = await fetch(`api/reservations-fixed.php?id=${id}`);
    if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
    }
    const data = await response.json();
    if (!data.success || !data.data) {
        throw new Error(data.message || 'Réservation non trouvée');
    }
    return data.data;
}

// Récupération directe des détails d'un emplacement
async function fetchEmplacementDetailsDirectly(id) {
    const response = await fetch(`api/emplacements-fixed.php?id=${id}`);
    if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
    }
    const data = await response.json();
    if (!data.success || !data.data) {
        throw new Error(data.message || 'Emplacement non trouvé');
    }
    return data.data;
}

// Traitement du formulaire d'emplacement
async function handleEmplacementFormSubmit(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
    }
    
    try {
        const formData = new FormData(form);
        const idInput = document.getElementById('emplacement-id');
        const emplacementId = idInput ? idInput.value : null;
        
        const emplacementData = {
            code: formData.get('code'),
            nom: formData.get('nom'),
            type: formData.get('type'),
            superficie: parseFloat(formData.get('longueur')) * parseFloat(formData.get('largeur')),
            longueur: parseFloat(formData.get('longueur')),
            largeur: parseFloat(formData.get('largeur')),
            tarif_journalier: parseFloat(formData.get('tarif')),
            tarif_horaire: parseFloat(formData.get('tarif')) / 24,
            tarif_mensuel: parseFloat(formData.get('tarif')) * 30,
            capacite_navire: formData.get('capacite'),
            equipements: formData.get('equipements'),
            etat: formData.get('etat') || 'disponible'
        };
        
        const method = emplacementId ? 'PUT' : 'POST';
        const url = emplacementId ? `api/emplacements-fixed.php?id=${emplacementId}` : 'api/emplacements-fixed.php';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(emplacementData)
        });
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            const actionText = emplacementId ? 'modifié' : 'ajouté';
            showNotification(`Emplacement ${actionText} avec succès!`, 'success');
            
            closeEmplacementModal();
            await loadEmplacements();
            await loadStats();
        } else {
            throw new Error(data.message || 'Erreur lors de l\'opération sur l\'emplacement');
        }
    } catch (error) {
        console.error('Erreur lors de l\'opération sur l\'emplacement:', error);
        showNotification(`Erreur: ${error.message}`, 'error');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Enregistrer';
        }
    }
}

// Traitement du formulaire d'utilisateur
async function handleUserFormSubmit(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
    }
    
    try {
        const formData = new FormData(form);
        const userId = formData.get('id');
        
        const userData = {
            username: formData.get('username'),
            full_name: formData.get('full_name'),
            email: formData.get('email'),
            password: formData.get('password'),
            role: formData.get('role'),
            status: formData.get('status'),
            phone: formData.get('phone'),
            company: formData.get('company')
        };
        
        // Si le mot de passe est vide et que c'est une modification, le supprimer
        if (!userData.password && userId) {
            delete userData.password;
        }
        
        const method = userId ? 'PUT' : 'POST';
        const url = userId ? `api/users-fixed.php?id=${userId}` : 'api/users-fixed.php';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            const actionText = userId ? 'modifié' : 'ajouté';
            showNotification(`Utilisateur ${actionText} avec succès!`, 'success');
            
            closeUserModal();
            await loadUsers();
            await loadStats();
        } else {
            throw new Error(data.message || 'Erreur lors de l\'opération sur l\'utilisateur');
        }
    } catch (error) {
        console.error('Erreur lors de l\'opération sur l\'utilisateur:', error);
        showNotification(`Erreur: ${error.message}`, 'error');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Enregistrer';
        }
    }
}

// Traitement du formulaire de réservation
async function handleReservationFormSubmit(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
    }
    
    try {
        const formData = new FormData(form);
        const reservationId = formData.get('id');
        
        const reservationData = {
            numero_reservation: formData.get('numero_reservation'),
            user_id: formData.get('user_id'),
            emplacement_id: formData.get('emplacement_id'),
            date_debut: formData.get('date_debut'),
            date_fin: formData.get('date_fin'),
            statut: formData.get('statut'),
            montant_total: parseFloat(formData.get('montant')),
            montant_paye: parseFloat(formData.get('montant_paye')),
            notes: formData.get('notes')
        };
        
        const method = reservationId ? 'PUT' : 'POST';
        const url = reservationId ? `api/reservations-fixed.php?id=${reservationId}` : 'api/reservations-fixed.php';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(reservationData)
        });
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            const actionText = reservationId ? 'modifiée' : 'ajoutée';
            showNotification(`Réservation ${actionText} avec succès!`, 'success');
            
            closeReservationModal();
            await loadReservations();
            await loadStats();
        } else {
            throw new Error(data.message || 'Erreur lors de l\'opération sur la réservation');
        }
    } catch (error) {
        console.error('Erreur lors de l\'opération sur la réservation:', error);
        showNotification(`Erreur: ${error.message}`, 'error');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Enregistrer';
        }
    }
}

// Fonction de confirmation de suppression
async function confirmDelete() {
    const id = document.getElementById('confirm-id').value;
    const type = document.getElementById('confirm-type').value;
    
    const confirmBtn = document.querySelector('#confirmModal .delete-btn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Suppression...';
    }
    
    try {
        let endpoint = '';
        let reloadFunction = null;
        
        switch (type) {
            case 'user':
                endpoint = 'api/users-fixed.php';
                reloadFunction = loadUsers;
                break;
            case 'reservation':
                endpoint = 'api/reservations-fixed.php';
                reloadFunction = loadReservations;
                break;
            case 'emplacement':
                endpoint = 'api/emplacements-fixed.php';
                reloadFunction = loadEmplacements;
                break;
            default:
                throw new Error('Type inconnu');
        }
        
        const response = await fetch(`${endpoint}?id=${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Erreur HTTP: ${response.status} - ${errorText}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} supprimé avec succès!`, 'success');
            
            // Recharger les données
            if (reloadFunction) {
                await reloadFunction();
            }
            
            // Mettre à jour les statistiques
            await loadStats();
            
            closeConfirmModal();
        } else {
            throw new Error(data.message || `Erreur lors de la suppression du ${type}`);
        }
    } catch (error) {
        console.error(`Erreur lors de la suppression du ${type}:`, error);
        showNotification(`Erreur: ${error.message}`, 'error');
    } finally {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Supprimer';
        }
    }
}
