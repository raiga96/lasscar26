-- MySQL dump 10.13  Distrib 5.7.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: sistem_sukan_jts
-- ------------------------------------------------------
-- Server version	5.7.44

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
-- Table structure for table `tbl_aturcara`
--

DROP TABLE IF EXISTS `tbl_aturcara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_aturcara` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jenis` enum('umum','pembukaan','penutup') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'umum',
  `tarikh` date NOT NULL,
  `masa` time NOT NULL,
  `aktiviti` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pegawai_bertanggungjawab` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `susunan` int(10) unsigned NOT NULL DEFAULT '0',
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_aturcara`
--

LOCK TABLES `tbl_aturcara` WRITE;
/*!40000 ALTER TABLE `tbl_aturcara` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_aturcara` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_audit_log`
--

DROP TABLE IF EXISTS `tbl_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_audit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pengguna_id` int(10) unsigned DEFAULT NULL,
  `tindakan` enum('create','update','delete','login','logout') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jadual_disentuh` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rekod_id` int(10) unsigned DEFAULT NULL,
  `butiran` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pengguna_id` (`pengguna_id`),
  CONSTRAINT `tbl_audit_log_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `tbl_pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_audit_log`
--

LOCK TABLES `tbl_audit_log` WRITE;
/*!40000 ALTER TABLE `tbl_audit_log` DISABLE KEYS */;
INSERT INTO `tbl_audit_log` VALUES (1,1,'login','tbl_pengguna',1,'Pengguna log masuk berjaya','::1','2026-07-20 14:08:36'),(2,1,'delete','tbl_bahagian',16,'Padam kontinjen: Jabatan Hutan Sarawak','::1','2026-07-20 14:09:34'),(3,1,'delete','tbl_bahagian',15,'Padam kontinjen: Lembaga Air Kuching','::1','2026-07-20 14:09:37'),(4,1,'delete','tbl_bahagian',13,'Padam kontinjen: Jabatan Kerja Raya Sarawak','::1','2026-07-20 14:09:40'),(5,1,'delete','tbl_bahagian',14,'Padam kontinjen: Pihak Berkuasa Tempatan','::1','2026-07-20 14:09:43'),(6,1,'update','tbl_bahagian',1,'Kemas kini kontinjen: Ibu Pejabat JTS (LANDAS HQ)','::1','2026-07-20 14:12:25'),(7,1,'update','tbl_bahagian',1,'Kemas kini kontinjen: Ibu Pejabat JTS (LANDAS HQ)','::1','2026-07-20 14:12:31'),(8,1,'update','tbl_bahagian',5,'Kemas kini kontinjen: JTS Betong (LANDAS BETONG)','::1','2026-07-20 14:13:05'),(9,1,'update','tbl_bahagian',10,'Kemas kini kontinjen: JTS Bintulu (LANDAS BINTULU)','::1','2026-07-20 14:13:28'),(10,1,'update','tbl_bahagian',9,'Kemas kini kontinjen: JTS Kapit (KPT)','::1','2026-07-20 14:14:48'),(11,1,'update','tbl_bahagian',2,'Kemas kini kontinjen: JTS Kuching (KCH)','::1','2026-07-20 14:15:05'),(12,1,'update','tbl_bahagian',2,'Kemas kini kontinjen: JTS Kuching (KCH)','::1','2026-07-20 14:16:35'),(13,1,'update','tbl_bahagian',12,'Kemas kini kontinjen: JTS Limbang (LBG)','::1','2026-07-20 14:16:45'),(14,1,'update','tbl_bahagian',12,'Kemas kini kontinjen: JTS Limbang (LBG)','::1','2026-07-20 14:20:08'),(15,1,'update','tbl_bahagian',11,'Kemas kini kontinjen: JTS Miri (MRI)','::1','2026-07-20 14:20:21'),(16,1,'update','tbl_bahagian',11,'Kemas kini kontinjen: JTS Miri (MRI)','::1','2026-07-20 14:25:53'),(17,1,'update','tbl_bahagian',8,'Kemas kini kontinjen: JTS Mukah (MKH)','::1','2026-07-20 14:26:05'),(18,1,'update','tbl_bahagian',3,'Kemas kini kontinjen: JTS Samarahan (SMR)','::1','2026-07-20 14:26:13'),(19,1,'update','tbl_bahagian',6,'Kemas kini kontinjen: JTS Sarikei (SRK)','::1','2026-07-20 14:26:25'),(20,1,'update','tbl_bahagian',7,'Kemas kini kontinjen: JTS Sibu (SBU)','::1','2026-07-20 14:26:30'),(21,1,'update','tbl_bahagian',7,'Kemas kini kontinjen: JTS Sibu (SBU)','::1','2026-07-20 14:26:51'),(22,1,'update','tbl_bahagian',4,'Kemas kini kontinjen: JTS Sri Aman (SRA)','::1','2026-07-20 14:27:03'),(23,1,'update','tbl_bahagian',9,'Kemas kini kontinjen: JTS Kapit (LANDAS KAPIT)','::1','2026-07-20 14:27:33'),(24,1,'update','tbl_bahagian',2,'Kemas kini kontinjen: JTS Kuching (LANDAS KUCHING)','::1','2026-07-20 14:27:43'),(25,1,'update','tbl_bahagian',12,'Kemas kini kontinjen: JTS Limbang (LANDAS LIMBANG)','::1','2026-07-20 14:27:51'),(26,1,'update','tbl_bahagian',12,'Kemas kini kontinjen: JTS Limbang (5D)','::1','2026-07-20 14:28:10'),(27,1,'update','tbl_bahagian',2,'Kemas kini kontinjen: JTS Kuching (1D)','::1','2026-07-20 14:28:17'),(28,1,'update','tbl_bahagian',9,'Kemas kini kontinjen: JTS Kapit (7D)','::1','2026-07-20 14:28:23'),(29,1,'update','tbl_bahagian',10,'Kemas kini kontinjen: JTS Bintulu (9D)','::1','2026-07-20 14:28:28'),(30,1,'update','tbl_bahagian',5,'Kemas kini kontinjen: JTS Betong (11D)','::1','2026-07-20 14:28:34'),(31,1,'update','tbl_bahagian',1,'Kemas kini kontinjen: Ibu Pejabat JTS (HQ)','::1','2026-07-20 14:28:40'),(32,1,'update','tbl_bahagian',11,'Kemas kini kontinjen: JTS Miri (4D)','::1','2026-07-20 14:28:47'),(33,1,'update','tbl_bahagian',8,'Kemas kini kontinjen: JTS Mukah (10D)','::1','2026-07-20 14:28:52'),(34,1,'update','tbl_bahagian',3,'Kemas kini kontinjen: JTS Samarahan (8D)','::1','2026-07-20 14:28:59'),(35,1,'update','tbl_bahagian',6,'Kemas kini kontinjen: JTS Sarikei (6D)','::1','2026-07-20 14:29:07'),(36,1,'update','tbl_bahagian',7,'Kemas kini kontinjen: JTS Sibu (3D)','::1','2026-07-20 14:29:17'),(37,1,'update','tbl_bahagian',4,'Kemas kini kontinjen: JTS Sri Aman (2D)','::1','2026-07-20 14:29:47'),(38,1,'create','tbl_bahagian',17,'Tambah kontinjen baru: JTS Serian (12D)','::1','2026-07-20 14:30:11'),(39,1,'create','tbl_bahagian',18,'Tambah kontinjen baru: MUDERN (MUDERN)','::1','2026-07-20 14:30:48'),(40,1,'create','tbl_bahagian',19,'Tambah kontinjen baru: JUPEM (JUPEM)','::1','2026-07-20 14:33:55'),(41,1,'create','tbl_bahagian',20,'Tambah kontinjen baru: LAND SURVEYOR BOARD (LSB)','::1','2026-07-20 14:34:31'),(42,1,'logout','tbl_pengguna',1,'Pengguna log keluar sistem','::1','2026-07-20 14:51:50'),(43,1,'login','tbl_pengguna',1,'Pengguna log masuk berjaya','::1','2026-07-20 14:52:34'),(44,1,'create','tbl_hero_banner',1,'Tambah banner utama baru: LANDAS SPORT CARNIVAL 2026','::1','2026-07-20 15:02:12'),(45,1,'update','tbl_hero_banner',1,'Kemas kini banner utama: LANDAS SPORT CARNIVAL 2026','::1','2026-07-20 15:12:58');
/*!40000 ALTER TABLE `tbl_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_bahagian`
--

DROP TABLE IF EXISTS `tbl_bahagian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_bahagian` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_bahagian` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `singkatan` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis` enum('dalaman','jemputan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dalaman',
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','tidak_aktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_bahagian`
--

LOCK TABLES `tbl_bahagian` WRITE;
/*!40000 ALTER TABLE `tbl_bahagian` DISABLE KEYS */;
INSERT INTO `tbl_bahagian` VALUES (1,'Ibu Pejabat JTS','HQ','dalaman','55c8ecc67d9de66e849841b155271d33.png','aktif','2026-07-20 13:50:41'),(2,'JTS Kuching','1D','dalaman','ff154be8a841ef1dc971b3d39ba179bd.png','aktif','2026-07-20 13:50:41'),(3,'JTS Samarahan','8D','dalaman','17baf9a9a1726293d2d62c40e61d4546.png','aktif','2026-07-20 13:50:41'),(4,'JTS Sri Aman','2D','dalaman','3c362f8cec1f8f6909f956a2f791d334.png','aktif','2026-07-20 13:50:41'),(5,'JTS Betong','11D','dalaman','475e4339668a2965d0e4079cef0c8547.png','aktif','2026-07-20 13:50:41'),(6,'JTS Sarikei','6D','dalaman','23f0a3adc6da67646fb28e4f3b463a8b.png','aktif','2026-07-20 13:50:41'),(7,'JTS Sibu','3D','dalaman','14507f5f66b3c78d7eca1838e6e29641.png','aktif','2026-07-20 13:50:41'),(8,'JTS Mukah','10D','dalaman','d2ae0340045765a1e53f7a719c57d15e.png','aktif','2026-07-20 13:50:41'),(9,'JTS Kapit','7D','dalaman','c367072564796bb8902c2f362328feda.png','aktif','2026-07-20 13:50:41'),(10,'JTS Bintulu','9D','dalaman','e841fec6a1f6b306742ae6a4b99b8665.png','aktif','2026-07-20 13:50:41'),(11,'JTS Miri','4D','dalaman','6ca72219c32e7b74d534a6bd014467ca.png','aktif','2026-07-20 13:50:41'),(12,'JTS Limbang','5D','dalaman','708bf865362b071c5aa8fe3cb39e4f2c.png','aktif','2026-07-20 13:50:41'),(17,'JTS Serian','12D','dalaman','1e44966a198513067de92cb6292b96d6.png','aktif','2026-07-20 14:30:11'),(18,'MUDERN','MUDERN','jemputan','b828e374f606b1a1424e694aa8d1ea8b.png','aktif','2026-07-20 14:30:48'),(19,'JUPEM','JUPEM','jemputan','b22386a3167fa9e9dfd7fca16a273416.png','aktif','2026-07-20 14:33:55'),(20,'LAND SURVEYOR BOARD','LSB','jemputan','123a3229a2bccc840ae5f1565b2f79ce.png','aktif','2026-07-20 14:34:31');
/*!40000 ALTER TABLE `tbl_bahagian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_galeri`
--

DROP TABLE IF EXISTS `tbl_galeri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_galeri` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tajuk` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_fail` enum('imej','video') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'imej',
  `url_fail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `album` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sukan_id` int(10) unsigned DEFAULT NULL,
  `upload_oleh` int(10) unsigned DEFAULT NULL,
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sukan_id` (`sukan_id`),
  KEY `upload_oleh` (`upload_oleh`),
  KEY `idx_album` (`album`),
  CONSTRAINT `tbl_galeri_ibfk_1` FOREIGN KEY (`sukan_id`) REFERENCES `tbl_sukan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tbl_galeri_ibfk_2` FOREIGN KEY (`upload_oleh`) REFERENCES `tbl_pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_galeri`
--

LOCK TABLES `tbl_galeri` WRITE;
/*!40000 ALTER TABLE `tbl_galeri` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_galeri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_hero_banner`
--

DROP TABLE IF EXISTS `tbl_hero_banner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_hero_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tajuk` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_imej` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bahagian_juara_id` int(10) unsigned DEFAULT NULL,
  `status_aktif` enum('aktif','tidak_aktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tidak_aktif',
  `susunan` int(10) unsigned NOT NULL DEFAULT '0',
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `bahagian_juara_id` (`bahagian_juara_id`),
  CONSTRAINT `tbl_hero_banner_ibfk_1` FOREIGN KEY (`bahagian_juara_id`) REFERENCES `tbl_bahagian` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_hero_banner`
--

LOCK TABLES `tbl_hero_banner` WRITE;
/*!40000 ALTER TABLE `tbl_hero_banner` DISABLE KEYS */;
INSERT INTO `tbl_hero_banner` VALUES (1,'LANDAS SPORT CARNIVAL 2026','60b2b5866632e2612ae8d5297f01c684.png',NULL,'aktif',0,'2026-07-20 15:02:12');
/*!40000 ALTER TABLE `tbl_hero_banner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_jadual_perlawanan`
--

DROP TABLE IF EXISTS `tbl_jadual_perlawanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_jadual_perlawanan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sukan_id` int(10) unsigned NOT NULL,
  `pasukan_a_id` int(10) unsigned NOT NULL,
  `pasukan_b_id` int(10) unsigned DEFAULT NULL,
  `venue_id` int(10) unsigned NOT NULL,
  `tarikh` date NOT NULL,
  `masa` time NOT NULL,
  `pusingan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('akan_datang','live','selesai','ditangguh') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'akan_datang',
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `dikemaskini_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sukan_id` (`sukan_id`),
  KEY `pasukan_a_id` (`pasukan_a_id`),
  KEY `pasukan_b_id` (`pasukan_b_id`),
  KEY `venue_id` (`venue_id`),
  KEY `idx_status` (`status`),
  KEY `idx_tarikh` (`tarikh`),
  CONSTRAINT `tbl_jadual_perlawanan_ibfk_1` FOREIGN KEY (`sukan_id`) REFERENCES `tbl_sukan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_jadual_perlawanan_ibfk_2` FOREIGN KEY (`pasukan_a_id`) REFERENCES `tbl_pasukan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_jadual_perlawanan_ibfk_3` FOREIGN KEY (`pasukan_b_id`) REFERENCES `tbl_pasukan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tbl_jadual_perlawanan_ibfk_4` FOREIGN KEY (`venue_id`) REFERENCES `tbl_venue` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_jadual_perlawanan`
--

LOCK TABLES `tbl_jadual_perlawanan` WRITE;
/*!40000 ALTER TABLE `tbl_jadual_perlawanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_jadual_perlawanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_kedudukan_pingat`
--

DROP TABLE IF EXISTS `tbl_kedudukan_pingat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_kedudukan_pingat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bahagian_id` int(10) unsigned NOT NULL,
  `emas` int(10) unsigned NOT NULL DEFAULT '0',
  `perak` int(10) unsigned NOT NULL DEFAULT '0',
  `gangsa` int(10) unsigned NOT NULL DEFAULT '0',
  `jumlah` int(10) unsigned GENERATED ALWAYS AS (((`emas` + `perak`) + `gangsa`)) STORED,
  `dikemaskini_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bahagian_id` (`bahagian_id`),
  CONSTRAINT `tbl_kedudukan_pingat_ibfk_1` FOREIGN KEY (`bahagian_id`) REFERENCES `tbl_bahagian` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_kedudukan_pingat`
--

LOCK TABLES `tbl_kedudukan_pingat` WRITE;
/*!40000 ALTER TABLE `tbl_kedudukan_pingat` DISABLE KEYS */;
INSERT INTO `tbl_kedudukan_pingat` (`id`, `bahagian_id`, `emas`, `perak`, `gangsa`, `dikemaskini_pada`) VALUES (1,1,0,0,0,'2026-07-20 13:57:24'),(2,2,0,0,0,'2026-07-20 13:57:24'),(3,3,0,0,0,'2026-07-20 13:57:24'),(4,4,0,0,0,'2026-07-20 13:57:24'),(5,5,0,0,0,'2026-07-20 13:57:24'),(6,6,0,0,0,'2026-07-20 13:57:24'),(7,7,0,0,0,'2026-07-20 13:57:24'),(8,8,0,0,0,'2026-07-20 13:57:24'),(9,9,0,0,0,'2026-07-20 13:57:24'),(10,10,0,0,0,'2026-07-20 13:57:24'),(11,11,0,0,0,'2026-07-20 13:57:24'),(12,12,0,0,0,'2026-07-20 13:57:24');
/*!40000 ALTER TABLE `tbl_kedudukan_pingat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_keputusan`
--

DROP TABLE IF EXISTS `tbl_keputusan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_keputusan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jadual_id` int(10) unsigned NOT NULL,
  `skor_a` int(10) unsigned DEFAULT NULL,
  `skor_b` int(10) unsigned DEFAULT NULL,
  `pasukan_menang_id` int(10) unsigned DEFAULT NULL,
  `jenis_pingat` enum('emas','perak','gangsa','tiada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tiada',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `direkod_oleh` int(10) unsigned DEFAULT NULL,
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `dikemaskini_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jadual_id` (`jadual_id`),
  KEY `pasukan_menang_id` (`pasukan_menang_id`),
  KEY `direkod_oleh` (`direkod_oleh`),
  CONSTRAINT `tbl_keputusan_ibfk_1` FOREIGN KEY (`jadual_id`) REFERENCES `tbl_jadual_perlawanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_keputusan_ibfk_2` FOREIGN KEY (`pasukan_menang_id`) REFERENCES `tbl_pasukan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tbl_keputusan_ibfk_3` FOREIGN KEY (`direkod_oleh`) REFERENCES `tbl_pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_keputusan`
--

LOCK TABLES `tbl_keputusan` WRITE;
/*!40000 ALTER TABLE `tbl_keputusan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_keputusan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_pasukan`
--

DROP TABLE IF EXISTS `tbl_pasukan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_pasukan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bahagian_id` int(10) unsigned NOT NULL,
  `sukan_id` int(10) unsigned NOT NULL,
  `nama_pasukan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_bahagian_sukan` (`bahagian_id`,`sukan_id`),
  KEY `sukan_id` (`sukan_id`),
  CONSTRAINT `tbl_pasukan_ibfk_1` FOREIGN KEY (`bahagian_id`) REFERENCES `tbl_bahagian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_pasukan_ibfk_2` FOREIGN KEY (`sukan_id`) REFERENCES `tbl_sukan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_pasukan`
--

LOCK TABLES `tbl_pasukan` WRITE;
/*!40000 ALTER TABLE `tbl_pasukan` DISABLE KEYS */;
INSERT INTO `tbl_pasukan` VALUES (1,2,2,'Kuching Badminton','2026-07-20 13:57:24'),(2,3,2,'Samarahan Badminton','2026-07-20 13:57:24');
/*!40000 ALTER TABLE `tbl_pasukan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_pengguna`
--

DROP TABLE IF EXISTS `tbl_pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_pengguna` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_penuh` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emel` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kata_laluan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `peranan` enum('super_admin','editor','media') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'editor',
  `status` enum('aktif','tidak_aktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `percubaan_gagal` tinyint(3) unsigned DEFAULT '0',
  `dikunci_sehingga` datetime DEFAULT NULL,
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `dikemaskini_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emel` (`emel`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_pengguna`
--

LOCK TABLES `tbl_pengguna` WRITE;
/*!40000 ALTER TABLE `tbl_pengguna` DISABLE KEYS */;
INSERT INTO `tbl_pengguna` VALUES (1,'Administrator JTS','admin@jts.sarawak.gov.my','$2y$10$l/GrsTa.hdF3QMZrXXehMeOveuwBE2RUCh7ndH0dJtmxM9VvfcKla','super_admin','aktif',0,NULL,'2026-07-20 13:50:41','2026-07-20 13:50:41');
/*!40000 ALTER TABLE `tbl_pengguna` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_sukan`
--

DROP TABLE IF EXISTS `tbl_sukan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_sukan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_sukan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('lelaki','wanita','campuran') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'campuran',
  `jenis_perlawanan` enum('individu','berpasukan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'berpasukan',
  `ikon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `status` enum('aktif','tidak_aktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_sukan`
--

LOCK TABLES `tbl_sukan` WRITE;
/*!40000 ALTER TABLE `tbl_sukan` DISABLE KEYS */;
INSERT INTO `tbl_sukan` VALUES (1,'Bola Sepak','lelaki','berpasukan','bi-dribbble','Pertandingan bola sepak 9-sebelah padang terbuka.','aktif','2026-07-20 13:50:41'),(2,'Badminton','campuran','berpasukan','bi-lightning-fill','Acara badminton bergu campuran dan bergu lelaki.','aktif','2026-07-20 13:50:41'),(3,'Catur','campuran','individu','bi-grid-3x3-gap-fill','Pertandingan catur klasik intelek.','aktif','2026-07-20 13:50:41'),(4,'Dart','campuran','individu','bi-bullseye','Pertandingan ketepatan balingan dart.','aktif','2026-07-20 13:50:41'),(5,'Netball','wanita','berpasukan','bi-basketball','Acara bola jaring wanita antarabangsa.','aktif','2026-07-20 13:50:41'),(6,'Karom','campuran','individu','bi-circle-fill','Pertandingan karom beregu terbuka.','aktif','2026-07-20 13:50:41');
/*!40000 ALTER TABLE `tbl_sukan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_venue`
--

DROP TABLE IF EXISTS `tbl_venue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_venue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_tempat` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `kapasiti` int(10) unsigned DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `dicipta_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_venue`
--

LOCK TABLES `tbl_venue` WRITE;
/*!40000 ALTER TABLE `tbl_venue` DISABLE KEYS */;
INSERT INTO `tbl_venue` VALUES (1,'Stadium Perpaduan Petra Jaya','Jalan Stadium, Petra Jaya, 93050 Kuching, Sarawak',1.5833330,110.3500000,5000,'Gelanggang utama badminton dan majlis penutup.','2026-07-20 13:50:41'),(2,'Padang Bola Sepak JTS','Jalan Simpang Tiga, 93300 Kuching, Sarawak',1.5305560,110.3563890,1000,'Padang rasmi bagi perlawanan bola sepak.','2026-07-20 13:50:41'),(3,'Dewan Serbaguna Ibu Pejabat JTS','Menara Tanah & Survei, Simpang Tiga, 93300 Kuching, Sarawak',1.5310000,110.3570000,400,'Lokasi perlawanan Catur, Dart dan Karom, serta Majlis Pembukaan.','2026-07-20 13:50:41');
/*!40000 ALTER TABLE `tbl_venue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `vw_kedudukan_pingat`
--

DROP TABLE IF EXISTS `vw_kedudukan_pingat`;
/*!50001 DROP VIEW IF EXISTS `vw_kedudukan_pingat`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_kedudukan_pingat` AS SELECT 
 1 AS `bahagian_id`,
 1 AS `nama_bahagian`,
 1 AS `singkatan`,
 1 AS `logo_url`,
 1 AS `jenis`,
 1 AS `emas`,
 1 AS `perak`,
 1 AS `gangsa`,
 1 AS `jumlah`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vw_kedudukan_pingat`
--

/*!50001 DROP VIEW IF EXISTS `vw_kedudukan_pingat`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_kedudukan_pingat` AS select `b`.`id` AS `bahagian_id`,`b`.`nama_bahagian` AS `nama_bahagian`,`b`.`singkatan` AS `singkatan`,`b`.`logo_url` AS `logo_url`,`b`.`jenis` AS `jenis`,sum((case when (`k`.`jenis_pingat` = 'emas') then 1 else 0 end)) AS `emas`,sum((case when (`k`.`jenis_pingat` = 'perak') then 1 else 0 end)) AS `perak`,sum((case when (`k`.`jenis_pingat` = 'gangsa') then 1 else 0 end)) AS `gangsa`,sum((case when (`k`.`jenis_pingat` in ('emas','perak','gangsa')) then 1 else 0 end)) AS `jumlah` from ((`tbl_bahagian` `b` left join `tbl_pasukan` `p` on((`p`.`bahagian_id` = `b`.`id`))) left join `tbl_keputusan` `k` on((`k`.`pasukan_menang_id` = `p`.`id`))) group by `b`.`id`,`b`.`nama_bahagian`,`b`.`singkatan`,`b`.`logo_url`,`b`.`jenis` order by `emas` desc,`perak` desc,`gangsa` desc,`b`.`nama_bahagian` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-21  1:06:42
