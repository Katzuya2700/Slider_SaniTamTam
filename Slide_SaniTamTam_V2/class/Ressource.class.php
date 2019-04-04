<?php

    require_once ("config.php");

    /**********
     * 
     * Permet de rendre invisible les erreurs non necessaire mais logique
     * 
     **********/
    ini_set("display_errors",0);error_reporting(0);

    class Ressource {

        private $is_conn; // Test de connexion, renvoie true ou false
        private $resultatAutorisationAffichage; // Permet de savoir si il faut afficher (true) ou non (false)
        private $miseAJourInterne; // Renvoi true ou false en fonction de la situation

        private $internalJson = false; // Contient le Json interne brute
        private $externalJson = false; // Contient le Json externe brute

        private $parsed_InternalJson = false; // Contient un Json interne viable
        private $parsed_ExternalJson = false; // Contient un Json externe viable

        private $nomImage; // Tableau qui contient les noms des images

        private $tab; // Tableau qui ne contient que les valeurs du tableau interne spécifique (titre, image)
        private $tabForm; // Tableau qui contient les valeurs du tableau interne spécifique piur un formulaire (titre, image, date de publication, date événement)

        private $tabInterne = false; // Tableau interne filtrer a partir du $parsed_InternalJson
        private $tabExterne = false; // Tableau externe filtrer a partir du $parsed_ExternalJson

        private $tabIdInterne; // Tableau pour les ID interne
        private $tabIdExterne; // Tableau pour les ID externe

        private $erreurs; // Tableau qui contient les messages d'erreurs

        /********************
         * 
         * PARTIE GESTION D'ERREURS
         * 
         ********************/

        /**********
         * 
         * Renvoi un tableau d'erreurs
         * 
         * $erreurs => tableau qui contient tous les messages d'erreurs
         * 
         **********/
        public function tabErreurs() {

            if (!empty($this->erreurs)) {
                    return ($this->erreurs);
            }
        }

        /********************
         * 
         * PARTIE GESTION DU CONTENU SLIDER
         * 
         ********************/

        /**********
         * 
         * Test de connexion à internet
         * 
         * $connected => test de la connexion avec les constantes LIEN_SITE / PORT_WEB (config.php / CONSTANTES DANS LE FICHIER RESSOURCE.CLASS.PHP)
         * 
         * If $connected renvoi un "true" alors $is_conn = true
         * Else $connected renvoi un "false" alors $is_conn = false
         * 
         **********/
        public function connexionInternet() {

            $connected = @fsockopen (LIEN_SITE,PORT_WEB);

            if ($connected) {
                $this->is_conn = true;
                fclose($connected);
            }else {
                $this->is_conn = false;
                array_push($this->erreurs, "connexionInternet : Pas de connexion à internet.");
            }
        }

        /**********
         * 
         * Recupération du Json interne
         * 
         * Constante CHEMIN_INTERNE_JON => le chemin du fichier Json interne (config.php / CONSTANTES DANS LE FICHIER RESSOURCE.CLASS.PHP)
         * 
         * Méthode qui retourne soit les données lues ou un false si une erreur se produit
         * 
         **********/
        public function recupJSONInterne() {

            if (file_exists(CHEMIN_INTERNE_JSON)) {
                if ($this->internalJson = file_get_contents(CHEMIN_INTERNE_JSON)) {
                }else {
                    array_push ($this->erreurs, "recupJSONInterne : Le file_get n'est pas valide.");
                }
            }else {
                array_push($this->erreurs, "recupJSONInterne : Le fichier interne.json n'existe pas, merci de recharger la page.");
                fopen("json/interne.json","r+");
            }
        }

        /**********
         * 
         * Recupération du Json externe
         * 
         * Constante LIEN_SLIDER_EXTERNE => le lien du Json externe (config.php / CONSTANTES DANS LE FICHIER RESSOURCE.CLASS.PHP)
         * 
         * Méthode qui retourne soit les données lues ou un false si une erreur se produit
         * 
         **********/
        public function recupJSONExterne() {

            if ($this->externalJson = file_get_contents(LIEN_SLIDER_EXTERNE)) {
            }else {
                array_push($this->erreurs, "recupJSONExterne : Le lien externe n'est pas valide.");
            }
        }

        /********** 
         * 
         * Méthode pour le décodage du Json et récupération de se qu'ils retournes
         * 
         * La méthode à était factorisée avec deux paramétres pour éviter de la répétition inutile
         * Il faudra donc appeler cette méthode deux fois dans le constructeur mais avec deux paramétres différents
         * 
         * $parsedDecodeJSON => correspond à $parsed_InternalJson ou à $parsed_ExternalJson
         * $JSON => correspond à $internalJson ou à $externalJson
         * 
         **********/
        public function jsonDecode(&$parsedDecodeJSON, &$JSON) {
            
            if ($parsedDecodeJSON = json_decode($JSON, true)) {
                if (empty($parsedDecodeJSON)) {
                    array_push($this->erreurs, "jsonDecode : Le parsed_InternalJson ou le parsed_ExternalJson est vide");
                }
            }else {
                array_push($this->erreurs, "jsonDecode : Le parsed_InternalJson ou le parsed_ExternalJson n'est pas valide.");
            }
        }

        /********** 
         * 
         * Méthode pour le traitement de deux tableaux et la récupération de se qu'ils retournes
         * 
         * La méthode à était factorisée avec deux paramétres pour éviter de la répétition inutile
         * Il faudra donc appeler cette méthode deux fois dans le constructeur mais avec deux paramétres différents
         * 
         * $parsedJson => correspond à $parsed_InternalJson ou à $parsed_ExternalJson
         * $tabJson => correspond à $tabInterne ou à $tabExterne
         * 
         **********/
        public function traitementTableau(&$parsedJson, &$tabJson) {

            $tabJson = array();

            foreach ($parsedJson["nodes"] as $noeud) {
                array_push($tabJson, $noeud["node"]);
            }
        }

        /**********
         * 
         * Méthode pour initialisé un tableau qui sera utiliser pour afficher les ressources demandées
         * 
         * $tab => contient les ressources interne, qui seron utiliser pour le slider
         * 
         **********/
        public function tabRessourcesInterne() {

            $this->tab = array();
        }

        public function tabRessourcesInterneForm() {

            $this->tabForm = array();
        }

        /**********
         * 
         * Méthode pour comparer les deux tableaux Json, et donc savoir si il faut affichier ou non
         * 
         * $resultatAutorisationAffichage / booléen => utiliser pour savoir si l'affichage doit être fait
         * $tabInterne / booléen => contient les ressources du slider interne au projet
         * $tabExterne / booléen => contient les ressources pour le slider mais externe au projet
         * 
         **********/
        public function autorisationAffichage() {

            if ($this->tabExterne === false) {
                if ($this->tabInterne === false) {
                    array_push($this->erreurs, "testComparaison : les deux tableau son vide, la connexion à internet est requise.");
                    $this->resultatAutorisationAffichage = false;
                }else {
                    $this->resultatAutorisationAffichage = true;
                }
            }else {
                if ($this->tabInterne === $this->tabExterne) {
                    $this->resultatAutorisationAffichage = true;
                }else {
                    $this->resultatAutorisationAffichage = true;
                    $this->miseAJourInterne = true;
                    // dans cette situation le tab interne doit être mise a jour, ressource y comprit
                }
            }
        }

        /**********
         * 
         * Méthode pour push le contenu du Json avec un file_put_contents
         * 
         * Constante CHEMIN_INTERNE_JSON => le chemin du fichier interne Json (config.php / CONSTANTES DANS LE FICHIER RESSOURCE.CLASS.PHP)
         * 
         **********/
        public function ajoutContenuJsonInterne() {

            $importJson = file_put_contents(CHEMIN_INTERNE_JSON, $this->externalJson);
            $this->resultatAutorisationAffichage = true;
        }

        /********************
         * 
         * PARTIE POUR L'AFFICHAGE DU SLIDE
         * 
         ********************/

        /**********
         * 
         * Méthode pour construire le tableau du slider
         * 
         * Constante CHEMIN_IMAGE_DEFAULT => Chemin de l'image par default du slider (config.php / CONSTANTES DANS LE FICHIER RESSOURCE.CLASS.PHP)
         * $slide / string => contient en string le contenu du slider
         * $sanitize_file_name => méthode qui permet de supprimer les caractères spéciaux (voir plus bas pour le code)
         * 
         * Les multiples foreach permetent d'allez chercher les informations nécessaires pour l'affichage du slider
         * Pour les images il est obligatoire d'avoir une image par default car chaque slide n'a pas obligatoirement une image
         * 
         **********/
        public function constructionTabSlide() {

            foreach($this->tabInterne as $key => $value) {
                $slide = '' ;

                $slide .= '<div class="box_texte_slide"><h2 class="texte_slide">'.$value['title'].'</h2></div>';
                $slide .= '<div class="box_date-slide"><p class="date_publication">'.$value['date_publication'].'</p></div>';

                foreach($value as $key2 => $value2) {
                    if ($key2 === "Image") {
                        $src = $info = pathinfo($value2);
                        if (empty($src['basename'])) {
                            $slide .= '
                            <div class="div_img_slide" style="background-image:url('.CHEMIN_IMAGE_DEFAULT.')">
                            </div>
                            '; 
                        }else {
                            $slide .= '
                            <div class="div_img_slide" style="background-image:url(ressources/'.$this->sanitize_file_name($src['basename']).')">
                            </div>
                            ';    
                        }
                    }
                }
                array_push($this->tab,$slide);
            }
            return ($this->tab);  
        }

        /**********
         * 
         *  Méthode qui retourne un tableau, qui contient le contenu des slider, pour ensuite l'afficher
         * 
         **********/
        public function getTab() {

            return $this->tab ;
            
        }

        public function constructionTabSlideListeForm() {

            foreach($this->tabInterne as $key => $value) {
                $formInterface = '';

                $formInterface .='<div class="groupe_list">';

                foreach($value as $key2 => $value2) {
                    if ($key2 === "Image") {
                        $src = $info = pathinfo($value2);
                        if (empty($src['basename'])) {
                            $formInterface .= '
                            <div class="div_img_form" ><img src="'.CHEMIN_IMAGE_DEFAULT.'"/>
                            </div>
                            '; 
                        }else {
                            $formInterface .= '
                            <div class="div_img_form"><img src="ressources/'.$this->sanitize_file_name($src['basename']).'"/>
                            </div>
                            ';    
                        }
                    }
                }
                $formInterface .='&nbsp';
                $formInterface .= '<div class="box_texte_form"><p class="texte_title_form">'.$value['title'].'</p></div>';
                $formInterface .='&nbsp';
                $formInterface .= '<div class="box_texte_form_publication"><p class="texte_publication_form">'.$value['date_publication'].'</p></div>';
                $formInterface .='&nbsp';
                $formInterface .= '<div class="box_texte_form_event"><p class="texte_event_form">'.$value['date_event'].'</p></div>';
                $formInterface .= '</div>';
                array_push($this->tabForm,$formInterface);
            }
            return ($this->tabForm);  
        }

        public function getTabForm() {

            return $this->tabForm;
        }

        /********************
         * 
         * PARTIE GESTION DES RESSOURCES
         * 
         ********************/
        
        /**********
         * 
         * Méthode pour ajouter le contenu dansle dossier ressources
         * 
         * $idTempo / string => va contenir l'ID de la slide, est remis a zéro pour chaque boucle
         *                      n'est utilisable que dans cette méthode, ne pas ajouter manuellement une valeur dans $idTempo
         * 
         * La méthode sanitize_file_name est utiliser dans cette méthode pour supprimer les caractères inutiles
         * Pour plus d'information allez voir plus bas dans la méthode sanitize_file_name
         * 
         **********/          
        public function ajoutContenuDossierFichier() {

            foreach($this->tabExterne as $key => $value) {
                foreach($value as $key2 => $value2) {
                    if($key2 === "Image") {
                        if ($value2 === "") {
                            $value2 = " ";
                        }else {
                            if ($content = file_get_contents($value2)) {
                                $info = pathinfo($value2);
                                if (file_put_contents("ressources/".$this->sanitize_file_name($info['basename']), $content)) { 
                                }else {
                                    array_push($this->erreurs, "ajoutContenu : Le file_put_contents n'est pas valide.");
                                }
                            }else {
                                array_push($this->erreurs, "ajoutContenu : Le file_get_contents n'est pas valide.");
                            }
                        }
                    }
                }
            }
        }

        /**********
         * 
         * Méthode pour supprimer le dossier ressources et les fichiers qui son à l'intérieur
         * 
         * Constante CHEMIN_DOSSIER_RESSOURCES => chemin du dossier ressources (config.php / CONSTANTES DANS LE FICHIER RESSOURCE.CLASS.PHP)
         * $dossier => contient le CHEMIN_DOSSIER_RESSOURCES
         * $dir_iterator => crée une instance récursive avec comme paramètre $dossier
         * $iterator => crée une instance récursive avec comme paramètre $dir_iterator et RecursiveIteratorIterator::CHILD_FIRST
         * $fichier => contient le dossier/fichier actuel et qui est analiser
         * 
         * Utilise un système de récursivité pour allez chercher les enfant les plus éloignier
         * pour ensuite remonté tous le dossier en suppriment en fure-et à mesure
         * 
         * IMPORTANT => Se code à était trouver sur le site StackOverflow
         * 
         **********/
        public function supprimerContenu() {

            $dossier = CHEMIN_DOSSIER_RESSOURCES;
            $dir_iterator = new RecursiveDirectoryIterator($dossier);
            $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);

            // On supprime chaque dossier et chaque fichier du dossier cible
            foreach($iterator as $fichier){
                $fichier->isDir() ? rmdir($fichier) : unlink($fichier);
            }
        }

        /**********
         * Sanitizes a filename replacing whitespace with dashes
         *
         * Removes special characters that are illegal in filenames on certain
         * operating systems and special characters requiring special escaping
         * to manipulate at the command line. Replaces spaces and consecutive
         * dashes with a single dash. Trim period, dash and underscore from beginning
         * and end of filename.
         *
         * @since 2.1.0
         *
         * @param string $filename The filename to be sanitized
         * @return string The sanitized filename
         * 
         * FR / Méthode qui permet de supprimer les caractéres spéciaux, dans se cas il est utiliser
         *      pour rendre lisible les nom des ressources.
         *      
         *      $string / string => contient le string à étudier
         *      $force_lowercase / boolean = true => force $string a mettre tous en lowercase si en "true"
         *      $anal / boolean = false => pour l'analyse du $string
         *      $strip / array(string) => contient tout les caractères spéciaux
         *      $clean / multi => va prendre plusieur valeurs dans la méthode
         *                          mais le pricipe de base est de clean le $string
         * 
         *      IMPORTANT !!! Il à était trouver sur le site StackOverflow.
         **********/
        public function sanitize_file_name($string, $force_lowercase = true, $anal = false) {
            $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                           "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                           "â€”", "â€“", ",", "<", ">", "/", "?");
            $clean = trim(str_replace($strip, "", strip_tags($string)));
            $clean = preg_replace('/\s+/', "-", $clean);
            $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
            return ($force_lowercase) ?
                (function_exists('mb_strtolower')) ?
                    mb_strtolower($clean, 'UTF-8') :
                    strtolower($clean) :
                $clean;
        }

        /********************
         * 
         * LE CONSTRUCTEUR DE L'AGO, POUR L'AFFICHAGE DANS LE FICHIER BASEPAGE.CLASS.PHP
         * 
         ********************/

        public function __construct() {

            $return = array();
            $this->erreurs = array();
            $this->recupJSONInterne();
            if ($this->internalJson) {
                $this->jsonDecode($this->parsed_InternalJson, $this->internalJson);
                if ($this->parsed_InternalJson) {
                    $this->traitementTableau($this->parsed_InternalJson, $this->tabInterne);
                }
            }
            $this->connexionInternet();
            if($this->is_conn) {
                $this->recupJSONExterne();
                if ($this->externalJson) {
                    $this->jsonDecode($this->parsed_ExternalJson, $this->externalJson);
                    if ($this->parsed_ExternalJson) {
                        $this->traitementTableau($this->parsed_ExternalJson, $this->tabExterne);
                    }
                }
            }
            $this->tabRessourcesInterne();
            $this->tabRessourcesInterneForm();
            $this->autorisationAffichage();
                if ($this->miseAJourInterne) {
                    $this->supprimerContenu();
                    $this->ajoutContenuDossierFichier(); 
                    $this->ajoutContenuJsonInterne();
                }
                if ($this->resultatAutorisationAffichage) {
                    $this->constructionTabSlide();
                    $this->constructionTabSlideListeForm();
                }else {
                    array_push($this->erreurs, "IL N'Y A RIEN A AFFICHER");
                }
            $this->tabErreurs();
        }
    }

    /********************
     * 
     * FONCTION DE VISUALISATION POUR DEBUG
     * 
     ********************/

    /**********
     * 
     * Fonction pour afficher la source ciblé avec un print_r
     * 
     * Moin détaillé qu'il var_dump mais parfois plus ccompréhensible
     * 
     * $source => contient votre cible
     * 
     **********/
    function p($source) {

        echo "<pre>";
        print_r($source);
        echo "</pre>";
    }

    /**********
     * 
     * Fonction pour afficher la source ciblé avec un var_dump
     * 
     * Le plus précie, mais parfois trops fourni en information
     * 
     * $source => contient votre cible
     * 
     **********/
    function v($source) {

        echo "<pre>";
        var_dump($source);
        echo "</pre>";
    }
?>