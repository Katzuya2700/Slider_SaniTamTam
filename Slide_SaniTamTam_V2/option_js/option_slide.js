/********************
 * 
 * CODE JAVASCRIPT (Jquery) POUR LE SLIDER
 * 
 ********************/

/**********
 * 
 * Se code permet de faire fonctionner le slider avec des paramètres définis par l'utilisateur.
 * 
 **********/

$(document).on('ready', function(){
    $('.your-class').slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          autoplay: true,
          autoplaySpeed: 3000,
          adaptiveHeight: true,
          arrows: false,
      });
    });