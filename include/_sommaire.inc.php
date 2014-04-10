<?php
/** 
 * Contient la division pour le sommaire, sujet à des variations suivant la 
 * connexion ou non d'un utilisateur, et dans l'avenir, suivant le type de cet utilisateur 
 * @todo  RAS
 */

?>
    <!-- Division pour le sommaire -->
    <div id="menuGauche">
     <div id="infosUtil">
    <?php      
      if (estVisiteurConnecte() ) {
          $idUser = obtenirIdUserConnecte() ;
          $lgUser = obtenirDetailVisiteur($idConnexion, $idUser);
          $nom = $lgUser['nom'];
          $prenom = $lgUser['prenom'];
	  $type = $lgUser['type'];            
    ?>
        <h2>
    <?php  
            echo $nom . " " . $prenom ;
    ?>
        </h2>
    <?php
       }
    ?>  
      </div>  
<?php      
  if (estVisiteurConnecte ()) {
	if($type==0)//C'est un visiteur qui se connecte
	{
?>
	<h3>Visiteur médical</h3>
        <ul id="menuList">
           <li class="smenu">
              <a href="cAccueil.php" title="Page d'accueil">Accueil</a>
           </li>
           <li class="smenu">
              <a href="cSeDeconnecter.php" title="Se déconnecter">Se déconnecter</a>
           </li>
           <li class="smenu">
              <a href="cSaisieFicheFrais.php" title="Saisie fiche de frais du mois courant">Saisie fiche de frais</a>
           </li>
           <li class="smenu">
              <a href="cConsultFichesFrais.php" title="Consultation de mes fiches de frais">Mes fiches de frais</a>
           </li>
         </ul>
        <?php
          // affichage des éventuelles erreurs déjà détectées
          if ( nbErreurs($tabErreurs) > 0 ) {
              echo toStringErreurs($tabErreurs) ;
          }
	}
	if($type==1)//C'est un comptable qui se connecter
	{?>
	<h3>Comptable</h3>
	<ul id="menuList">
	    <li class="smenu">
		<a href="cAccueilCompta.php" title="Page d'acceuil">Acceuil</a>
	   </li>
	   <li>
		<a href="cSeDeconnecter.php" title="Se déconnecter">Se déconnecter</a>
	   </li>
	   <li>
		<a href="cValideFicheFrais.php" title="Valide des fiches de frais">Valide fiche de frais</a>
	  </li>
	  <li>
		<a href="cSuivreFicheFrais.php" title="Suivi du paiement de fiche de frais">Suivi paiement fiche de fais</a>
	  </li>
	</ul>
	<?
	}
  }
        ?>
    </div>
    
