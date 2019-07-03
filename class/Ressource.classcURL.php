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
        private $tabGetFormulaire;
        private $tabGetRadio;
        private $tabGetJS;
        private $tabGetRadioTime;
        private $tabGetJsCodeReload;
        private $tabGetCalTimeReload;

        private $tabInterne = false; // Tableau interne filtrer a partir du $parsed_InternalJson
        private $tabExterne = false; // Tableau externe filtrer a partir du $parsed_ExternalJson

        private $tabIdInterne; // Tableau pour les ID interne
        private $tabIdExterne; // Tableau pour les ID externe

        private $erreurs; // Tableau qui contient les messages d'erreurs
        $GlobalFileHandle = null;

        /********************
         * 
         * PARTIE INITIALISATION DES TABLEAUX
         * 
         ********************/

        /* Va contenir les informations pour l'affichage du slider(json interne) */
        public function tabRessourcesInterne() {

            $this->tab = array();
        }

        /* Va contenir les informations pour l'affichage de la liste dans l'inteface utilisateur */
        public function tabRessourcesInterneForm() {

            $this->tabForm = array();
        }

        /* Va contenir les informations pour l'affichage du formulaire(methode GET) */
        public function tabRessourcesGetForm() {

            $this->tabGetFormulaire = array();
        }

        /* Va contenir les information pour créer l'affichage des boutons radio */
        public function tabGetFormulaireRadio() {

            $this->tabGetRadio = array();
        }

        /* Va contenir les informations pour crée la ligne de code Jquery autoplay */
        public function tabGetCodeJS() {

            $this->tabGetCodeJS = array();
        }

        /* Va contenir les information pour crée les bouton radio pour le reload */
        public function tabGetButtonRadioReload() {

            $this->tabGetRadioTime = array();
        }

        public function tabGetCalTime() {
            
            $this->tabGetCalTimeReload = array();
        }

        /********************
         * 
         * PARTIE RETOUR DE DONNEES
         * 
         ********************/

        /* Permet de retourner les données du tableau pour les boutons radio */
        public function getTabButtonRadio() {

            return $this->tabGetRadio;
        }

        /* Permet de retourner les données du tableau pour le code JS/Jquery(autoplaySpeed) */
        public function getTabCodeJSSpeedPlay() {

            return $this->tabGetCodeJS;
        }

        /* Permet de retourner les données du tableau pour le slider */
        public function getTab() {

            return $this->tab ;
        }

        /* Permet de retourner les données du tableau pour la liste du formulaire */
        public function getTabForm() {

            return $this->tabForm;
        }

        /* Permet de retourner les données du tableau pour système de reload du formulaire */
        public function getTabRadioReload() {

            return $this->tabGetRadioTime;
        }
        
        /************************************************************
         * 
         * PARTIE GESTION DE LA CONNEXION INTERNET
         * 
         ************************************************************/

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

        /************************************************************
         * 
         * PARTIE RECUPERATION DES DONNEES
         * 
         ************************************************************/

        /* Recupére les données du GET et les push dans un tableau */
        public function recupFormGet() {

            $temps = htmlspecialchars($_GET['Temps']);
            $reload_slide = htmlspecialchars($_GET['reload']);
            $time_reload_slide = htmlspecialchars($_GET['Temps_reload']);

            array_push($this->tabGetFormulaire, $temps);
            array_push($this->tabGetFormulaire, $reload_slide);
            array_push($this->tabGetFormulaire, $time_reload_slide);
        }

        /**********
         * 
         * Recupération du Json interne
         * 
         * Constante CHEMIN_INTERNE_JSON => le chemin du json interne (config.php / CONSTANTES DANS LE FICHIER RESSOURCE.CLASS.PHP)
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
               
        public function recupJSONExterne(LIEN_SLIDER_EXTERNE, '/var/www/html/json/slider_externe.json') {

            if ($this->externalJson = {
                global $GlobalFileHandle;
                 $GlobalFileHandle = fopen('/var/www/html/json/slider_externe.json', 'w+');
                 $ch = curl_init(LIEN_SLIDER_EXTERNE);
                    curl_setopt($ch, CURLOPT_USERPWD, 'Animateur2:anitamtam2');
                    curl_setopt($ch, CURLOPT_FILE, $GlobalFileHandle);
                    curl_setopt($ch, CURLOPT_PROTOCOLS, CURL_PROTO_HTTP);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($GlobalFileHandle);
            }else {
                array_push($this->erreurs, "recupJSONExterne : Le lien externe n'est pas valide.");
            }
        }

        /************************************************************
         * 
         * PARTIE SCRIPT JS POUR LE RELOAD DE LA PAGE
         * 
         * Contient les function liées au script Js de reload.
         * 
         ************************************************************/

        // Init de la variable en tableau.
        public function tabGetCodeJSReload() {

            $this->tabGetJsCodeReload = array();
        }
        
        // Création du script Js ne sera actif que si la valeur retourné par le formulaire est 'oui'.
        public function scriptJSTestReloadPage() {
            $codeReloadPage = '';

            foreach($this->tabGetFormulaire as $key => $value) {
                if($key === 1) {
                    if($value === oui) {
                        $codeReloadPage.='
                        $(document).ready(function() {
                            setTimeout(function(){ 
                                $.ajax({
                                    url: location.href,
                                    beforeSend: function( xhr ) {
                                        if ( console && console.log ) {
                                            console.log( "Mise à jour du contenus lancé");
                                          }
                                    }
                                }).done(function( data ) {
                                    if ( console && console.log ) {
                                      console.log( "Mise à jour du contenus");
                                    }
                                    location.reload(); 
                                })
                               
                            },';
                            foreach($this->tabGetCalTimeReload as $key => $value) {
                                $codeReloadPage .="$value";
                            }
                        $codeReloadPage.=');
                        });
                        </script>
                        ';
                    }
                }
            }
            array_push($this->tabGetJsCodeReload, $codeReloadPage);
        }

        // Retourne le tableau et permet d'envoyer le contenu du tableau dans la 'BasePage'.
        public function getTabJsCodeReload() {

            return $this->tabGetJsCodeReload;
        }

        /********************
         * 
         * PARTIE CREATION ET MISE EN PLACE DU FORMULAIRE
         * 
         ********************/

        public function calcuTempsReload() {
            $calTempMilliReload = 5;

            foreach($this->tabGetFormulaire as $key => $value) {
                if($key === 2) {
                    if(empty($value)) {
                        $calTempMilliReload = 180000;
                    }else {
                        $calcul = $value*60000;
                        $calTempMilliReload = $calcul;
                    }
                }
            }
            array_push($this->tabGetCalTimeReload,$calTempMilliReload);
        }
        
        public function creationRadioForm() {
            $formButtonRadio = '' ;

            foreach($this->tabGetFormulaire as $key => $value) {
                if($value === normale || $value === '') {
                    $formButtonRadio.='<input type="radio" name="time-slide" value="normale" id="normale" checked="checked" /> <label for="normale">normale</label>';
                    $formButtonRadio.='<input type="radio" name="time-slide" value="rapide" id="rapide" /> <label for="rapide">rapide</label>';
                    $formButtonRadio.='<input type="radio" name="time-slide" value="lent" id="lent" /> <label for="lent">lent</label>';
                }
                if($value === rapide) {
                    $formButtonRadio.='<input type="radio" name="time-slide" value="normale" id="normale"/> <label for="normale">normale</label>';
                    $formButtonRadio.='<input type="radio" name="time-slide" value="rapide" id="rapide" checked="checked"/> <label for="rapide">rapide</label>';
                    $formButtonRadio.='<input type="radio" name="time-slide" value="lent" id="lent" /> <label for="lent">lent</label>';
                }
                if($value === lent){
                    $formButtonRadio.='<input type="radio" name="time-slide" value="normale" id="normale"/> <label for="normale">normale</label>';
                    $formButtonRadio.='<input type="radio" name="time-slide" value="rapide" id="rapide" /> <label for="rapide">rapide</label>';
                    $formButtonRadio.='<input type="radio" name="time-slide" value="lent" id="lent" checked="checked"/> <label for="lent">lent</label>';
                }
            }
            array_push($this->tabGetRadio, $formButtonRadio);
        }

        public function creationSpeedJSCode() {
            $formSpeedJS = '';

            foreach($this->tabGetFormulaire as $key => $value) {
                if($key === 0) {
                    if($value ==='') {
                        $formSpeedJS.='autoplaySpeed: 5000,';
                    }else {
                        $formSpeedJS.='autoplaySpeed: '.$value.'000,';
                    }
                }
            }
            array_push($this->tabGetCodeJS,$formSpeedJS);
        }

        public function creationRadioTimeForm() {
            $radioTimeForm = '';

            foreach($this->tabGetFormulaire as $key => $value) {
                if($key === 1) {
                    if($value === non || $value === '') {
                        $radioTimeForm.='<select name="reload" id="autoReload">
                        <option value="non" selected>Non</option>
                        <option value="oui">Oui</option>
                        </select>';
                    }
                    if($value === oui) {
                        $radioTimeForm.='<select name="reload" id="autoReload">
                        <option value="non">Non</option>
                        <option value="oui" selected>Oui</option>
                        </select>';
                    }
                }
            }
            array_push($this->tabGetRadioTime,$radioTimeForm);
        }

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
         * Méthode pour le décodage du Json et récupération de ce qu'il retourne
         * 
         * La méthode a été factorisé avec deux paramètres pour éviter la répétition inutile
         * Il faudra donc appeler cette méthode deux fois dans le constructeur mais avec deux paramètres différents
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
         * Méthode pour le traitement de deux tableaux et la récupération de ce qu'il retourne
         * 
         * La méthode a été factorisé avec deux paramètres pour éviter la répétition inutile
         * Il faudra donc appeler cette méthode deux fois dans le constructeur mais avec deux paramètres différents
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
         * Méthode pour comparer les deux tableaux Json, et donc savoir s'il faut afficher ou non
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
                    // dans cette situation le tab interne doit être mise a jour, ressource y compris
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
				
				//var_dump($value) ;
				
				/*array (size=8)
  'title' => string 'Journée de la Terre - Opération Sanitas propre' (length=48)
  'date_publication' => string 'Lundi 15 avril 2019 - 10h03' (length=27)
  'id' => string '390' (length=3)
  '' => string '' (length=0)
  'Image' => string 'http://www.sanitamtam.fr/sites/default/files/field/image/FB_IMG_1555080324040_0.jpg' (length=83)
  'Lieu' => string 'POINdate_eventT (0.69943428039551 47.379796036008)' (length=40)
  'Corps' => string 'Le Jeudi 18 avril 2019, Régie Plus, en partenariat avec Pluriel(le)s et les acteurs du quartier, propose aux habitants du Sanitas de fêter la journée de la terre.
A...' (length=170)
  'Type_contenu' => string 'article' (length=7)*/
  
				$image ='' ;
			foreach($value as $key2 => $value2) {
                if ($key2 === "Image") {
                    $src = $info = pathinfo($value2);
                    if (empty($src['basename'])) {
                        $image = '<img src="'.CHEMIN_IMAGE_DEFAULT.'" />'; 
                    }else {
                        $image = '<img src="ressources/'.$this->sanitize_file_name($src['basename']).'" />';    
                    }
                }
            }
			$date_event = '' ;
			$type = 'article' ;
			if ($value['date_event'] !='') {
				$type = 'event' ;
				$date_event = '<h2>'.$value['date_event'].'</h2>' ;
				if (isset ($value['Nom_du_lieu']) and ($value['Nom_du_lieu'] !='')){
					$date_event .= '<h3>'.$value['Nom_du_lieu'].'</h3>' ;
				}
			}
	
				
				$slide .= '
					<article class="'.$type.'">
						<div class="container">
							<div class="gauche">
								<h1>'.$value['title'].'</h1>
								'.$date_event.'
								<p>'.$value['Corps'].'</p>
					 		</div>
							<div class="droite">'.$image.'</div>
						</div>
					</article>
					';
              //  $slide .= '<div class="box_texte_slide"><h2 class="texte_slide">'.$value['title'].'</h2></div>';
				//$slide .= '<div class="html"><div class="head"><div class="title"><div class="body">';
				//$slide .= '<div class="container"><h1 class="texte_slide">'.$value['title'].'</h1><p class="bow_texte_slide></p></div>';
           // $slide .= '<div class="box_date-slide">'.$value['date_publication'].'</div>';

                
                array_push($this->tab,$slide);
            }
            return ($this->tab);  
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
			/*					$image ='' ;
			foreach($value as $key2 => $value2) {
                if ($key2 === "Image") {
                    $src = $info = pathinfo($value2);
                    if (empty($src['basename'])) {
                        $image = '<img src="'.CHEMIN_IMAGE_DEFAULT.'" />'; 
                    }else {
                        $image = '<img src="ressources/'.$this->sanitize_file_name($src['basename']).'" />';    
                    }
                }
            }*/
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
            $this->tabRessourcesGetForm();
            $this->recupFormGet(); /* Recup. du GET */
            $this->tabGetCodeJSReload();
            $this->tabGetFormulaireRadio(); /* Recup des info pour l'affichage des boutons radio */
            $this->tabGetCodeJS(); /* Recup des info pour le code JS autoplay */
            $this->tabGetButtonRadioReload();
            $this->tabGetCalTime();
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
            $this->creationRadioForm(); /* Création des boutons radio */
            $this->creationRadioTimeForm();
            $this->calcuTempsReload();
            $this->creationSpeedJSCode(); /* Création du code Js pour l'autoplay */
            $this->tabRessourcesInterneForm(); /* Liste du formulaire */
            $this->scriptJSTestReloadPage();
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
