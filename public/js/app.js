// Fonction utilitaire pour afficher des messages
function showMessage(message, type = 'info') {
  const toastContainer = document.querySelector('.toast-container');
  if (toastContainer) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.setAttribute('aria-atomic', 'true');
    toast.setAttribute('data-bs-delay', '4000');
    
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
  }
}

// Fonction fallback pour la copie
function fallbackCopy(textToCopy, successCallback) {
  const textArea = document.createElement('textarea');
  textArea.value = textToCopy;
  textArea.style.position = 'fixed';
  textArea.style.top = '0';
  textArea.style.left = '0';
  textArea.style.width = '2em';
  textArea.style.height = '2em';
  textArea.style.padding = '0';
  textArea.style.border = 'none';
  textArea.style.outline = 'none';
  textArea.style.boxShadow = 'none';
  textArea.style.background = 'transparent';
  textArea.style.opacity = '0';
  textArea.style.zIndex = '-9999';
  
  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();
  
  try {
    const successful = document.execCommand('copy');
    document.body.removeChild(textArea);
    
    if (successful) {
      successCallback();
    } else {
      showMessage('Impossible de copier. Veuillez sélectionner le texte manuellement.', 'warning');
    }
  } catch (err) {
    document.body.removeChild(textArea);
    console.error('Erreur execCommand:', err);
    showMessage('Erreur lors de la copie. Veuillez sélectionner le texte manuellement.', 'danger');
  }
}

// Fonction pour copier un commentaire dans le presse-papiers
window.copyComment = function(button) {
  const commentId = button.getAttribute('data-comment-id');
  const commentElement = document.getElementById(commentId);
  
  if (!commentElement) {
    console.error('Commentaire introuvable avec l\'ID:', commentId);
    showMessage('Erreur : commentaire introuvable', 'danger');
    return;
  }
  
  const textToCopy = commentElement.textContent.trim();
  
  if (!textToCopy) {
    showMessage('Aucun texte à copier', 'warning');
    return;
  }
  
  // Fonction pour le feedback visuel
  const showSuccess = () => {
    const icon = button.querySelector('i');
    if (icon) {
      const originalClass = icon.className;
      icon.className = 'fas fa-check';
      
      // Retirer toutes les classes outline et ajouter success
      button.classList.remove('btn-outline-light', 'btn-outline-dark', 'btn-outline-danger', 'btn-outline-warning', 'btn-outline-success');
      button.classList.add('btn-success');
      
      showMessage('Commentaire copié dans le presse-papiers !', 'success');
      
      // Restaurer après 2 secondes
      setTimeout(() => {
        icon.className = originalClass;
        button.classList.remove('btn-success');
        // Restaurer la classe originale selon le contexte
        if (button.closest('.table-warning')) {
          button.classList.add('btn-outline-dark');
        } else {
          button.classList.add('btn-outline-light');
        }
      }, 2000);
    }
  };
  
  // Utiliser l'API Clipboard moderne (nécessite HTTPS ou localhost)
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(textToCopy).then(() => {
      showSuccess();
    }).catch(err => {
      console.error('Erreur Clipboard API:', err);
      // Fallback si l'API échoue
      fallbackCopy(textToCopy, showSuccess);
    });
  } else {
    // Fallback pour les navigateurs plus anciens
    fallbackCopy(textToCopy, showSuccess);
  }
};

// Gestion des toasts Bootstrap
document.addEventListener('DOMContentLoaded', function () {
  // Initialisation des toasts
  const toastElList = [].slice.call(document.querySelectorAll('.toast'));
  toastElList.map(function (toastEl) { 
    return new bootstrap.Toast(toastEl).show(); 
  });

  // Attacher les gestionnaires d'événements pour les boutons de copie
  function attachCopyButtons() {
    document.querySelectorAll('.copy-btn').forEach(button => {
      // Ne pas réattacher si déjà attaché
      if (button.dataset.listenerAttached === 'true') {
        return;
      }
      button.dataset.listenerAttached = 'true';
      
      button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (typeof copyComment === 'function') {
          copyComment(this);
        } else {
          console.error('La fonction copyComment n\'est pas disponible');
          showMessage('Erreur : fonction de copie non disponible', 'danger');
        }
      });
    });
  }
  
  // Attacher immédiatement
  attachCopyButtons();
  
  // Réattacher après un court délai au cas où le DOM n'est pas complètement chargé
  setTimeout(attachCopyButtons, 100);


  (function () {
    const applyTheme = () => {
      const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
      document.documentElement.setAttribute('class', isDark ? 'dark' : 'light');
    };
    // Première application
    applyTheme();
    // Réagir si l'utilisateur change le thème système
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    if (mq.addEventListener) {
      mq.addEventListener('change', applyTheme);
    } else if (mq.addListener) {
      // Safari anciens
      mq.addListener(applyTheme);
    }
  })();

  

  // Amélioration de l'accessibilité mobile
  if (window.innerWidth <= 576) {
    // Augmenter la taille des zones cliquables sur mobile
    document.querySelectorAll('.btn').forEach(btn => {
      btn.style.minHeight = '44px';
      btn.style.padding = '12px 16px';
    });
    
    // Améliorer la navigation tactile
    document.querySelectorAll('.nav-link').forEach(link => {
      link.style.padding = '12px 16px';
      link.style.minHeight = '44px';
    });
  }
});

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))