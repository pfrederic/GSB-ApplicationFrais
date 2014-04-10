<?php
/** 
 * Regroupe les fonctions d'accès aux données.
 * @package default
 * @author Arthur Martin
 * @todo  RAS
 */

/** 
 * Se connecte au serveur de données MySql.                      
 * Se connecte au serveur de données MySql à partir de valeurs
 * prédéfinies de connexion (hôte, compte utilisateur et mot de passe). 
 * Retourne l'identifiant de connexion si succès obtenu, le booléen false 
 * si problème de connexion.
 * @return resource identifiant de connexion
 */
function connecterServeurBD() {
    $hote = "localhost";
    $login = "technicien";
    $mdp = "ini01";
    return mysql_connect($hote, $login, $mdp);
}

/**
 * Sélectionne (rend active) la base de données.
 * Sélectionne (rend active) la BD prédéfinie gsb_frais sur la connexion
 * identifiée par $idCnx. Retourne true si succès, false sinon.
 * @param resource $idCnx identifiant de connexion
 * @return boolean succès ou échec de sélection BD 
 */
function activerBD($idCnx) {
    $bd = "GSB";
    $query = "SET CHARACTER SET utf8";
    // Modification du jeu de caractères de la connexion
    $res = mysql_query($query, $idCnx); 
    $ok = mysql_select_db($bd, $idCnx);
    return $ok;
}

/** 
 * Ferme la connexion au serveur de données.
 * Ferme la connexion au serveur de données identifiée par l'identifiant de 
 * connexion $idCnx.
 * @param resource $idCnx identifiant de connexion
 * @return void  
 */
function deconnecterServeurBD($idCnx) {
    mysql_close($idCnx);
}

/**
 * Echappe les caractères spéciaux d'une chaîne.
 * Envoie la chaîne $str échappée, càd avec les caractères considérés spéciaux
 * par MySql (tq la quote simple) précédés d'un \, ce qui annule leur effet spécial
 * @param string $str chaîne à échapper
 * @return string chaîne échappée 
 */    
function filtrerChainePourBD($str) {
    if ( ! get_magic_quotes_gpc() ) { 
        // si la directive de configuration magic_quotes_gpc est activée dans php.ini,
        // toute chaîne reçue par get, post ou cookie est déjà échappée 
        // par conséquent, il ne faut pas échapper la chaîne une seconde fois                              
        $str = mysql_real_escape_string($str);
    }
    return $str;
}

/** 
 * Fournit les informations sur un visiteur demandé. 
 * Retourne les informations du visiteur d'id $unId sous la forme d'un tableau
 * associatif dont les clés sont les noms des colonnes(id, nom, prenom).
 * @param resource $idCnx identifiant de connexion
 * @param string $unId id de l'utilisateur
 * @return array  tableau associatif du visiteur
 */
function obtenirDetailVisiteur($idCnx, $unId) {
    $id = filtrerChainePourBD($unId);
    $requete = "select id, nom, prenom, type from Visiteur where id='" . $unId . "'";
    $idJeuRes = mysql_query($requete, $idCnx);  
    $ligne = false;     
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }
    return $ligne ;
}

/**
 * Fournit le tableau du détail de tous les visiteurs
 * de la base de données
*/
function obtenirDetailToutVisiteur()
{
	$req="select id, nom, prenom from Visiteur where type=0";
	$tabDetailToutVisiteur=mysql_query($req);
	return $tabDetailToutVisiteur;
}

/**
 * Fournit les mois pour lesquelles le visiteur à une fiche de frais
 * antérieur au mois actuel.
 * @param string $unIdVisiteur id d'un visiteur
*/
function obtenirMoisFicheFraisAnt($unIdVisiteur)
{
	$req="select mois from FicheFrais where idVisiteur='" . $_SESSION['visiteur'] . "' and idEtat='CL' and substring(mois,5,2)<extract(month from current_date)";
	$tabMoisFicheFraisAnt=mysql_query($req);
	return $tabMoisFicheFraisAnt;
}

/**
 * Fournit toutes les fiches de frais de la base de données qui sont
 * en état de validation et attente de remboursement. Le nom et le prénom du visiteur
 * ainsi que le mois de la fiche de frais. Les données seront rangés dans l'ordre croissant
 * du nom, du prénom et du mois
*/
function obtenirMoisFicheFraisValide()
{
	$req="select nom, prenom, idVisiteur, mois from Visiteur inner join FicheFrais on idVisiteur=id where idEtat='VA' order by nom, prenom, mois;";
	$tabMoisFicheFraisValide=mysql_query($req);
	return $tabMoisFicheFraisValide;
}

/** 
 * Fournit les informations d'une fiche de frais. 
 * Retourne les informations de la fiche de frais du mois de $unMois (MMAAAA)
 * sous la forme d'un tableau associatif dont les clés sont les noms des colonnes
 * (nbJustitificatifs, idEtat, libelleEtat, dateModif, montantValide).
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return array tableau associatif de la fiche de frais
 */
function obtenirDetailFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $ligne = false;
    $requete="select IFNULL(nbJustificatifs,0) as nbJustificatifs, Etat.id as idEtat, libelle as libelleEtat, dateModif, montantValide 
    from FicheFrais inner join Etat on idEtat = Etat.id 
    where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    $idJeuRes = mysql_query($requete, $idCnx);  
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
    }        
    mysql_free_result($idJeuRes);
    
    return $ligne ;
}
              
/** 
 * Vérifie si une fiche de frais existe ou non. 
 * Retourne true si la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur existe, false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return booléen existence ou non de la fiche de frais
 */
function existeFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select idVisiteur from FicheFrais where idVisiteur='" . $unIdVisiteur . 
              "' and mois='" . $unMois . "'";
    $idJeuRes = mysql_query($requete, $idCnx);  
    $ligne = false ;
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }        
    
    // si $ligne est un tableau, la fiche de frais existe, sinon elle n'exsite pas
    return is_array($ligne) ;
}

/** 
 * Fournit le mois de la dernière fiche de frais d'un visiteur.
 * Retourne le mois de la dernière fiche de frais du visiteur d'id $unIdVisiteur.
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur id visiteur  
 * @return string dernier mois sous la forme AAAAMM
 */
function obtenirDernierMoisSaisi($idCnx, $unIdVisiteur) {
	$requete = "select max(mois) as dernierMois from FicheFrais where idVisiteur='" .
            $unIdVisiteur . "'";
	$idJeuRes = mysql_query($requete, $idCnx);
    $dernierMois = false ;
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        $dernierMois = $ligne["dernierMois"];
        mysql_free_result($idJeuRes);
    }        
	return $dernierMois;
}

/** 
 * Ajoute une nouvelle fiche de frais et les éléments forfaitisés associés, 
 * Ajoute la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur, avec les éléments forfaitisés associés dont la quantité initiale
 * est affectée à 0. Clôt éventuellement la fiche de frais précédente du visiteur. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return void
 */
function ajouterFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    // modification de la dernière fiche de frais du visiteur
    $dernierMois = obtenirDernierMoisSaisi($idCnx, $unIdVisiteur);
	$laDerniereFiche = obtenirDetailFicheFrais($idCnx, $dernierMois, $unIdVisiteur);
	if ( is_array($laDerniereFiche) && $laDerniereFiche['idEtat']=='CR'){
		modifierEtatFicheFrais($idCnx, $dernierMois, $unIdVisiteur, 'CL');
	}
    
    // ajout de la fiche de frais à l'état Créé
    $requete = "insert into FicheFrais (idVisiteur, mois, nbJustificatifs, montantValide, idEtat, dateModif) values ('" 
              . $unIdVisiteur 
              . "','" . $unMois . "',0,NULL, 'CR', '" . date("Y-m-d") . "')";
    mysql_query($requete, $idCnx);
    
    // ajout des éléments forfaitisés
    $requete = "select id from FraisForfait";
    $idJeuRes = mysql_query($requete, $idCnx);
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        while ( is_array($ligne) ) {
            $idFraisForfait = $ligne["id"];
            // insertion d'une ligne frais forfait dans la base
            $requete = "insert into LigneFraisForfait (idVisiteur, mois, idFraisForfait, quantite)
                        values ('" . $unIdVisiteur . "','" . $unMois . "','" . $idFraisForfait . "',0)";
            mysql_query($requete, $idCnx);
            // passage au frais forfait suivant
            $ligne = mysql_fetch_assoc ($idJeuRes);
        }
        mysql_free_result($idJeuRes);       
    }        
}

/**
 * Retourne le texte de la requête select concernant les mois pour lesquels un 
 * visiteur a une fiche de frais. 
 * 
 * La requête de sélection fournie permettra d'obtenir les mois (AAAAMM) pour 
 * lesquels le visiteur $unIdVisiteur a une fiche de frais. 
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requête select
 */                                                 
function obtenirReqMoisFicheFrais($unIdVisiteur) {
    $req = "select FicheFrais.mois as mois from  FicheFrais where FicheFrais.idvisiteur ='"
            . $unIdVisiteur . "' order by FicheFrais.mois desc ";
    return $req ;
}  
                  
/**
 * Retourne le texte de la requête select concernant les éléments forfaitisés 
 * d'un visiteur pour un mois donnés. 
 * 
 * La requête de sélection fournie permettra d'obtenir l'id, le libellé et la
 * quantité des éléments forfaitisés de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requête select
 */                                                 
function obtenirReqEltsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select idFraisForfait, libelle, quantite from LigneFraisForfait
              inner join FraisForfait on FraisForfait.id = LigneFraisForfait.idFraisForfait
              where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    return $requete;
}

/**
 * Retourne le texte de la requête select concernant les éléments hors forfait 
 * d'un visiteur pour un mois donnés. 
 * 
 * La requête de sélection fournie permettra d'obtenir l'id, la date, le libellé 
 * et le montant des éléments hors forfait de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demandé (AAAA-MM)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requête select
 */                                                 
function obtenirReqEltsHorsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select id, date, libelle, montant from LigneFraisHorsForfait
              where idVisiteur='" . $unIdVisiteur 
              . "' and date like '" . $unMois . "___'";
    return $requete;
}
/**
 * Retourne le nombre de justificatif fournit par le visiteur médical
 *
 * La requête de sélection permettra d'obtenir le nombre de justificatif,
 * selon le choix du visiteur médical et du mois choisi
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur
 * @return string texte de la requête select
 */
function obtenirNbJustificatif($unMois, $unIdVisiteur) {
    $requete = "select nbJustificatifs from FicheFrais where idVisiteur='" . $unIdVisiteur . "'
		and mois like '" . $unMois . "';";
    return $requete;
}

/**
 * Refuse une ligne hors forfait.
 * Ajoute le mot "REFUSE" devant le libelle du frais hors forfait.
 * Ceci indique le frais ne sera pas remboursé.
 * @param resource $idCnx identifiant de connexion
 * @param int     $unIdLigneHF  id de la ligne hors forfait
 * @param strinfg $unLibLigneHF chaine à laquelle on ajoute "REFUSE" pour stipulé que le fais hors forfait ne sera pas remboursé.
 * @return void
 */
function refuserLigneHF($idCnx, $unIdLigneHF, $unLibLigneHF) {
    $unLibLigneHF="REFUSE " . $unLibLigneHF;
    $requete = "update LigneFraisHorsForfait set libelle = '" . $unLibLigneHF . "' where id = " . $unIdLigneHF . ";";
    mysql_query($requete, $idCnx);
}

/**
 * Supprime une ligne hors forfait
 * Le visiteur, pour la fiche de frais du mois courant, peut retirer un frais hors forfait
 *
 * @param resource $idCnx identifiant de connexion
 * @param int $unIdLigneHF id de la ligne hors forfait
 * @return void
 */
function supprimerLigneHF($idCnx, $unIdLigneHF)
{
    $requete = "delete from LigneFraisHorsForfait where id= " . $unIdLigneHF . ";";
    mysql_query($requete, $idCnx);
}

/**
 * Ajoute une nouvelle ligne hors forfait.
 * Insère dans la BD la ligne hors forfait de libellé $unLibelleHF du montant 
 * $unMontantHF ayant eu lieu à la date $uneDateHF pour la fiche de frais du mois
 * $unMois du visiteur d'id $unIdVisiteur
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandé (AAMMMM)
 * @param string $unIdVisiteur id du visiteur
 * @param string $uneDateHF date du frais hors forfait
 * @param string $unLibelleHF libellé du frais hors forfait 
 * @param double $unMontantHF montant du frais hors forfait
 * @return void
 */
function ajouterLigneHF($idCnx, $unMois, $unIdVisiteur, $uneDateHF, $unLibelleHF, $unMontantHF) {
    $unLibelleHF = filtrerChainePourBD($unLibelleHF);
    $uneDateHF = filtrerChainePourBD(convertirDateFrancaisVersAnglais($uneDateHF));
    $unMois = filtrerChainePourBD($unMois);
    $requete = "insert into LigneFraisHorsForfait(idVisiteur, date, libelle,  montant) 
                values ('" . $unIdVisiteur . "','" . $uneDateHF . "','" . $unLibelleHF . "'," . $unMontantHF .")";
    mysql_query($requete, $idCnx);
}

/**
 * Modifie les quantités des éléments forfaitisés d'une fiche de frais. 
 * Met à jour les éléments forfaitisés contenus  
 * dans $desEltsForfaits pour le visiteur $unIdVisiteur et
 * le mois $unMois dans la table LigneFraisForfait, après avoir filtré 
 * (annulé l'effet de certains caractères considérés comme spéciaux par 
 *  MySql) chaque donnée
 * @param resource $idCnx identifiant de connexion 
 * @param string $unMois mois demandé (AAAAMM) 
 * @param string $unIdVisiteur  id visiteur
 * @param array $desEltsForfait tableau des quantités des éléments hors forfait
 * avec pour clés les identifiants des frais forfaitisés 
 * @return void  
 */
function modifierEltsForfait($idCnx, $unMois, $unIdVisiteur, $desEltsForfait) {
    $unMois=filtrerChainePourBD($unMois);
    $unIdVisiteur=filtrerChainePourBD($unIdVisiteur);
    foreach ($desEltsForfait as $idFraisForfait => $quantite) {
        $requete = "update LigneFraisForfait set quantite = " . $quantite . "
		    where idVisiteur = '" . $unIdVisiteur . "' and mois = '" . $unMois . "'
		    and idFraisForfait='".$idFraisForfait ."';";
     	mysql_query($requete, $idCnx) or die($requete);
    }
}

/**
 * Modifie les valeurs des éléments hors forfait d'une fiche de frais.
 * Met à jour les éléments hors forfait contenus dans
 * des $desEltsHorsForfaits pour le visiteur $unIdVisiteur et
 * le mois $unMois dans la table LigneFraisHorsForfait, après avoir filtré
 * (annulé l'effet de certains considérés comme spéciaux par MySql) de chaque donée
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur id visiteur
 * @param int    $unIdEltHorsForfait id de l'élément hors forfait
 * @param date   $dateHF date de l'élément hors forfait (JJ/MM/AAAA)
 * @param string $libelleHF libelle de l'élément hors forfait
 * @param float  $montantHF montant de l'élément hors forfait
 * @return void
 */
function modifierEltHorsForfait($idCnx, $unIdVisiteur, $unIdEltHorsForfait, $dateHF, $libelleHF, $montantHF) {
	$dateHF=convertirDateFrancaisVersAnglais($dateHF);
	$unIdVisiteur=filtrerChainePourBD($unIdVisiteur);
	$requete="update LigneFraisHorsForfait set date = '" . $dateHF . "',
		  libelle = '" . $libelleHF . "', montant = '" . $montantHF . "'
		  where id = '" . $unIdEltHorsForfait . "';";
	mysql_query($requete, $idCnx);
}

/**
 * Modifie le nombre de justificatifs le nombre de justificatifs
 * Met à jour le nombre de justificatifs
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur id visiteur
 * @param string $unMois mois demandé (MMAAAA)
 * @param int    $nbJustificatif nouvelle valeur du nombre de justificatif
 * @return void
 */
function modifierNbJustificatifs($idCnx, $unIdVisiteur, $unMois, $nouveauNbJustificatif) {
	$unIdVisiteur=filtrerChainePourBD($unIdVisiteur);
	$requete="update FicheFrais set nbJustificatifs = '" . $nouveauNbJustificatif . "' where idVisiteur = '" . $unIdVisiteur . "'
		  and mois like '%" . $unMois . "%';";
	mysql_query($requete, $idCnx);		
}

/**
 * Contrôle les informations de connexion d'un utilisateur.
 * Vérifie si les informations de connexion $unLogin, $unMdp sont ou non valides.
 * Retourne les informations de l'utilisateur sous forme de tableau associatif 
 * dont les clés sont les noms des colonnes (id, nom, prenom, login, mdp)
 * si login et mot de passe existent, le booléen false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unLogin login 
 * @param string $unMdp mot de passe 
 * @return array tableau associatif ou booléen false 
 */
function verifierInfosConnexion($idCnx, $unLogin, $unMdp) {
    $unLogin = filtrerChainePourBD($unLogin);
    $unMdp = filtrerChainePourBD($unMdp);
    // le mot de passe est crypté dans la base avec la fonction de hachage md5
    $req = "SELECT id, nom, prenom, login, mdp, type FROM Visiteur WHERE login='".$unLogin."' and mdp='" . $unMdp . "'";
    $idJeuRes = mysql_query($req, $idCnx);
    $ligne = false;
    if ( $idJeuRes ) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }
    return $ligne;
}

/**
 * Modifie l'état et la date de modification d'une fiche de frais 
 * Met à jour l'état de la fiche de frais du visiteur $unIdVisiteur pour
 * le mois $unMois à la nouvelle valeur $unEtat et passe la date de modif à 
 * la date d'aujourd'hui
 * @param string $unIdVisiteur 
 * @param string $unMois mois sous la forme aaaamm
 * @return void 
 */
function modifierEtatFicheFrais($unMois, $unIdVisiteur, $unEtat) {
    $requete = "update FicheFrais set idEtat = '" . $unEtat . 
               "', dateModif = now() where idVisiteur ='" .
               $unIdVisiteur . "' and mois = '". $unMois . "'";
    mysql_query($requete);
}

/**
 * Report d'un frais hors forfait sur la fiche de frais du mois suivant
 * Supprime le frais hors forfait de la fiche de frais, et la reporte
 * sur la fiche de frais du mois suivante. Si la fiche de frais n'existe pas
 * elle est alors créée, et tous les frais forfait sont passés à zéro.
 * @param int    $idFraisHorsForfait   id du frais hors forfait
 * @param date 	 $dateFraisHorsForfait date du frais hors forfait reporté.
 * @param string $libelleFraisHorsForfait libelle du frais hors forfait
 * 
**/
function reportFraisHorsForfait($idFraisHorsForfait, $dateFraisHorsForfait, $libelleFraisHorsForfait, $idVisiteurFraisHorsForfait)
{
	//Si le mois actuel du frais hors forfait est décembre
	//il faut que le mois passe à 01 (janvier)
	if(substr($dateFraisHorsForfait,5,2)==12)
	{
		$anne=substr($dateFraisHorsForfait,0,4);
		$annee=$anne+1;
		echo $annee;
		$dateFraisHorsForfait=substr_replace($dateFraisHorsForfait,"01",5,2);
		$dateFraisHorsForfait=substr_replace($dateFraisHorsForfait,$annee,0,3);
		echo $date;
		$req="update LigneFraisHorsForfait set date = '" . $dateFraisHorsForfait . "', libelle = ' REPORT ". $libelleFraisHorsForfait . "' where id = '" . $idFraisHorsForfait . "';";
	}
	else
	{
		$mois=substr($dateFraisHorsForfait,5,2);
		$mois=$mois+01;
		$dateFraisHorsForfait=substr_replace($dateFraisHorsForfait,$mois,5,2);
		$req="update LigneFraisHorsForfait set date = '" . $dateFraisHorsForfait . "', libelle = ' REPORT ". $libelleFraisHorsForfait . "' where id = '" . $idFraisHorsForfait . "';";
	}

	mysql_query($req);

	eventuelCreationFicheFrais($dateFraisHorsForfait, $idVisiteurFraisHorsForfait);
}

/**
 * Création éventuelle d'une fiche de frais lors du report d'un frais hors forfait
 * Cela permet de créer une fiche de frais si elle n'existe pas, au moi du report
 * d'un frais hors forfait
 * @param int $dateFraisHorsForfait       date du report du frais hors forfait
 * @param int $idVisiteurFraisHorsForfait id du visiteur concernés 
**/
function eventuelCreationFicheFrais($dateFraisHorsForfait, $idVisiteurFraisHorsForfait)
{
	$anneeMois=substr($dateFraisHorsForfait,0,4);
	if(substr($dateFraisHorsForfait,5,2)<=9)
	{
		$mois=substr($dateFraisHorsForfait,4,2);
		$mois=substr_replace($mois,"0",0,1);
		$anneeMois.=$mois;

	}
	else
	{
		$anneeMois.=substr($dateFraisHorsForfait,5,2);
	}
	
	$req="select idVisiteur, mois from FicheFrais where mois = '" . $anneeMois . "' and idVisiteur = '" . $idVisiteurFraisHorsForfait ."';";
	$resultat=mysql_query($req);
	$maLigne=mysql_fetch_array($resultat);
	if(empty($maLigne))
	{
		$req="insert into FicheFrais(idVisiteur, mois, nbJustificatifs, dateModif, idEtat) values('" . $idVisiteurFraisHorsForfait . "', " . $anneeMois . ", 0, now(), 'CR');";
		echo $req;
		mysql_query($req);
		$req="select id from FraisForfait;";
		$resultat=mysql_query($req);
		while($maLigne=mysql_fetch_array($resultat))
		{
			$req="insert into LigneFraisForfait values('" . $idVisiteurFraisHorsForfait . "', '" . $anneeMois . "', '" . $maLigne["id"] . "', 0);";
			mysql_query($req);
		}
	}
}
?>
