function basculerTheme() {
    let link = document.getElementById('theme-style');
    if (!link) return;
    
    let theme = 'clair';
    let urlActuelle = link.getAttribute('href') || link.href;
    let cacheBuster = "?t=" + new Date().getTime();

    // S'il détecte le mot "sombre" dans le nom du fichier
    if (urlActuelle.indexOf('sombre') !== -1) {
        link.setAttribute('href', 'style.css' + cacheBuster);
    } else {
        link.setAttribute('href', 'style-sombre.css' + cacheBuster);
        theme = 'sombre';
    }
    
    document.cookie = "theme=" + theme + "; path=/; max-age=" + (60*60*24*30);
}
async function sauvegarderProfilAjax(event) {
    event.preventDefault(); // Annule le rechargement brutal de la page
    
    const form = event.target;
    const formData = new FormData(form);
    
    const data = {
        action: 'modifier_profil',
        nom: formData.get('nom'),
        prenom: formData.get('prenom'),
        adresse: formData.get('adresse')
    };
    
    try {
        const response = await fetch('ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        const msgBox = document.getElementById('msg-ajax-profil');
        msgBox.textContent = result.message;
        msgBox.className = result.success ? 'msg-success-bold text-center mb-15' : 'text-center text-red mb-15';
        msgBox.style.display = 'block';
    } catch (error) {
        console.error('Erreur Fetch:', error);
    }
}

async function bloquerUtilisateurAjax(email, boutonElement) {
    if(!confirm("Êtes-vous sûr de vouloir bloquer " + email + " ?")) return;
    
    try {
        const response = await fetch('ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'bloquer_utilisateur', email: email })
        });
        const result = await response.json();
        
        if(result.success) {
            // Mise à jour visuelle du bouton SANS recharger la page
            boutonElement.textContent = 'Bloqué 🚫';
            boutonElement.classList.replace('btn-red', 'btn-muted'); 
            boutonElement.disabled = true;
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Erreur Fetch:', error);
    }
}

async function filtrerCarteAjax(categorie) {
    try {
        const url = categorie === 'tous' ? 'produits.php' : 'produits.php?categorie=' + categorie;
        const response = await fetch(url);
        const htmlComplet = await response.text();
        
        const parser = new DOMParser();
        const documentVirtuel = parser.parseFromString(htmlComplet, 'text/html');
        
        const ancienneGrille = document.getElementById('grille-produits');
        const nouvelleGrille = documentVirtuel.getElementById('grille-produits');
        
        if(ancienneGrille && nouvelleGrille) {
            ancienneGrille.innerHTML = nouvelleGrille.innerHTML;
        }
    } catch (error) {
        console.error('Erreur de filtre AJAX:', error);
    }
}
