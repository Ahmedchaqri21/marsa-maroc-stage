/**
 * Scripts principaux - Marsa Maroc Port Management System
 * Fonctions utilitaires communes à toute l'application
 */

class MarsaMarocApp {
    constructor() {
        this.baseUrl = this.getBaseUrl();
        this.initializeApp();
    }

    getBaseUrl() {
        const path = window.location.pathname;
        const segments = path.split('/');
        const projectIndex = segments.findIndex(seg => seg === 'marsa maroc project' || seg === 'marsa-maroc-project');
        if (projectIndex !== -1) {
            return segments.slice(0, projectIndex + 1).join('/');
        }
        return '';
    }

    initializeApp() {
        // Initialiser les composants communs
        this.initializeNotifications();
        this.initializeLoadingStates();
        this.initializeTooltips();
    }

    // Système de notifications
    initializeNotifications() {
        // Créer le conteneur de notifications s'il n'existe pas
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
    }

    showNotification(message, type = 'info', duration = 5000) {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = this.getNotificationIcon(type);
        notification.innerHTML = `
            <div class="notification-icon">
                <i class="${icon}"></i>
            </div>
            <div class="notification-message">${message}</div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        container.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Suppression automatique
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, duration);
    }

    getNotificationIcon(type) {
        const icons = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    // États de chargement
    initializeLoadingStates() {
        // Ajouter les styles CSS pour les états de chargement si nécessaire
        this.addLoadingStyles();
    }

    addLoadingStyles() {
        if (document.getElementById('loading-styles')) return;

        const style = document.createElement('style');
        style.id = 'loading-styles';
        style.textContent = `
            .loading-spinner {
                display: inline-block;
                width: 20px;
                height: 20px;
                border: 2px solid rgba(255,255,255,0.3);
                border-radius: 50%;
                border-top-color: #fff;
                animation: spin 1s ease-in-out infinite;
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            
            .btn-loading {
                position: relative;
                pointer-events: none;
                opacity: 0.7;
            }
        `;
        document.head.appendChild(style);
    }

    setLoadingState(element, isLoading) {
        if (isLoading) {
            element.classList.add('btn-loading');
            element.disabled = true;
            
            // Ajouter le spinner s'il n'existe pas
            let spinner = element.querySelector('.loading-spinner');
            if (!spinner) {
                spinner = document.createElement('span');
                spinner.className = 'loading-spinner';
                element.insertBefore(spinner, element.firstChild);
            }
        } else {
            element.classList.remove('btn-loading');
            element.disabled = false;
            
            // Supprimer le spinner
            const spinner = element.querySelector('.loading-spinner');
            if (spinner) {
                spinner.remove();
            }
        }
    }

    // Tooltips
    initializeTooltips() {
        document.addEventListener('mouseover', (e) => {
            if (e.target.hasAttribute('title') && !e.target.hasAttribute('data-tooltip-initialized')) {
                this.createTooltip(e.target);
                e.target.setAttribute('data-tooltip-initialized', 'true');
            }
        });
    }

    createTooltip(element) {
        const title = element.getAttribute('title');
        element.removeAttribute('title');
        
        let tooltip;
        
        element.addEventListener('mouseenter', () => {
            tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = title;
            document.body.appendChild(tooltip);
            
            // Positionner le tooltip
            const rect = element.getBoundingClientRect();
            tooltip.style.cssText = `
                position: absolute;
                top: ${rect.top - tooltip.offsetHeight - 10}px;
                left: ${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px;
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1000;
                pointer-events: none;
                white-space: nowrap;
            `;
        });
        
        element.addEventListener('mouseleave', () => {
            if (tooltip && tooltip.parentElement) {
                tooltip.remove();
            }
        });
    }

    // Utilitaires API
    async apiCall(endpoint, options = {}) {
        const url = `${this.baseUrl}/api/${endpoint}`;
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };
        
        const mergedOptions = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, mergedOptions);
            
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API Call Error:', error);
            this.showNotification(`Erreur API: ${error.message}`, 'error');
            throw error;
        }
    }

    // Formatage des données
    formatMoney(amount) {
        if (amount === undefined || amount === null) return '-';
        return new Intl.NumberFormat('fr-MA', {
            style: 'currency',
            currency: 'MAD'
        }).format(amount);
    }

    formatDate(dateStr, options = {}) {
        if (!dateStr) return '-';
        
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        
        const defaultOptions = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        
        return date.toLocaleDateString('fr-FR', { ...defaultOptions, ...options });
    }

    // Validation
    validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'Ce champ est requis');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });
        
        return isValid;
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
}

// Initialiser l'application
let marsaApp;
document.addEventListener('DOMContentLoaded', () => {
    marsaApp = new MarsaMarocApp();
    
    // Exposer globalement pour compatibilité
    window.showNotification = (message, type, duration) => marsaApp.showNotification(message, type, duration);
    window.setLoadingState = (element, isLoading) => marsaApp.setLoadingState(element, isLoading);
    window.apiCall = (endpoint, options) => marsaApp.apiCall(endpoint, options);
    window.formatMoney = (amount) => marsaApp.formatMoney(amount);
    window.formatDate = (dateStr, options) => marsaApp.formatDate(dateStr, options);
});
