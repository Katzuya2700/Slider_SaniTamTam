<?php

    include_once ("config.php");
    include_once (CHEMIN_CLASS_RESSOURCE);

    class Page {

        private $metaDonnees = "";
        private $scriptSlide = "";
        private $affichage = "";
        private $finPage = "";

        /********************
         * 
         * PARTIE SQUELETTE HTML VALIDE
         * 
         ********************/

        /**********
         * 
         * Méthode pour mettre en place les méta-données de base
         * 
         * Constante CHEMIN_SLICK_CSS => chemin du fichier CSS de base (config.php / CONSTANTES DANS LE FICHIER BASEPAGE.CLASS.PHP)
         * Constante CHEMIN_SLICK_THEME_CSS => chemin du fichier du thème CSS (config.php / CONSTANTES DANS LE FICHIER BASEPAGE.CLASS.PHP)
         * 
         **********/
        public function metaDonnees() {

            $this->metaDonnees.="<!DOCTYPE html>
            <html lang='fr'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <meta http-equiv='X-UA-Compatible' content='ie=edge'>
                <link rel='stylesheet' type='text/css' href='".CHEMIN_SLICK_CSS."'/>
                <link rel='stylesheet' type='text/css' href='".CHEMIN_SLICK_THEME_CSS."'/>
                <title>SaniTamTam</title>
                ";
        }

        /**********
         * 
         * Méthode pour afficher le contenu du slider mais aussi le tableau de messages d'erreurs
         * 
         * $visible / int => premet de rendre visible ou invisible le tableau d'erreurs (0 = visible / 1 = invisible)
         * 
         * getTab() => contient toutes les données nécessaires pour l'affichage
         * tabErreurs() => contient tout les messages d'erreurs
         * 
         **********/
        public function affichage() {

            $visible = 1;

            $ressource = new Ressource();

            $slides = $ressource->getTab();

            $this->affichage.= "<section class='your-class'>";
            foreach ($slides as $slide) {
                $this->affichage.= "<div>";
                $this->affichage.= "$slide";
                $this->affichage.= "</div>";
            }
            $this->affichage .="</section>";

            $errors = $ressource->tabErreurs();

            if ($visible === 0) {
                $this->affichage.= "<ul>";
                foreach ($errors as $error) {
                    $this->affichage.= "<li>";
                    $this->affichage.= "$error";
                    $this->affichage.= "</li>";
                }
                $this->affichage.= "</ul>";
            }

            $forms = $ressource->getTabForm();
            $radios = $ressource->getTabButtonRadio();
            $reloads = $ressource->getTabRadioReload();

            $this->affichage.="<div class='interface' id='divacacher'>";
            $this->affichage.="<form method='get' action='index.php'>
            <p class='form_texte'>Temps entre chaque image : </p><input type='number' name='Temps'>
            ";
            $this->affichage.="<br><br><p class='form_texte'>Rechargement de la page automatique : </p>";
            foreach ($reloads as $reload) {
                $this->affichage.= "$reload";
            }
            $this->affichage.="<input type='number' name='Temps_reload'>";
            $this->affichage.="<br><br><p class='reloadforce'>Rechargement forcé : </p>
            <input type='submit'name='Rechargement' value='Rechargement'>
            <br><br>";
            $this->affichage.="<input type='submit'name='Enregistrement' value='Enregistrement'>";
            $this->affichage.="</form>";
            $this->affichage.="<button id='button_list'>Liste de vues (Slider)</button>";
            $this->affichage.= "<div class='super_groupe_list'>";
                foreach ($forms as $form) {
                    $this->affichage.= "$form";
                }
            $this->affichage.= "</div>";
            $this->affichage.= "</div>";
            $this->affichage.="<div class='zone_button'>
            <input type='button' id='button_interface' value='Interface admin' onclick='AfficherMasquer()'>
            </div>";
        }

        /**********
         * 
         * Méthode qui contient le script JavaScript pour le bibliothèque "SLICK"
         * 
         * Constante LIEN_CODE_JQUERY_JS => lien du code Jquery (config.php / CONSTANTES DANS LE FICHIER BASEPAGE.CLASS.PHP)
         * Constante CHEMIN_SLICK_JS => chemin du fichier JavaScript (config.php / CONSTANTES DANS LE FICHIER BASEPAGE.CLASS.PHP)
         * 
         * .your-class => la class cible, à changer pour le nom de class que vous avez choisie
         * 
         * Tout le code après <script type='text/javascript'> va toucher directement au fonctionnement du slider
         * il est donc à modifier avec prudence
         * 
         * Pour debug penssez à mettre autoplay sur false, pour arrêter le slide.
         * 
         * Contiennent aussi les autres codes JS pour l'interface administrateur.
         * 
         **********/
        public function scriptSlide() {

            $ressource = new Ressource();

            $codeJSs = $ressource->getTabCodeJSSpeedPlay();
            $codeReloads = $ressource->getTabJsCodeReload();

            $this->scriptSlide.="
            <script src='".LIEN_CODE_JQUERY_JS."' type='text/javascript'></script>
            <script src='".CHEMIN_SLICK_JS."' type='text/javascript' charset='utf-8'></script>

            <script type='text/javascript'>
            $(document).on('ready', function(){
                $('.your-class').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    autoplay: true,";
                    foreach ($codeJSs as $codeJS) {
                        $this->scriptSlide.= "$codeJS";
                    }
                    $this->scriptSlide.="adaptiveHeight: true,
                    arrows: false,
                  });
                });
            </script>
            
            <script type=\"text/javascript\" src='option_js/visibility_button_interface.js'>;
            </script>

            <script type='text/javascript' src='option_js/visibility_interface.js'>
            </script>

            <script type='text/javascript'>";
            foreach($codeReloads as $codeReload) {
                $this->scriptSlide.= "$codeReload";
            }
            $this->scriptSlide.="</script>

            <script type=\"text/javascript\" src='option_js/visibility_list_slide.js'>
            </script>
            
            </head>
            <body>
            ";
        }

        /**********
         * 
         * Méthode pour mettre en place les balises fermentes du HTML
         * 
         **********/
        public function finPage() {

            $this->finPage.="
            </body>
            </html>";
        }

        /********************
         * 
         * PARTIE POUR LE CONTRUCT. ET DESTRUCT.
         * 
         ********************/

        public function __construct() {

            $this->metaDonnees();
            $this->scriptSlide();
            $this->affichage();
            $this->finPage();

        }

        public function __destruct() {

            echo $this->metaDonnees;
            echo $this->scriptSlide;
            echo $this->affichage;
            echo $this->finPage;
        }

    }

?>