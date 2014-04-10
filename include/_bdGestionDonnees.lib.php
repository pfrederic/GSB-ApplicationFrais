<?php
/** 
 * Regroupe les fonctions d'acc�s aux donn�es.
 * @package default
 * @author Arthur Martin
 * @todo  RAS
 */

/** 
 * Se connecte au serveur de donn�es MySql.                      
 * Se connecte au serveur de donn�es MySql � partir de valeurs
 * pr�d�finies de connexion (h�te, compte utilisateur et mot de passe). 
 * Retourne l'identifiant de connexion si succ�s obtenu, le bool�en false 
 * si probl�me de connexion.
 * @return resource identifiant de connexion
 */
function connecterServeurBD() {
    $hote = "localhost";
    $login = "technicien";
    $mdp = "ini01";
    return mysql_connect($hote, $login, $mdp);
}

/**
 * S�lectionne (rend active) la base de donn�es.
 * S�lectionne (rend active) la BD pr�d�finie gsb_frais sur la connexion
 * identifi�e par $idCnx. Retourne true si succ�s, false sinon.
 * @param resource $idCnx identifiant de connexion
 * @return boolean succ�s ou �chec de s�lection BD 
 */
function activerBD($idCnx) {
    $bd = "GSB";
    $query = "SET CHARACTER SET utf8";
    // Modification du jeu de caract�res de la connexion
    $res = mysql_query($query, $idCnx); 
    $ok = mysql_select_db($bd, $idCnx);
    return $ok;
}

/** 
 * Ferme la connexion au serveur de donn�es.
 * Ferme la connexion au serveur de donn�es identifi�e par l'identifiant de 
 * connexion $idCnx.
 * @param resource $idCnx identifiant de connexion
 * @return void  
 */
function deconnecterServeurBD($idCnx) {
    mysql_close($idCnx);
}

/**
 * Echappe les caract�res sp�ciaux d'une cha�ne.
 * Envoie la cha�ne $str �chapp�e, c�d avec les caract�res consid�r�s sp�ciaux
 * par MySql (tq la quote simple) pr�c�d�s d'un \, ce qui annule leur effet sp�cial
 * @param string $str cha�ne � �chapper
 * @return string cha�ne �chapp�e 
 */    
function filtrerChainePourBD($str) {
    if ( ! get_magic_quotes_gpc() ) { 
        // si la directive de configuration magic_quotes_gpc est activ�e dans php.ini,
        // toute cha�ne re�ue par get, post ou cookie est d�j� �chapp�e 
        // par cons�quent, il ne faut pas �chapper la cha�ne une seconde fois                              
        $str = mysql_real_escape_string($str);
    }
    return $str;
}

/** 
 * Fournit les informations sur un visiteur demand�. 
 * Retourne les informations du visiteur d'id $unId sous la forme d'un tableau
 * associatif dont les cl�s sont les noms des colonnes(id, nom, prenom).
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
 * Fournit le tableau du d�tail de tous les visiteurs
 * de la base de donn�es
*/
function obtenirDetailToutVisiteur()
{
	$req="select id, nom, prenom from Visiteur where type=0";
	$tabDetailToutVisiteur=mysql_query($req);
	return $tabDetailToutVisiteur;
}

/**
 * Fournit les mois pour lesquelles le visiteur � une fiche de frais
 * ant�rieur au mois actuel.
 * @param string $unIdVisiteur id d'un visiteur
*/
function obtenirMoisFicheFraisAnt($unIdVisiteur)
{
	$req="select mois from FicheFrais where idVisiteur='" . $_SESSION['visiteur'] . "' and idEtat='CL' and substring(mois,5,2)<extract(month from current_date)";
	$tabMoisFicheFraisAnt=mysql_query($req);
	return $tabMoisFicheFraisAnt;
}

/**
 * Fournit toutes les fiches de frais de la base de donn�es qui sont
 * en �tat de validation et attente de remboursement. Le nom et le pr�nom du visiteur
 * ainsi que le mois de la fiche de frais. Les donn�es seront rang�s dans l'ordre croissant
 * du nom, du pr�nom et du mois
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
 * sous la forme d'un tableau associatif dont les cl�s sont les noms des colonnes
 * (nbJustitificatifs, idEtat, libelleEtat, dateModif, montantValide).
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demand� (MMAAAA)
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
 * V�rifie si une fiche de frais existe ou non. 
 * Retourne true si la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur existe, false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demand� (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return bool�en existence ou non de la fiche de frais
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
 * Fournit le mois de la derni�re fiche de frais d'un visiteur.
 * Retourne le mois de la derni�re fiche de frais du visiteur d'id $unIdVisiteur.
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
 * Ajoute une nouvelle fiche de frais et les �l�ments forfaitis�s associ�s, 
 * Ajoute la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur, avec les �l�ments forfaitis�s associ�s dont la quantit� initiale
 * est affect�e � 0. Cl�t �ventuellement la fiche de frais pr�c�dente du visiteur. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demand� (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return void
 */
function ajouterFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    // modification de la derni�re fiche de frais du visiteur
    $dernierMois = obtenirDernierMoisSaisi($idCnx, $unIdVisiteur);
	$laDerniereFiche = obtenirDetailFicheFrais($idCnx, $dernierMois, $unIdVisiteur);
	if ( is_array($laDerniereFiche) && $laDerniereFiche['idEtat']=='CR'){
		modifierEtatFicheFrais($idCnx, $dernierMois, $unIdVisiteur, 'CL');
	}
    
    // ajout de la fiche de frais � l'�tat Cr��
    $requete = "insert into FicheFrais (idVisiteur, mois, nbJustificatifs, montantValide, idEtat, dateModif) values ('" 
              . $unIdVisiteur 
              . "','" . $unMois . "',0,NULL, 'CR', '" . date("Y-m-d") . "')";
    mysql_query($requete, $idCnx);
    
    // ajout des �l�ments forfaitis�s
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
 * Retourne le texte de la requ�te select concernant les mois pour lesquels un 
 * visiteur a une fiche de frais. 
 * 
 * La requ�te de s�lection fournie permettra d'obtenir les mois (AAAAMM) pour 
 * lesquels le visiteur $unIdVisiteur a une fiche de frais. 
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requ�te select
 */                                                 
function obtenirReqMoisFicheFrais($unIdVisiteur) {
    $req = "select FicheFrais.mois as mois from  FicheFrais where FicheFrais.idvisiteur ='"
            . $unIdVisiteur . "' order by FicheFrais.mois desc ";
    return $req ;
}  
                  
/**
 * Retourne le texte de la requ�te select concernant les �l�ments forfaitis�s 
 * d'un visiteur pour un mois donn�s. 
 * 
 * La requ�te de s�lection fournie permettra d'obtenir l'id, le libell� et la
 * quantit� des �l�ments forfaitis�s de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demand� (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requ�te select
 */                                                 
function obtenirReqEltsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select idFraisForfait, libelle, quantite from LigneFraisForfait
              inner join FraisForfait on FraisForfait.id = LigneFraisForfait.idFraisForfait
              where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    return $requete;
}

/**
 * Retourne le texte de la requ�te select concernant les �l�ments hors forfait 
 * d'un visiteur pour un mois donn�s. 
 * 
 * La requ�te de s�lection fournie permettra d'obtenir l'id, la date, le libell� 
 * et le montant des �l�ments hors forfait de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demand� (AAAA-MM)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requ�te select
 */                                                 
function obtenirReqEltsHorsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select id, date, libelle, montant from LigneFraisHorsForfait
              where idVisiteur='" . $unIdVisiteur 
              . "' and date like '" . $unMois . "___'";
    return $requete;
}
/**
 * Retourne le nombre de justificatif fournit par le visiteur m�dical
 *
 * La requ�te de s�lection permettra d'obtenir le nombre de justificatif,
 * selon le choix du visiteur m�dical et du mois choisi
 * @param string $unMois mois demand� (MMAAAA)
 * @param string $unIdVisiteur id visiteur
 * @return string texte de la requ�te select
 */
function obtenirNbJustificatif($unMois, $unIdVisiteur) {
    $requete = "select nbJustificatifs from FicheFrais where idVisiteur='" . $unIdVisiteur . "'
		and mois like '" . $unMois . "';";
    return $requete;
}

/**
 * Refuse une ligne hors forfait.
 * Ajoute le mot "REFUSE" devant le libelle du frais hors forfait.
 * Ceci indique le frais ne sera pas rembours�.
 * @param resource $idCnx identifiant de connexion
 * @param int     $unIdLigneHF  id de la ligne hors forfait
 * @param strinfg $unLibLigneHF chaine � laquelle on ajoute "REFUSE" pour stipul� que le fais hors forfait ne sera pas rembours�.
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
 * Ins�re dans la BD la ligne hors forfait de libell� $unLibelleHF du montant 
 * $unMontantHF ayant eu lieu � la date $uneDateHF pour la fiche de frais du mois
 * $unMois du visiteur d'id $unIdVisiteur
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demand� (AAMMMM)
 * @param string $unIdVisiteur id du visiteur
 * @param string $uneDateHF date du frais hors forfait
 * @param string $unLibelleHF libell� du frais hors forfait 
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
 * Modifie les quantit�s des �l�ments forfaitis�s d'une fiche de frais. 
 * Met � jour les �l�ments forfaitis�s contenus  
 * dans $desEltsForfaits pour le visiteur $unIdVisiteur et
 * le mois $unMois dans la table LigneFraisForfait, apr�s avoir filtr� 
 * (annul� l'effet de certains caract�res consid�r�s comme sp�ciaux par 
 *  MySql) chaque donn�e
 * @param resource $idCnx identifiant de connexion 
 * @param string $unMois mois demand� (AAAAMM) 
 * @param string $unIdVisiteur  id visiteur
 * @param array $desEltsForfait tableau des quantit�s des �l�ments hors forfait
 * avec pour cl�s les identifiants des frais forfaitis�s 
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
 * Modifie les valeurs des �l�ments hors forfait d'une fiche de frais.
 * Met � jour les �l�ments hors forfait contenus dans
 * des $desEltsHorsForfaits pour le visiteur $unIdVisiteur et
 * le mois $unMois dans la table LigneFraisHorsForfait, apr�s avoir filtr�
 * (annul� l'effet de certains consid�r�s comme sp�ciaux par MySql) de chaque don�e
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur id visiteur
 * @param int    $unIdEltHorsForfait id de l'�l�ment hors forfait
 * @param date   $dateHF date de l'�l�ment hors forfait (JJ/MM/AAAA)
 * @param string $libelleHF libelle de l'�l�ment hors forfait
 * @param float  $montantHF montant de l'�l�ment hors forfait
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
 * Met � jour le nombre de justificatifs
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur id visiteur
 * @param string $unMois mois demand� (MMAAAA)
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
 * Contr�le les informations de connexion d'un utilisateur.
 * V�rifie si les informations de connexion $unLogin, $unMdp sont ou non valides.
 * Retourne les informations de l'utilisateur sous forme de tableau associatif 
 * dont les cl�s sont les noms des colonnes (id, nom, prenom, login, mdp)
 * si login et mot de passe existent, le bool�en false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unLogin login 
 * @param string $unMdp mot de passe 
 * @return array tableau associatif ou bool�en false 
 */
function verifierInfosConnexion($idCnx, $unLogin, $unMdp) {
    $unLogin = filtrerChainePourBD($unLogin);
    $unMdp = filtrerChainePourBD($unMdp);
    // le mot de passe est crypt� dans la base avec la fonction de hachage md5
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
 * Modifie l'�tat et la date de modification d'une fiche de frais 
 * Met � jour l'�tat de la fiche de frais du visiteur $unIdVisiteur pour
 * le mois $unMois � la nouvelle valeur $unEtat et passe la date de modif � 
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
 * elle est alors cr��e, et tous les frais forfait sont pass�s � z�ro.
 * @param int    $idFraisHorsForfait   id du frais hors forfait
 * @param date 	 $dateFraisHorsForfait date du frais hors forfait report�.
 * @param string $libelleFraisHorsForfait libelle du frais hors forfait
 * 
**/
function reportFraisHorsForfait($idFraisHorsForfait, $dateFraisHorsForfait, $libelleFraisHorsForfait, $idVisiteurFraisHorsForfait)
{
	//Si le mois actuel du frais hors forfait est d�cembre
	//il faut que le mois passe � 01 (janvier)
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
 * Cr�ation �ventuelle d'une fiche de frais lors du report d'un frais hors forfait
 * Cela permet de cr�er une fiche de frais si elle n'existe pas, au moi du report
 * d'un frais hors forfait
 * @param int $dateFraisHorsForfait       date du report du frais hors forfait
 * @param int $idVisiteurFraisHorsForfait id du visiteur concern�s 
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
