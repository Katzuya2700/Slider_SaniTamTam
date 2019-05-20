/************************************************************
 * 
 * CODE JQUERY QUI PERMET DE CACHER OU RENDRE VISIBLE LA LISTE DES VUES DU SLIDER DANS L'INTERFACE UTILISATEUR
 * 
 ************************************************************/
$(document).ready(function()
{
    $('.super_groupe_list').hide();
    $('#button_list').click(function()
    {
        $('.super_groupe_list').toggle();
    });
});