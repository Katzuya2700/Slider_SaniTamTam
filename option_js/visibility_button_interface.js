/********************
 * 
 * CODE JAVASCRIPT (Jquery) POUR LE BOUTON
 * 
 ********************/

/**********
 * 
 * Code permet de rendre visible ou invisible le bouton qui permet d'afficher l'interface.
 * 
 * Temps que nous sommes à l'extérieur le bouton est invisible. Si nous rentrons dans la zone de la div(zone_button) elle apparaît.
 * 
 **********/
$( document ).ready(function() {

    $('div.zone_button #button_interface').hide();

    $('div.zone_button').hover(
        function() {
            $('div.zone_button #button_interface').fadeIn();
        },
        function() {
            $('div.zone_button #button_interface').fadeOut();
            $('div.zone_button #button_interface').hide();
        }
    );
});