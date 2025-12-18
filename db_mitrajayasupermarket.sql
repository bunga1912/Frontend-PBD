-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: db_mitrajayasupermarket
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `barang`
--

DROP TABLE IF EXISTS `barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barang` (
  `idbarang` int(11) NOT NULL AUTO_INCREMENT,
  `jenis` char(1) NOT NULL,
  `nama` varchar(45) NOT NULL,
  `idsatuan` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `harga` int(11) DEFAULT NULL,
  `margin_persen` double DEFAULT NULL,
  `harga_jual` int(11) DEFAULT NULL,
  PRIMARY KEY (`idbarang`),
  KEY `idsatuan` (`idsatuan`),
  CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`idsatuan`) REFERENCES `satuan` (`idsatuan`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang`
--

LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES (1,'A','Gula Pasir',3,1,15000,5,15750),(2,'A','Tepung Terigu',3,1,12000,5,12600),(3,'B','Teh Celup',1,1,18000,5,18900),(4,'B','Susu Kental Manis',1,0,14000,5,14700),(5,'C','Detergen Bubuk',5,1,38000,5,39900),(6,'C','Pelicin Pakaian',4,0,22000,5,23100),(7,'D','Kornet Sapi',8,1,32000,5,33600),(8,'D','Sarden',8,1,26000,5,27300),(9,'E','Garam Dapur',1,1,5000,5,5250),(10,'F','Tisu Gulung',5,0,28000,5,29400),(11,'A','Minyak Goreng',4,1,20000,5,21000);
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_penerimaan`
--

DROP TABLE IF EXISTS `detail_penerimaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_penerimaan` (
  `iddetail_penerimaan` bigint(20) NOT NULL AUTO_INCREMENT,
  `idpenerimaan` bigint(20) DEFAULT NULL,
  `barang_idbarang` int(11) DEFAULT NULL,
  `jumlah_terima` int(11) DEFAULT NULL,
  `harga_satuan_terima` int(11) DEFAULT NULL,
  `sub_total_terima` int(11) DEFAULT NULL,
  PRIMARY KEY (`iddetail_penerimaan`),
  KEY `idpenerimaan` (`idpenerimaan`),
  KEY `barang_idbarang` (`barang_idbarang`),
  CONSTRAINT `detail_penerimaan_ibfk_1` FOREIGN KEY (`idpenerimaan`) REFERENCES `penerimaan` (`idpenerimaan`),
  CONSTRAINT `detail_penerimaan_ibfk_2` FOREIGN KEY (`barang_idbarang`) REFERENCES `barang` (`idbarang`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penerimaan`
--

LOCK TABLES `detail_penerimaan` WRITE;
/*!40000 ALTER TABLE `detail_penerimaan` DISABLE KEYS */;
INSERT INTO `detail_penerimaan` VALUES (19,1,1,5,15000,75000),(20,1,2,10,12000,120000),(21,1,5,1,30000,30000),(22,2,3,4,18000,72000),(23,2,9,14,5000,70000),(24,2,7,1,32000,32000),(25,3,2,10,12000,120000),(26,3,5,5,38000,190000),(27,3,8,2,26000,52000),(31,5,7,2,32000,64000),(32,5,10,5,28000,140000),(33,5,1,3,15000,45000);
/*!40000 ALTER TABLE `detail_penerimaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_pengadaan`
--

DROP TABLE IF EXISTS `detail_pengadaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_pengadaan` (
  `iddetail_pengadaan` bigint(20) NOT NULL AUTO_INCREMENT,
  `harga_satuan` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `sub_total` int(11) DEFAULT NULL,
  `idbarang` int(11) DEFAULT NULL,
  `idpengadaan` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`iddetail_pengadaan`),
  KEY `idbarang` (`idbarang`),
  KEY `idpengadaan` (`idpengadaan`),
  CONSTRAINT `detail_pengadaan_ibfk_1` FOREIGN KEY (`idbarang`) REFERENCES `barang` (`idbarang`),
  CONSTRAINT `detail_pengadaan_ibfk_2` FOREIGN KEY (`idpengadaan`) REFERENCES `pengadaan` (`idpengadaan`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_pengadaan`
--

LOCK TABLES `detail_pengadaan` WRITE;
/*!40000 ALTER TABLE `detail_pengadaan` DISABLE KEYS */;
INSERT INTO `detail_pengadaan` VALUES (1,15000,10,150000,1,1),(2,12000,20,240000,2,1),(3,30000,2,60000,5,1),(4,18000,5,90000,3,2),(5,5000,20,100000,9,2),(6,32000,2,64000,7,2),(7,12000,10,120000,2,3),(8,38000,5,190000,5,3),(9,26000,2,52000,8,3),(10,26000,3,78000,8,4),(11,5000,10,50000,9,4),(12,14000,1,14000,4,4),(13,32000,5,160000,7,5),(14,28000,10,280000,10,5),(15,15000,5,75000,1,5),(16,15000,2,30000,1,6),(20,15000,50,750000,1,7),(21,12000,30,360000,2,7),(22,5000,20,100000,9,7),(23,12000,7,84000,2,8),(24,38000,8,304000,5,8);
/*!40000 ALTER TABLE `detail_pengadaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_penjualan`
--

DROP TABLE IF EXISTS `detail_penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_penjualan` (
  `iddetail_penjualan` bigint(20) NOT NULL AUTO_INCREMENT,
  `harga_satuan` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL,
  `penjualan_idpenjualan` int(11) DEFAULT NULL,
  `idbarang` int(11) DEFAULT NULL,
  PRIMARY KEY (`iddetail_penjualan`),
  KEY `penjualan_idpenjualan` (`penjualan_idpenjualan`),
  KEY `idbarang` (`idbarang`),
  CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`penjualan_idpenjualan`) REFERENCES `penjualan` (`idpenjualan`),
  CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`idbarang`) REFERENCES `barang` (`idbarang`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penjualan`
--

LOCK TABLES `detail_penjualan` WRITE;
/*!40000 ALTER TABLE `detail_penjualan` DISABLE KEYS */;
INSERT INTO `detail_penjualan` VALUES (1,15000,10,150000,1,1),(2,12000,10,120000,1,2),(3,18000,1,18000,1,3),(4,5000,2,10000,1,9),(5,32000,10,320000,2,7),(6,26000,5,130000,2,8),(7,22000,3,66000,3,6),(8,28000,2,56000,3,10),(9,18000,2,36000,4,3),(10,15000,2,30000,4,1),(11,5000,2,10000,4,9),(12,38000,10,380000,5,5),(13,32000,2,64000,5,7),(14,26000,3,78000,5,8),(15,14000,2,28000,6,4),(16,26000,4,104000,6,8),(17,5000,5,25000,7,9),(18,18000,9,162000,7,3);
/*!40000 ALTER TABLE `detail_penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_retur`
--

DROP TABLE IF EXISTS `detail_retur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_retur` (
  `iddetail_retur` int(11) NOT NULL AUTO_INCREMENT,
  `jumlah` int(11) DEFAULT NULL,
  `alasan` varchar(200) DEFAULT NULL,
  `idretur` bigint(20) DEFAULT NULL,
  `iddetail_penerimaan` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`iddetail_retur`),
  KEY `idretur` (`idretur`),
  KEY `iddetail_penerimaan` (`iddetail_penerimaan`),
  CONSTRAINT `detail_retur_ibfk_1` FOREIGN KEY (`idretur`) REFERENCES `retur` (`idretur`),
  CONSTRAINT `detail_retur_ibfk_2` FOREIGN KEY (`iddetail_penerimaan`) REFERENCES `detail_penerimaan` (`iddetail_penerimaan`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_retur`
--

LOCK TABLES `detail_retur` WRITE;
/*!40000 ALTER TABLE `detail_retur` DISABLE KEYS */;
INSERT INTO `detail_retur` VALUES (7,2,'Barang rusak',1,19),(8,3,'Kemasan rusak',1,20),(9,1,'Tidak sesuai spesifikasi',2,25),(10,2,'Barang cacat',2,26),(11,1,'Salah kirim',3,22),(12,5,'Kadaluarsa',3,23),(15,2,'Barang rusak',1,19),(16,3,'Kemasan rusak',1,20),(17,1,'Tidak sesuai spesifikasi',2,25),(18,2,'Barang cacat',2,26),(19,1,'Salah kirim',3,22),(20,5,'Kadaluarsa',3,23),(21,1,'Barang rusak',4,31),(22,2,'Tidak sesuai pesanan',4,32);
/*!40000 ALTER TABLE `detail_retur` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_stok`
--

DROP TABLE IF EXISTS `kartu_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kartu_stok` (
  `idkartu_stok` bigint(20) NOT NULL AUTO_INCREMENT,
  `jenis_transaksi` char(1) DEFAULT NULL,
  `masuk` int(11) DEFAULT NULL,
  `keluar` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `idtransaksi` int(11) DEFAULT NULL,
  `idbarang` int(11) DEFAULT NULL,
  PRIMARY KEY (`idkartu_stok`),
  KEY `idbarang` (`idbarang`),
  CONSTRAINT `kartu_stok_ibfk_1` FOREIGN KEY (`idbarang`) REFERENCES `barang` (`idbarang`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_stok`
--

LOCK TABLES `kartu_stok` WRITE;
/*!40000 ALTER TABLE `kartu_stok` DISABLE KEYS */;
INSERT INTO `kartu_stok` VALUES (1,'K',0,5,-5,'2025-11-26 14:45:55',7,9),(2,'K',0,9,-9,'2025-11-26 14:45:55',7,3),(3,'M',5,0,5,'2025-11-26 15:18:32',1,1),(4,'M',10,0,10,'2025-11-26 15:18:32',1,2),(5,'M',1,0,1,'2025-11-26 15:18:32',1,5),(6,'M',4,0,-5,'2025-11-26 15:18:32',2,3),(7,'M',14,0,9,'2025-11-26 15:18:32',2,9),(8,'M',1,0,1,'2025-11-26 15:18:32',2,7),(9,'M',10,0,20,'2025-11-26 15:18:32',3,2),(10,'M',5,0,6,'2025-11-26 15:18:32',3,5),(11,'M',2,0,2,'2025-11-26 15:18:32',3,8),(12,'M',0,0,2,'2025-11-26 15:18:32',4,8),(13,'M',0,0,9,'2025-11-26 15:18:32',4,9),(14,'M',0,0,0,'2025-11-26 15:18:32',4,4),(15,'M',2,0,3,'2025-11-26 15:18:32',5,7),(16,'M',5,0,5,'2025-11-26 15:18:32',5,10),(17,'M',3,0,8,'2025-11-26 15:18:32',5,1),(18,'R',0,2,6,'2025-11-26 15:22:43',1,1),(19,'R',0,3,17,'2025-11-26 15:22:43',1,2),(20,'R',0,1,16,'2025-11-26 15:22:43',2,2),(21,'R',0,2,4,'2025-11-26 15:22:43',2,5),(22,'R',0,1,-6,'2025-11-26 15:22:43',3,3),(23,'R',0,5,4,'2025-11-26 15:22:43',3,9),(24,'R',0,2,4,'2025-11-26 15:24:31',1,1),(25,'R',0,3,13,'2025-11-26 15:24:31',1,2),(26,'R',0,1,12,'2025-11-26 15:24:31',2,2),(27,'R',0,2,2,'2025-11-26 15:24:31',2,5),(28,'R',0,1,-7,'2025-11-26 15:24:31',3,3),(29,'R',0,5,-1,'2025-11-26 15:24:31',3,9),(30,'R',0,1,2,'2025-11-26 15:24:31',4,7),(31,'R',0,2,3,'2025-11-26 15:24:31',4,10);
/*!40000 ALTER TABLE `kartu_stok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `margin_penjualan`
--

DROP TABLE IF EXISTS `margin_penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `margin_penjualan` (
  `idmargin_penjualan` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `persen` double DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `iduser` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idmargin_penjualan`),
  KEY `iduser` (`iduser`),
  CONSTRAINT `margin_penjualan_ibfk_1` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `margin_penjualan`
--

LOCK TABLES `margin_penjualan` WRITE;
/*!40000 ALTER TABLE `margin_penjualan` DISABLE KEYS */;
INSERT INTO `margin_penjualan` VALUES (1,'2025-11-14 23:45:09',5,1,1,'2025-11-14 23:45:09'),(2,'2025-11-14 23:45:09',7.5,0,1,'2025-11-26 08:26:36'),(3,'2025-11-14 23:45:09',10,0,2,'2025-11-26 13:11:07'),(4,'2025-11-14 23:45:09',12.5,0,2,'2025-11-26 13:10:59'),(5,'2025-11-14 23:45:09',15,0,1,'2025-11-14 23:45:09'),(6,'2025-11-14 23:45:09',2.3,0,2,'2025-11-14 23:45:09'),(7,'2025-11-26 19:21:58',4,0,NULL,'2025-11-26 19:22:16');
/*!40000 ALTER TABLE `margin_penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penerimaan`
--

DROP TABLE IF EXISTS `penerimaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penerimaan` (
  `idpenerimaan` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` char(1) DEFAULT NULL,
  `idpengadaan` bigint(20) DEFAULT NULL,
  `iduser` int(11) DEFAULT NULL,
  `subtotal_nilai` int(11) DEFAULT 0,
  PRIMARY KEY (`idpenerimaan`),
  KEY `idpengadaan` (`idpengadaan`),
  KEY `iduser` (`iduser`),
  CONSTRAINT `penerimaan_ibfk_1` FOREIGN KEY (`idpengadaan`) REFERENCES `pengadaan` (`idpengadaan`),
  CONSTRAINT `penerimaan_ibfk_2` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penerimaan`
--

LOCK TABLES `penerimaan` WRITE;
/*!40000 ALTER TABLE `penerimaan` DISABLE KEYS */;
INSERT INTO `penerimaan` VALUES (1,'2025-11-15 08:53:30','P',1,4,225000),(2,'2025-11-15 08:53:30','S',2,4,174000),(3,'2025-11-15 08:53:30','C',3,4,362000),(5,'2025-11-15 08:53:30','P',5,4,249000);
/*!40000 ALTER TABLE `penerimaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengadaan`
--

DROP TABLE IF EXISTS `pengadaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengadaan` (
  `idpengadaan` bigint(20) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_iduser` int(11) DEFAULT NULL,
  `status` char(1) DEFAULT NULL,
  `vendor_idvendor` int(11) DEFAULT NULL,
  `subtotal_nilai` int(11) DEFAULT NULL,
  `ppn` int(11) DEFAULT NULL,
  `total_nilai` int(11) DEFAULT NULL,
  PRIMARY KEY (`idpengadaan`),
  KEY `user_iduser` (`user_iduser`),
  KEY `vendor_idvendor` (`vendor_idvendor`),
  CONSTRAINT `pengadaan_ibfk_1` FOREIGN KEY (`user_iduser`) REFERENCES `user` (`iduser`),
  CONSTRAINT `pengadaan_ibfk_2` FOREIGN KEY (`vendor_idvendor`) REFERENCES `vendor` (`idvendor`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengadaan`
--

LOCK TABLES `pengadaan` WRITE;
/*!40000 ALTER TABLE `pengadaan` DISABLE KEYS */;
INSERT INTO `pengadaan` VALUES (1,'2025-01-10 02:15:00',1,'A',1,450000,45000,495000),(2,'2025-01-12 07:30:00',5,'A',3,280000,28000,308000),(3,'2025-01-14 03:00:00',5,'A',2,360000,36000,396000),(4,'2025-01-15 09:45:00',1,'A',4,150000,15000,165000),(5,'2025-01-17 04:20:00',5,'A',5,520000,52000,572000),(6,'2025-11-15 04:17:09',1,'P',2,30000,50000,80000),(7,'2025-11-23 18:03:18',5,'P',1,1210000,50000,1260000),(8,'2025-11-26 19:24:57',2,'P',8,388000,2500,390500);
/*!40000 ALTER TABLE `pengadaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjualan` (
  `idpenjualan` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal_nilai` int(11) DEFAULT NULL,
  `ppn` int(11) DEFAULT NULL,
  `total_nilai` int(11) DEFAULT NULL,
  `iduser` int(11) DEFAULT NULL,
  `idmargin_penjualan` int(11) DEFAULT NULL,
  PRIMARY KEY (`idpenjualan`),
  KEY `iduser` (`iduser`),
  KEY `idmargin_penjualan` (`idmargin_penjualan`),
  CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`),
  CONSTRAINT `penjualan_ibfk_2` FOREIGN KEY (`idmargin_penjualan`) REFERENCES `margin_penjualan` (`idmargin_penjualan`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
INSERT INTO `penjualan` VALUES (1,'2025-01-15 03:30:00',300000,30000,330000,3,6),(2,'2025-01-15 04:00:00',450000,45000,495000,3,1),(3,'2025-01-16 02:15:00',120000,12000,132000,3,4),(4,'2025-01-16 07:20:00',80000,8000,88000,3,1),(5,'2025-01-17 09:45:00',520000,52000,572000,3,5),(6,'2025-11-15 08:36:53',132000,10000,151900,3,2),(7,'2025-11-26 14:45:55',187000,2500,198850,3,1);
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retur`
--

DROP TABLE IF EXISTS `retur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retur` (
  `idretur` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `idpenerimaan` bigint(20) DEFAULT NULL,
  `iduser` int(11) DEFAULT NULL,
  PRIMARY KEY (`idretur`),
  KEY `idpenerimaan` (`idpenerimaan`),
  KEY `iduser` (`iduser`),
  CONSTRAINT `retur_ibfk_1` FOREIGN KEY (`idpenerimaan`) REFERENCES `penerimaan` (`idpenerimaan`),
  CONSTRAINT `retur_ibfk_2` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `retur`
--

LOCK TABLES `retur` WRITE;
/*!40000 ALTER TABLE `retur` DISABLE KEYS */;
INSERT INTO `retur` VALUES (1,'2025-11-22 10:52:27',1,4),(2,'2025-11-22 10:52:27',3,4),(3,'2025-11-22 10:52:27',2,4),(4,'2025-11-22 10:52:27',5,4);
/*!40000 ALTER TABLE `retur` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `idrole` int(11) NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(100) NOT NULL,
  PRIMARY KEY (`idrole`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'admin'),(2,'super admin'),(3,'kasir'),(4,'warehouse'),(5,'purchasing'),(9,'direktur');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satuan`
--

DROP TABLE IF EXISTS `satuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `satuan` (
  `idsatuan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_satuan` varchar(45) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`idsatuan`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satuan`
--

LOCK TABLES `satuan` WRITE;
/*!40000 ALTER TABLE `satuan` DISABLE KEYS */;
INSERT INTO `satuan` VALUES (1,'Pcs',1),(3,'Kg',0),(4,'Liter',0),(5,'Pack',0),(8,'Kaleng',0),(10,'Karung',0);
/*!40000 ALTER TABLE `satuan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `iduser` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` varchar(100) NOT NULL,
  `idrole` int(11) DEFAULT NULL,
  PRIMARY KEY (`iduser`),
  KEY `idrole` (`idrole`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`idrole`) REFERENCES `role` (`idrole`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'bunga07','bunga123',1),(2,'lala11','234lula',2),(3,'salsa005','9876bongs',3),(4,'bila3431','890akucantik',4),(5,'struick12','biasasaja45',5),(6,'sabrina098','cantik12',9);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor`
--

DROP TABLE IF EXISTS `vendor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor` (
  `idvendor` int(11) NOT NULL AUTO_INCREMENT,
  `nama_vendor` varchar(100) DEFAULT NULL,
  `badan_hukum` char(1) DEFAULT NULL,
  `status` char(1) DEFAULT NULL,
  PRIMARY KEY (`idvendor`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor`
--

LOCK TABLES `vendor` WRITE;
/*!40000 ALTER TABLE `vendor` DISABLE KEYS */;
INSERT INTO `vendor` VALUES (1,'PT Sumber Rejeki','1','1'),(2,'CV Maju Jaya','0','1'),(3,'UD Makmur Sentosa','0','1'),(4,'PT Indo Supply','0','1'),(5,'PT Perkasa Raya','1','1'),(6,'CV Cahaya Abadi','1','1'),(7,'UD Prima Mandiri','0','1'),(8,'PT Bumi Karya','1','1'),(9,'CV Bintang Terang','0','1'),(10,'PT Sentosa Abadi','1','1'),(11,'PT Sucindo','1','1'),(12,'PT Bulakbanteng','0','0');
/*!40000 ALTER TABLE `vendor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_barang`
--

DROP TABLE IF EXISTS `view_barang`;
/*!50001 DROP VIEW IF EXISTS `view_barang`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_barang` AS SELECT 
 1 AS `idbarang`,
 1 AS `jenis`,
 1 AS `nama`,
 1 AS `idsatuan`,
 1 AS `harga`,
 1 AS `status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_barang_aktif`
--

DROP TABLE IF EXISTS `view_barang_aktif`;
/*!50001 DROP VIEW IF EXISTS `view_barang_aktif`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_barang_aktif` AS SELECT 
 1 AS `idbarang`,
 1 AS `jenis`,
 1 AS `nama`,
 1 AS `idsatuan`,
 1 AS `harga`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_margin`
--

DROP TABLE IF EXISTS `view_margin`;
/*!50001 DROP VIEW IF EXISTS `view_margin`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_margin` AS SELECT 
 1 AS `idmargin_penjualan`,
 1 AS `persen`,
 1 AS `status`,
 1 AS `iduser`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_margin_aktif`
--

DROP TABLE IF EXISTS `view_margin_aktif`;
/*!50001 DROP VIEW IF EXISTS `view_margin_aktif`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_margin_aktif` AS SELECT 
 1 AS `idmargin_penjualan`,
 1 AS `persen`,
 1 AS `iduser`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_role`
--

DROP TABLE IF EXISTS `view_role`;
/*!50001 DROP VIEW IF EXISTS `view_role`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_role` AS SELECT 
 1 AS `idrole`,
 1 AS `nama_role`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_satuan`
--

DROP TABLE IF EXISTS `view_satuan`;
/*!50001 DROP VIEW IF EXISTS `view_satuan`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_satuan` AS SELECT 
 1 AS `idsatuan`,
 1 AS `nama_satuan`,
 1 AS `status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_satuan_aktif`
--

DROP TABLE IF EXISTS `view_satuan_aktif`;
/*!50001 DROP VIEW IF EXISTS `view_satuan_aktif`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_satuan_aktif` AS SELECT 
 1 AS `idsatuan`,
 1 AS `nama_satuan`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_user`
--

DROP TABLE IF EXISTS `view_user`;
/*!50001 DROP VIEW IF EXISTS `view_user`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_user` AS SELECT 
 1 AS `iduser`,
 1 AS `username`,
 1 AS `password`,
 1 AS `idrole`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_vendor`
--

DROP TABLE IF EXISTS `view_vendor`;
/*!50001 DROP VIEW IF EXISTS `view_vendor`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_vendor` AS SELECT 
 1 AS `idvendor`,
 1 AS `nama_vendor`,
 1 AS `badan_hukum`,
 1 AS `status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_vendor_aktif`
--

DROP TABLE IF EXISTS `view_vendor_aktif`;
/*!50001 DROP VIEW IF EXISTS `view_vendor_aktif`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_vendor_aktif` AS SELECT 
 1 AS `idvendor`,
 1 AS `nama_vendor`,
 1 AS `badan_hukum`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `view_barang`
--

/*!50001 DROP VIEW IF EXISTS `view_barang`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_barang` AS select `barang`.`idbarang` AS `idbarang`,`barang`.`jenis` AS `jenis`,`barang`.`nama` AS `nama`,`barang`.`idsatuan` AS `idsatuan`,`barang`.`harga` AS `harga`,`barang`.`status` AS `status` from `barang` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_barang_aktif`
--

/*!50001 DROP VIEW IF EXISTS `view_barang_aktif`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_barang_aktif` AS select `barang`.`idbarang` AS `idbarang`,`barang`.`jenis` AS `jenis`,`barang`.`nama` AS `nama`,`barang`.`idsatuan` AS `idsatuan`,`barang`.`harga` AS `harga` from `barang` where `barang`.`status` = 1 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_margin`
--

/*!50001 DROP VIEW IF EXISTS `view_margin`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_margin` AS select `margin_penjualan`.`idmargin_penjualan` AS `idmargin_penjualan`,`margin_penjualan`.`persen` AS `persen`,`margin_penjualan`.`status` AS `status`,`margin_penjualan`.`iduser` AS `iduser` from `margin_penjualan` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_margin_aktif`
--

/*!50001 DROP VIEW IF EXISTS `view_margin_aktif`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_margin_aktif` AS select `margin_penjualan`.`idmargin_penjualan` AS `idmargin_penjualan`,`margin_penjualan`.`persen` AS `persen`,`margin_penjualan`.`iduser` AS `iduser` from `margin_penjualan` where `margin_penjualan`.`status` = 1 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_role`
--

/*!50001 DROP VIEW IF EXISTS `view_role`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_role` AS select `role`.`idrole` AS `idrole`,`role`.`nama_role` AS `nama_role` from `role` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_satuan`
--

/*!50001 DROP VIEW IF EXISTS `view_satuan`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_satuan` AS select `satuan`.`idsatuan` AS `idsatuan`,`satuan`.`nama_satuan` AS `nama_satuan`,`satuan`.`status` AS `status` from `satuan` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_satuan_aktif`
--

/*!50001 DROP VIEW IF EXISTS `view_satuan_aktif`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_satuan_aktif` AS select `satuan`.`idsatuan` AS `idsatuan`,`satuan`.`nama_satuan` AS `nama_satuan` from `satuan` where `satuan`.`status` = 1 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_user`
--

/*!50001 DROP VIEW IF EXISTS `view_user`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_user` AS select `user`.`iduser` AS `iduser`,`user`.`username` AS `username`,`user`.`password` AS `password`,`user`.`idrole` AS `idrole` from `user` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_vendor`
--

/*!50001 DROP VIEW IF EXISTS `view_vendor`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_vendor` AS select `vendor`.`idvendor` AS `idvendor`,`vendor`.`nama_vendor` AS `nama_vendor`,`vendor`.`badan_hukum` AS `badan_hukum`,`vendor`.`status` AS `status` from `vendor` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_vendor_aktif`
--

/*!50001 DROP VIEW IF EXISTS `view_vendor_aktif`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_vendor_aktif` AS select `vendor`.`idvendor` AS `idvendor`,`vendor`.`nama_vendor` AS `nama_vendor`,`vendor`.`badan_hukum` AS `badan_hukum` from `vendor` where `vendor`.`status` = 1 */;
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

-- Dump completed on 2025-11-27 21:51:39
