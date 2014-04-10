<?php
$repInclude = './include/';
require($repInclude . "_init.inc.php");

// page inaccessible si visiteur non connecté
if (!estVisiteurConnecte()) {
        header("Location: cSeConnecter.php");
}
require($repInclude . "_enteteCompta.inc.html");
require($repInclude . "_sommaire.inc.php");

//Si l'utilisateur a choisi une fiche de frais dans la liste déroulante,
//on stocke les informations nécessaire à l'identification de la fiche de frais
//dans des variables sessions
if(isset($_POST['btActionChoixFicheFrais']))
{//début if
	$_SESSION['visiteur']=substr($_POST['lstFicheFrais'],0,3);
	$_SESSION['mois']=substr($_POST['lstFicheFrais'],4,6);
}//fin if

if(isset($_POST['btActionFormRemboursementFicheFrais']))
{
	modifierEtatFicheFrais($_SESSION['mois'], $_SESSION['visiteur'], "RB");
	echo("<h2>Fiche de frais remboursée</h2>");
}

?>
<div id="contenu">
<div name="droite">
<div name="haut"><h1>Suivi de paiement des fiches de frais</h1></div>	
<div name="bas">
<?
$idFicheFraisValide=obtenirMoisFicheFraisValide();
//Si il y a des fiches de frais validée dans la base de données
if(!empty($idFicheFraisValide))
{//début if
	?>
	<form name="choixFicheFrais" method="POST" action="">
	<select name="lstFicheFrais">
	<?
	while($lgFicheFrais=mysql_fetch_array($idFicheFraisValide))
	{//début while
		$mois=$lgFicheFrais['mois'];
		$noMois= intval(substr($mois, 4, 2));
		$annee = intval(substr($mois, 0, 4));
	?>
		<option value="<?=$lgFicheFrais['idVisiteur']."|".$lgFicheFrais['mois'];?>"
		<?
		//Si une fiche de frais a déjà été sélectionnée, on maintient le choix de l'utilisateur
		//sur ce qu'il a choisi
		if(($_SESSION['visiteur']==$lgFicheFrais['idVisiteur']) && ($_SESSION['mois']==$lgFicheFrais['mois']))
		{//début if
			echo "selected";
		}//fin if
		?>
		>
		<?
		echo $lgFicheFrais['nom']." ".$lgFicheFrais['prenom']." ".obtenirLibelleMois($noMois)." ".$annee;
		?>
		</option>

	<?
	}//fin while
	?>
	</select>
	<input type="submit" name="btActionChoixFicheFrais" value="Consulter" />
	</form>
	<?
}//fin if
//Si il y a aucune fiche de frais, affichage d'un message
else
{//début else
	echo("<h2>Aucune fiche de frais disponible</h2>");
}//fin else
mysql_free_result($idFicheFraisValide);
//Si l'utilisateur a choisi une fiche de frais,
//on affiche les détails de la fiche
if(isset($_POST['btActionChoixFicheFrais']))
{//début if
	$req=obtenirReqEltsForfaitFicheFrais($_SESSION['mois'], $_SESSION['visiteur']);
	$idEltsForfaitFicheFrais=mysql_query($req);
	?>
<!------------------------------------------------------------------------------------------------------------------------------------------------>
<!-- En forfait -->
	<h2>Elements forfaitaires</h2>
	<table>
	<?
	while($lgEltsForfaitFicheFrais=mysql_fetch_array($idEltsForfaitFicheFrais))
	{//début while
	?>
		<tr>
				<th width="300" height="20"><?echo $lgEltsForfaitFicheFrais["libelle"];?></th>
				<td width="300" height="20"><?echo $lgEltsForfaitFicheFrais["quantite"];?></td>
		</tr>
	<?
	}//fin while
	?>
	</table>
	<?
	mysql_free_result($idEltsForfaitFicheFrais);
	?>
	<p></p>
<!-------------------------------------------------------------------------------------------------------------------------------------------------------->
<!-- Hors forfait -->
	<h2>Element hors forfait</h2>
	<?
	$mois=substr($_SESSION['mois'],0,4).'-'.substr($_SESSION['mois'],4,2);
	$req=obtenirReqEltsHorsForfaitFicheFrais($mois, $_SESSION['visiteur']);
	$idEltsHorsForfaitFicheFrais=mysql_query($req);
	
	while($lgEltsHorsForfaitFicheFrais=mysql_fetch_array($idEltsHorsForfaitFicheFrais))
	{//début while
		$lgEltsHorsForfaitFicheFrais["date"]=convertirDateAnglaisVersFrancais($lgEltsHorsForfaitFicheFrais["date"]);
	?>
		<table id="tableSuiviHorsForfait">
			<?
			for($i=1;$i<mysql_num_fields($idEltsHorsForfaitFicheFrais);$i++)
			{//début for
			?>
				<tr>
					<th width="300" height="20"><?echo mysql_field_name($idEltsHorsForfaitFicheFrais,$i);?></th>
					<td width="300" height="20"><?echo $lgEltsHorsForfaitFicheFrais[mysql_field_name($idEltsHorsForfaitFicheFrais,$i)];?></th>
				</tr>
			<?
			}//fin for
			?>
		</table>
		<p></p>
	<?
	}//fin while
	mysql_free_result($idEltsHorsForfaitFicheFrais);
	?>
	<form name="formRemboursementFicheFrais" method="POST" action="">
	<input type="submit" name="btActionFormRemboursementFicheFrais" value="Remboursée" />
	</form>
<?
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
