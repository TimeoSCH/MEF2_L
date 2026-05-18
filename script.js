function basculerTheme() {

    let baliseCss = document.getElementById('theme-style');
    
    let themeActuel = baliseCss.getAttribute('href');
    
    if (themeActuel === 'style.css') {
       
        baliseCss.setAttribute('href', 'style-sombre.css');   
        document.cookie = "theme=sombre; max-age=" + (30*24*60*60) + "; path=/";
    } else {
     
        baliseCss.setAttribute('href', 'style.css');       
        document.cookie = "theme=clair; max-age=" + (30*24*60*60) + "; path=/";
    }
}
