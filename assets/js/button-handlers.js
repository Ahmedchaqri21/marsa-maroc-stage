// Fonction pour attacher les gestionnaires d'événements aux boutons des tables
function attachTableButtonEvents() {
    // Détacher d'abord tous les gestionnaires existants pour éviter les duplications
    document.querySelectorAll('[data-action]').forEach(button => {
        // Utiliser un attribut personnalisé pour suivre si les gestionnaires ont été attachés
        if (button.dataset.eventAttached === 'true') {
            return;
        }
        
        const action = button.dataset.action;
        const id = button.dataset.id;
        
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Désactiver le bouton pendant le traitement
            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            try {
                console.log(`Action: ${action}, ID: ${id}`);
                
                switch (action) {
                    case 'edit-user':
                        const user = await fetchUserDetails(id);
                        showUserModal(user);
                        break;
                        
                    case 'delete-user':
                        showConfirmModal(id, 'user');
                        break;
                        
                    case 'edit-reservation':
                        const reservation = await fetchReservationDetails(id);
                        showReservationModal(reservation);
                        break;
                        
                    case 'delete-reservation':
                        showConfirmModal(id, 'reservation');
                        break;
                        
                    case 'edit-emplacement':
                        const emplacement = await fetchEmplacementDetails(id);
                        showEmplacementModal(emplacement);
                        break;
                        
                    case 'delete-emplacement':
                        showConfirmModal(id, 'emplacement');
                        break;
                        
                    case 'view-reservation':
                        const resDetails = await fetchReservationDetails(id);
                        showReservationModal(resDetails, true); // true pour mode visualisation uniquement
                        break;
                }
            } catch (error) {
                console.error(`Erreur lors de l'action ${action}:`, error);
                showNotification(`Erreur: ${error.message}`, 'error');
            } finally {
                // Réactiver le bouton
                this.disabled = false;
                this.innerHTML = originalHTML;
            }
        });
        
        // Marquer le bouton comme ayant des gestionnaires attachés
        button.dataset.eventAttached = 'true';
    });
}
