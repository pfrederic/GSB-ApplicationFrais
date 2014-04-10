<?php
/*Connection Base de donnee*/
    $hote = "localhost";
    $login = "technicien";
    $mdp = "ini01";
    mysql_connect($hote, $login, $mdp);
/*Utilisation de le BD GSB*/
    $bd = "GSB";
    mysql_select_db($bd);
/*Requete SQL*/
$req="update FicheFrais set idEtat='CL' where substring(mois,5,2)<extract(month from current_date) and idEtat='CR'";
mysql_query($req);
/*Deconnection Base deonnee*/ 
    mysql_close();
?>

