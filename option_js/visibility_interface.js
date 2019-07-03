/********************
 * 
 * CODE JAVASCRIPT POUR L'INTERFACE
 * 
 ********************/

/**********
 * 
 * Code permet de rendre visible ou invisible l'interface au click sur le bouton(#button_interface) prévu à cet effet.
 * 
 **********/
function AfficherMasquer() {

    divInfo = document.getElementById('divacacher');

    if (divInfo.style.display == 'none') {
        divInfo.style.display = 'flex';
    }
    else {
        divInfo.style.display = 'none';
    }
}
