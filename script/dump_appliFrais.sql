-- MySQL dump 10.13  Distrib 5.1.73, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: GSB
-- ------------------------------------------------------
-- Server version	5.1.73-1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Etat`
--

DROP TABLE IF EXISTS `Etat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Etat` (
  `id` char(2) NOT NULL,
  `libelle` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Etat`
--

LOCK TABLES `Etat` WRITE;
/*!40000 ALTER TABLE `Etat` DISABLE KEYS */;
INSERT INTO `Etat` VALUES ('CL','Saisie clôturée'),('CR','Fiche créée, saisie en cours'),('RB','Remboursée'),('VA','Validée et mise en paiement');
/*!40000 ALTER TABLE `Etat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FicheFrais`
--

DROP TABLE IF EXISTS `FicheFrais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FicheFrais` (
  `idVisiteur` char(4) NOT NULL,
  `mois` char(6) NOT NULL,
  `nbJustificatifs` int(11) DEFAULT NULL,
  `montantValide` decimal(10,2) DEFAULT NULL,
  `dateModif` date DEFAULT NULL,
  `idEtat` char(2) DEFAULT 'CR',
  PRIMARY KEY (`idVisiteur`,`mois`),
  KEY `idEtat` (`idEtat`),
  CONSTRAINT `FicheFrais_ibfk_1` FOREIGN KEY (`idEtat`) REFERENCES `Etat` (`id`),
  CONSTRAINT `FicheFrais_ibfk_2` FOREIGN KEY (`idVisiteur`) REFERENCES `Visiteur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FicheFrais`
--

LOCK TABLES `FicheFrais` WRITE;
/*!40000 ALTER TABLE `FicheFrais` DISABLE KEYS */;
INSERT INTO `FicheFrais` VALUES ('a17','201306',7,NULL,'2013-07-30','RB'),('a17','201307',3,NULL,'2013-07-09','CL'),('a17','201312',0,NULL,'2013-12-27','CR'),('e22','201305',4,'40.50','2013-07-23','VA'),('e22','201311',2,NULL,'2013-11-20','VA'),('e22','201312',0,NULL,'2013-12-27','CR');
/*!40000 ALTER TABLE `FicheFrais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FraisForfait`
--

DROP TABLE IF EXISTS `FraisForfait`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FraisForfait` (
  `id` char(3) NOT NULL,
  `libelle` char(20) DEFAULT NULL,
  `montant` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FraisForfait`
--

LOCK TABLES `FraisForfait` WRITE;
/*!40000 ALTER TABLE `FraisForfait` DISABLE KEYS */;
INSERT INTO `FraisForfait` VALUES ('ETP','Forfait Etape','110.00'),('KM','Frais Kilométrique','0.62'),('NUI','Nuitée Hôtel','80.00'),('REP','Repas Restaurant','25.00');
/*!40000 ALTER TABLE `FraisForfait` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LigneFraisForfait`
--

DROP TABLE IF EXISTS `LigneFraisForfait`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LigneFraisForfait` (
  `idVisiteur` char(4) NOT NULL,
  `mois` char(6) NOT NULL,
  `idFraisForfait` char(3) NOT NULL,
  `quantite` int(11) DEFAULT NULL,
  PRIMARY KEY (`idVisiteur`,`mois`,`idFraisForfait`),
  KEY `idFraisForfait` (`idFraisForfait`),
  CONSTRAINT `LigneFraisForfait_ibfk_1` FOREIGN KEY (`idVisiteur`, `mois`) REFERENCES `FicheFrais` (`idVisiteur`, `mois`),
  CONSTRAINT `LigneFraisForfait_ibfk_2` FOREIGN KEY (`idFraisForfait`) REFERENCES `FraisForfait` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LigneFraisForfait`
--

LOCK TABLES `LigneFraisForfait` WRITE;
/*!40000 ALTER TABLE `LigneFraisForfait` DISABLE KEYS */;
INSERT INTO `LigneFraisForfait` VALUES ('a17','201306','ETP',2),('a17','201306','KM',81),('a17','201306','NUI',3),('a17','201306','REP',9),('a17','201307','ETP',2),('a17','201307','KM',181),('a17','201307','NUI',4),('a17','201307','REP',6),('a17','201312','ETP',1),('a17','201312','KM',252),('a17','201312','NUI',2),('a17','201312','REP',12),('e22','201311','ETP',2),('e22','201311','KM',450),('e22','201311','NUI',3),('e22','201311','REP',7),('e22','201312','ETP',2),('e22','201312','KM',450),('e22','201312','NUI',4),('e22','201312','REP',7);
/*!40000 ALTER TABLE `LigneFraisForfait` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LigneFraisHorsForfait`
--

DROP TABLE IF EXISTS `LigneFraisHorsForfait`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LigneFraisHorsForfait` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idVisiteur` char(4) NOT NULL,
  `libelle` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idVisiteur` (`idVisiteur`),
  CONSTRAINT `LigneFraisHorsForfait_ibfk_1` FOREIGN KEY (`idVisiteur`) REFERENCES `FicheFrais` (`idVisiteur`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LigneFraisHorsForfait`
--

LOCK TABLES `LigneFraisHorsForfait` WRITE;
/*!40000 ALTER TABLE `LigneFraisHorsForfait` DISABLE KEYS */;
INSERT INTO `LigneFraisHorsForfait` VALUES (12,'a17','bouquet de fleurs','2013-06-04','40.00'),(13,'a17','boite de chocolats','2013-07-17','17.00'),(14,'a17','Boite de chocolats de noel','2013-12-11','25.30'),(15,'e22','Bouquet de fleurs','2013-11-15','14.30');
/*!40000 ALTER TABLE `LigneFraisHorsForfait` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Visiteur`
--

DROP TABLE IF EXISTS `Visiteur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Visiteur` (
  `id` char(4) NOT NULL,
  `nom` char(30) DEFAULT NULL,
  `prenom` char(30) DEFAULT NULL,
  `login` char(20) DEFAULT NULL,
  `mdp` char(20) DEFAULT NULL,
  `adresse` char(30) DEFAULT NULL,
  `cp` char(5) DEFAULT NULL,
  `ville` char(30) DEFAULT NULL,
  `dateEmbauche` date DEFAULT NULL,
  `type` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Visiteur`
--

LOCK TABLES `Visiteur` WRITE;
/*!40000 ALTER TABLE `Visiteur` DISABLE KEYS */;
INSERT INTO `Visiteur` VALUES ('a131','Villechalane','Louis','lvillachane','jux7g','8 rue des Charmes','46000','Cahors','2005-12-21',0),('a17','Andre','David','dandre','oppg5','1 rue Petit','46200','Lalbenque','1998-11-23',0),('a55','Bedos','Christian','cbedos','gmhxd','1 rue Peranud','46250','Montcuq','1995-01-12',0),('a93','Tusseau','Louis','ltusseau','ktp3s','22 rue des Ternes','46123','Gramat','2000-05-01',0),('b13','Bentot','Pascal','pbentot','doyw1','11 allée des Cerises','46512','Bessines','1992-07-09',0),('b16','Bioret','Luc','lbioret','hrjfs','1 Avenue gambetta','46000','Cahors','1998-05-11',0),('b19','Bunisset','Francis','fbunisset','4vbnd','10 rue des Perles','93100','Montreuil','1987-10-21',0),('b25','Bunisset','Denise','dbunisset','s1y1r','23 rue Manin','75019','paris','2010-12-05',0),('b28','Cacheux','Bernard','bcacheux','uf7r3','114 rue Blanche','75017','Paris','2009-11-12',0),('b34','Cadic','Eric','ecadic','6u8dc','123 avenue de la République','75011','Paris','2008-09-23',0),('b4','Charoze','Catherine','ccharoze','u817o','100 rue Petit','75019','Paris','2005-11-12',0),('b50','Clepkens','Christophe','cclepkens','bw1us','12 allée des Anges','93230','Romainville','2003-08-11',0),('b59','Cottin','Vincenne','vcottin','2hoh9','36 rue Des Roches','93100','Monteuil','2001-11-18',0),('c14','Daburon','François','fdaburon','7oqpv','13 rue de Chanzy','94000','Créteil','2022-00-02',0),('c3','De','Philippe','pde','gk9kx','13 rue Barthes','94000','Créteil','2010-12-14',0),('c54','Debelle','Michel','mdebelle','od5rt','181 avenue Barbusse','93210','Rosny','2006-11-23',0),('d13','Debelle','Jeanne','jdebelle','nvwqq','134 allée des Joncs','44000','Nantes','2000-05-11',0),('d51','Debroise','Michel','mdebroise','sghkb','2 Bld Jourdain','44000','Nantes','2001-04-17',0),('e22','Desmarquest','Nathalie','ndesmarquest','f1fob','14 Place d Arc','45000','Orléans','2005-11-12',0),('e24','Desnost','Pierre','pdesnost','4k2o5','16 avenue des Cèdres','23200','Guéret','2001-02-05',0),('e39','Dudouit','Frédéric','fdudouit','44im8','18 rue de l église','23120','GrandBourg','2000-08-01',0),('e49','Duncombe','Claude','cduncombe','qf77j','19 rue de la tour','23100','La souteraine','1987-10-10',0),('e5','Enault-Pascreau','Céline','cenault','y2qdu','25 place de la gare','23200','Gueret','1995-09-01',0),('e52','Eynde','Valérie','veynde','i7sn3','3 Grand Place','13015','Marseille','1999-11-01',0),('f21','Finck','Jacques','jfinck','mpb3t','10 avenue du Prado','13002','Marseille','2001-11-10',1),('f39','Frémont','Fernande','ffremont','xs5tq','4 route de la mer','13012','Allauh','1998-10-01',1),('f4','Gest','Alain','agest','dywvt','30 avenue de la mer','13025','Berre','1985-11-01',1);
/*!40000 ALTER TABLE `Visiteur` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-02-17 14:40:09
