// Ce fichier contient les correctifs pour les lags sur les boutons

// Fonction pour charger les détails d'un utilisateur par son ID
async function fetchUserDetails(id) {
    try {
        console.log(`Récupération des détails de l'utilisateur ID: ${id}`);
        
        const response = await fetch(`api/users-fixed.php?id=${id}`);
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
            return data.data;
        } else {
            throw new Error(data.message || 'Utilisateur non trouvé');
        }
    } catch (error) {
        console.error("Erreur lors de la récupération des détails de l'utilisateur:", error);
        throw error;
    }
}

// Fonction pour charger les détails d'une réservation par son ID
async function fetchReservationDetails(id) {
    try {
        console.log(`Récupération des détails de la réservation ID: ${id}`);
        
        const response = await fetch(`api/reservations-fixed.php?id=${id}`);
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
            return data.data;
        } else {
            throw new Error(data.message || 'Réservation non trouvée');
        }
    } catch (error) {
        console.error("Erreur lors de la récupération des détails de la réservation:", error);
        throw error;
    }
}

// Fonction pour charger les détails d'un emplacement par son ID
async function fetchEmplacementDetails(id) {
    try {
        console.log(`Récupération des détails de l'emplacement ID: ${id}`);
        
        const response = await fetch(`api/emplacements-fixed.php?id=${id}`);
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
            return data.data;
        } else {
            throw new Error(data.message || 'Emplacement non trouvé');
        }
    } catch (error) {
        console.error("Erreur lors de la récupération des détails de l'emplacement:", error);
        throw error;
    }
}

// Utilitaire de formatage de date pour les entrées datetime-local
function formatDateForInput(dateStr) {
    if (!dateStr) return '';
    
    // Convertir la chaîne de date en objet Date
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return '';
    
    // Format YYYY-MM-DDThh:mm requis pour datetime-local
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Fonction pour charger les clients dans une liste déroulante
async function loadClientsForSelect() {
    try {
        const users = await fetchUsers();
        const select = document.getElementById('client-id');
        
        if (!select) return;
        
        // Garder seulement l'option par défaut
        select.innerHTML = '<option value="">Sélectionnez un client</option>';
        
        // Filtrer uniquement les utilisateurs avec le rôle "user"
        const clients = users.filter(user => user.role === 'user');
        
        clients.forEach(client => {
            const option = document.createElement('option');
            option.value = client.id;
            option.textContent = `${client.full_name} (${client.username})`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Erreur lors du chargement des clients:", error);
        showNotification("Erreur lors du chargement des clients: " + error.message, 'error');
    }
}

// Fonction pour charger les emplacements dans une liste déroulante
async function loadEmplacementsForSelect() {
    try {
        const emplacements = await fetchEmplacements();
        const select = document.getElementById('emplacement-id');
        
        if (!select) return;
        
        // Garder seulement l'option par défaut
        select.innerHTML = '<option value="">Sélectionnez un emplacement</option>';
        
        // Filtrer uniquement les emplacements disponibles
        const availableEmplacements = emplacements.filter(emp => emp.etat === 'disponible' || emp.statut === 'disponible');
        
        availableEmplacements.forEach(emp => {
            const option = document.createElement('option');
            option.value = emp.id;
            option.textContent = `${emp.code} - ${emp.nom} (${formatMoney(emp.tarif_journalier)}/jour)`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Erreur lors du chargement des emplacements:", error);
        showNotification("Erreur lors du chargement des emplacements: " + error.message, 'error');
    }
}
