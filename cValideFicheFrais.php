<?
$repInclude = './include/';
require($repInclude . "_init.inc.php");

// page inaccessible si visiteur non connecté
if (!estVisiteurConnecte()) {
	header("Location: cSeConnecter.php");  
}
require($repInclude . "_enteteCompta.inc.html");
require($repInclude . "_sommaire.inc.php");

/*
<input type="text" size="3" name="eltFF['<?=$eltFraisForfait['idFraisForfait'];?>']" value="<?=$eltFraisForfait['quantite'];?>"
*/

//Si le formulaire des frais forfaitisés est posté
if (isset($_POST['btActionFormFraisForfait']))
{//début if
	modifierEltsForfait($idConnexion, $_SESSION['mois'], $_SESSION['visiteur'], $_POST['eltFF']);
	echo("<p class=\"info\">Modification(s) enregistrée(s)</p>");
}//fin if

//Si le formulaire des frais hors forfait est posté
if (isset($_POST['btActionFormFraisHorsForfait']))
{//début if
	modifierEltHorsForfait($idConnexion, $_SESSION['visiteur'],$_POST['hfId1'],$_POST['hfDate1'],$_POST['hfLib1'],$_POST['hfMont1']);
	echo("<p class=\"info\">Modification(s) enregistrée(s)</p>");	
}//fin if

//Si le formulaire du nombre de justificatif est posté
if (isset($_POST['btActionModifNbJustificatif']))
{//début if
	modifierNbJustificatifs($idConnexion, $_SESSION['visiteur'], $_SESSION['mois'], $_POST['nbJustificatif']);
	echo("<p class=\"info\">Modification enregistrée</p>");
}//fin if

//Si le formulaire de refus de remboursement du frais hors forfait est posté
if(isset($_POST['btActionFormSupprFraisHorsForfait']))
{//début if
	refuserLigneHF($idConnexion, $_POST['hfId2'], $_POST['hfLib2']);
	echo("<p class=\"info\">Modification enregistrée</p>");
}//fin if

if(isset($_POST['btActionFormReportFraisHorsForfait']))
{
	reportFraisHorsForfait($_POST['hfId3'], $_POST['hfDate3'], $_POST['hfLib3'], $_SESSION['visiteur']);
}

//Si la formulaire de validation de la fiche de frais est posté
if(isset($_POST['btActionValiderFicheFrais']))
{//début if
	modifierEtatFicheFrais($_SESSION['mois'], $_SESSION['visiteur'], "VA");
	echo("<<p class=\"info\">>Modification enregistrée</p>");
	unset($_SESSION['mois']);
}//fin if
?>

<div id="contenu">
<div name="droite">
<div name="haut"><h1>Validation des Frais</h1></div>	
<div name="bas">
<form name="formChoixVisiteur" method="POST" action="">
<label class="titre">Choisir le visiteur :</label>
<?
//Si l'utilisateur a choisi un visiteur médical
if(isset($_POST['action']) && $_POST['action']=="Suivant")
{//début if
	//Mise dans une variable session l'id du visiteur choisi
	$_SESSION['visiteur']=$_POST['lstVisiteur'];

	//Si un visiteur médical a été sélectionné
	//on supprime la variable sessions 'mois' déjà existant,
	//cela permet d'enlever les tableaux d'un autre visiteur
	unset($_SESSION['mois']);
}//fin if

if(isset($_POST['action']) && $_POST['action']=="Afficher")
{//début if
	$_SESSION['mois']=$_POST['lstMois'];
}//fin if

//Récupération des informations de tous les visiteurs
$idJeuDetailToutVisiteur=obtenirDetailToutVisiteur();
?>
<select name="lstVisiteur">
	<?
	//boucle qui parcours les occurences
	while($lgDetailToutVisiteur=mysql_fetch_array($idJeuDetailToutVisiteur))
	{//début while
	?>
		<option value="<?=$lgDetailToutVisiteur['id']?>"<?
		//Si un visiteur a déjà été choisi, alors on maintient l'option de la
		//liste déroulante sur le choix déjà fait
		if($lgDetailToutVisiteur['id']==$_SESSION['visiteur'])
		{//début if
			echo "selected";
		}//fin if
		?>
		>
		<?
		//Affichage nom et prénom dans la liste déroulante
		echo $lgDetailToutVisiteur['prenom']." ".$lgDetailToutVisiteur['nom']
		?>
		</option>
		<?
}//fin while
mysql_free_result($idJeuDetailToutVisiteur);
?>
</select>
<input type="submit" name="action" value="Suivant" />
</form>
<?
//Si le le visiteur médical a été sélectionné dans la liste déroulante
if(((isset($_POST['action'])) && ($_POST['action']=="Suivant")) || isset($_SESSION['mois']))
{//début if
	?>
	<label class="titre">Mois :</label>
	<?
	//Récupération des moi pour lesquelles le visiteur a des fiche de frais pour
	//les mois antérieur
	$idJeuMois = obtenirMoisFicheFraisAnt($_SESSION['visiteur']);
	$lgMois = mysql_fetch_assoc($idJeuMois);
	//Si le visiteur médical a une ou des fiches de frais
	if(!empty($lgMois))
	{//début if
	?>
		<form name="formChoixMois" method="POST" action="">
		<select name="lstMois">
		<?
		//Boucle qui parcours les occurences
		while ( is_array($lgMois) )
		{//début while
			$mois = $lgMois["mois"];
			$noMois = intval(substr($mois, 4, 2));
			$annee = intval(substr($mois, 0, 4));
			?>
				<option value="<?=$lgMois["mois"]; ?>"
				<?
				//Si le mois à déjà été sélectionné
				if(isset($_SESSION['mois']) && $lgMois["mois"]==$_SESSION['mois'])
				{//début if
					echo "selected";
				}//fin if
				?>
				>
				<?
				//Affichage du mois dans la liste déroulante
				//Appel de la fonction pour un affichage plus ludique
				echo obtenirLibelleMois($noMois) . " " . $annee;
				?>
				</option>
				<?
				$lgMois = mysql_fetch_assoc($idJeuMois);
		}//fin while
		?>
		</select>
		<?
	mysql_free_result($idJeuMois);
	?>
		<p class="titre" />
		<input type="submit" name="action" value="Afficher" />
		</form>
		<?
	}//fin if
	//Sinon, si le visiteur a aucune fiche de frais
	else
	{//début else
	?>
		<b>Aucune fiche de frais a validé pour ce visieur médical</b>
	<?
	}//fin else
}//fin if

//Si l'utilisateur à choisi un mois
if(isset($_POST['action']) && ($_POST['action']=="Afficher") || (isset($_SESSION['visiteur']) && isset($_SESSION['mois'])))
{//début if
?>
<!-------------------------------------------------------------------------------------------------------------------------------->
<!-- En Forfait -->
<?
	// demande de la requête pour obtenir la liste des éléments 
	// forfaitisés du visiteur séléctionné pour le mois demandé
	$req = obtenirReqEltsForfaitFicheFrais($_SESSION['mois'], $_SESSION['visiteur']);
	$idJeuEltsFraisForfait = mysql_query($req, $idConnexion);
	echo mysql_error($idConnexion);
	
	$tabEltsFraisForfait = array();
	while ($lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait))
	// parcours des frais forfaitisés du visiteur connecté
	// le stockage intermédiaire dans un tableau est nécessaire
	// car chacune des lignes du jeu d'enregistrements doit être doit être
	// affichée au sein d'une colonne du :tableau HTML
	{//début while
		$tabEltFraisForfait[] = $lgEltForfait;
	}//fin while
	mysql_free_result($idJeuEltsFraisForfait);

	?>
	<div><h2>Frais au forfait </h2></div>
	<table class="listeLegere">
	<form name="formFraisForfait" action="" method="POST">
	<tr>
	<?
	// premier parcours du tableau des frais forfaitisés du visiteur connecté
	// pour afficher la ligne des libellés des frais forfaitisés
        foreach($tabEltFraisForfait as $eltFraisForfait)
	{//début foreach
	?>
		<th><?=$eltFraisForfait['libelle'];?></th>
	<?php
	}//fin foreach
	?>
		<th>Action</th>
		</tr>
		<tr align="center">
		<?
	// second parcours du tableau des frais forfaitisés du visiteur connecté
	// pour afficher la ligne des quantités des frais forfaitisés
	foreach ( $tabEltFraisForfait as $eltFraisForfait)
	{//début foreach
			?>
			<td width="80"> <input type="text" size="3" name="eltFF[<?=$eltFraisForfait['idFraisForfait'];?>]" value="<?=$eltFraisForfait['quantite'];?>" /></td>
			<?
	}//fin foreach
	?>
			<td width="80"> <input type="submit" name="btActionFormFraisForfait" value="Modifier" /></td>
		</tr>
		</table>
		</form>
<!-------------------------------------------------------------------------------------------------------------------------------------------->
<!-- Hors Forfait -->
	    <?	
	// demande de la requête pour obtenir la liste des éléments hors
	// forfait du visiteur connecté pour le mois demandé
	$moisEcharpe =substr($_SESSION['mois'],0,4).'-'.substr($_SESSION['mois'], 4, 2);
	$req = obtenirReqEltsHorsForfaitFicheFrais($moisEcharpe, $_SESSION['visiteur']);
	$idJeuEltsHorsForfait = mysql_query($req, $idConnexion);
	$lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
  ?>
  	</div>

	<p class="titre" /><div><h2>Hors Forfait</h2></div>
	<table border="1">
		<tr>
			<th width="100" >Date</th>
			<th width="290" >Libellé </th>
			<th width="90" >Montant</th>
			<th width="80" >Action</th>
		</tr>
		<?
		//boucle qui parcours les éléments hors forfait
		while( is_array($lgEltHorsForfait))
		{//début while
		?>
			<tr align="center">
				<form name="formModifFraisHorsForfait" method="POST" action="">
					<input type="hidden" name="hfId1" value="<?=$lgEltHorsForfait["id"];?>" />
					<td width="100" ><input type="text" size="12" name="hfDate1" value="<?=convertirDateAnglaisVersFrancais($lgEltHorsForfait["date"]);?>"/></td>
					<td width="290"><input type="text" size="35" name="hfLib1" value="<?=filtrerChainePourNavig($lgEltHorsForfait["libelle"]);?>"/></td> 
					<td width="90"><input type="text" size="5" name="hfMont1" value="<?=$lgEltHorsForfait["montant"];?>"/></td>	
					<td width="80"><input type="submit" name="btActionFormFraisHorsForfait" value="Modifier" />
				</form>
				<form name="formSupprFraisHorsForfait" method="POST" action="" >
					<input type="hidden" name="hfId2" value="<?=$lgEltHorsForfait["id"];?>" />
					<input type="hidden" name="hfLib2" value="<?=$lgEltHorsForfait["libelle"];?>" />
					<input type="submit" name="btActionFormSupprFraisHorsForfait" value="Refuser" />
				</form>
				<form name="formReportFraisHorsForfait" method="POST" action="">
					<input type="hidden" name="hfId3" value="<?=$lgEltHorsForfait["id"];?>" />
					<input type="hidden" name="hfDate3" value="<?=$lgEltHorsForfait["date"];?>" />
					<input type="hidden" name="hfLib3" value="<?=$lgEltHorsForfait["libelle"];?>" />
					<input type="submit" name="btActionFormReportFraisHorsForfait" value="Reporter" /></td>
				</form>
			</tr>
			<?
			$lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
		}//fin while
		mysql_free_result($idJeuEltsHorsForfait);
		?>
	</table>		
<!-------------------------------------------------------------------------------------------------------------------------------->
<!-- Justficatifs -->
	<p class="titre"></p>
	<form name="modifNbJustificatif" method="POST" action="">
		<?
		$req=obtenirNbJustificatif($_SESSION['mois'], $_SESSION['visiteur']);
		$idNombreDeJustificatifs=mysql_query($req,$idConnexion);
		$lgNbJustificatifs=mysql_fetch_assoc($idNombreDeJustificatifs);
		?>
		<div class="titre">Nb Justificatifs</div><input type="text" class="zone" size="4" name="nbJustificatif" value="<?=$lgNbJustificatifs["nbJustificatifs"];?>" />
		<input type="submit" name="btActionModifNbJustificatif" value="Modifier" />
	</form>
	<form name="formValideFicheFrais" method="POST" action="" >
		<p class="titre" /><label class="titre">&nbsp;</label><input class="zone" type="reset" /><input class="zone" name="btActionValiderFicheFrais" type="submit" value="Valider" />
	</form>
	<?
	mysql_free_result($idNombreDeJustificatifs);
}//fin if
?>
</div>
</div>
</div>
<?
require($repInclude . "_pied.inc.html");
require($repInclude . "_fin.inc.php");
//fin php
?>
