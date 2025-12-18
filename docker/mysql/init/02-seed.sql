-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: eclipse
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `eclipse`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `eclipse` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `eclipse`;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'21R','Dinis Galvão','$2y$10$4Kzsviej.WgTHUh/JatVKOuyYDDFqKi5pBIy50grBPmNgXUbsAzlq','2025-12-17 16:22:11'),(2,'juju','Joana','$2y$10$4NqnPo6/LWTrbSt.g6abge5OPCESc3uBAIToXsR8/JRkwkUvoMI2a','2025-12-18 03:19:19');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `videos`
--

DROP TABLE IF EXISTS `videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `videos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `path` varchar(255) NOT NULL,
  `duration` varchar(10) NOT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_videos_user` (`user_id`),
  CONSTRAINT `fk_videos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `videos`
--

LOCK TABLES `videos` WRITE;
/*!40000 ALTER TABLE `videos` DISABLE KEYS */;
INSERT INTO `videos` VALUES (6,1,'Hero Dogo','user_files/21R/Hero_Dogo/video_69431fbc67ae1.mp4','00:10','user_files/21R/Hero_Dogo/thumb_69431fbc66ac8.png','The Dogo saved my babie\'s life','2025-12-17 21:25:18'),(7,1,'Polémica com André Ventura','user_files/21R/Pol_mica_com_Andr_Ventura/video_6943258135f89.mp4','00:11','user_files/21R/Pol_mica_com_Andr_Ventura/thumb_6943258134c90.png','Ele disse mesmo isto???','2025-12-17 21:49:54'),(8,1,'Montenegro disse coisas polémicas','user_files/21R/Montenegro_disse_coisas_pol_micas/video_69432810d4870.mp4','00:05','user_files/21R/Montenegro_disse_coisas_pol_micas/thumb_69432810d35c2.png','Que mundo louco em que vivemos, definitivamente não é AI','2025-12-17 22:00:50'),(9,1,'Flick - My LBAW webapp','user_files/21R/Flick_-_My_LBAW_webapp/video_694371c820807.mp4','01:58','user_files/21R/Flick_-_My_LBAW_webapp/thumb_694371c81ebcc.png','This was a worked carried out on the 3rd uni year, a social media app','2025-12-18 03:15:58'),(10,2,'Sound Maestro 2','user_files/juju/Sound_Maestro/video_6943777da41b5.mp4','01:23','user_files/juju/Sound_Maestro/thumb_6943777d9a87f.png','I edited this description','2025-12-18 03:40:17'),(13,2,'Setting up plastic bags','user_files/juju/Setting_up_plastic_bags/video_694379930be2e.mp4','00:13','user_files/juju/Setting_up_plastic_bags/thumb_69437992dc4e3.png','Cool project','2025-12-18 03:48:47'),(14,2,'End result of the plastic bag video','user_files/juju/End_result_of_the_plastic_bag_video/video_69437af19b840.mp4','00:05','user_files/juju/End_result_of_the_plastic_bag_video/thumb_69437af190937.png','What do u think?','2025-12-18 03:54:29');
/*!40000 ALTER TABLE `videos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'eclipse'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-18 15:38:51
