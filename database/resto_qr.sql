/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 10.4.32-MariaDB : Database - resto_qr
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


/*Table structure for table `admin` */

DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `admin` */

insert  into `admin`(`id_admin`,`username`,`password`,`nama`) values 
(1,'admin','0192023a7bbd73250516f069df18b500','Administrator');

/*Table structure for table `bank` */

DROP TABLE IF EXISTS `bank`;

CREATE TABLE `bank` (
  `id_bank` int(11) NOT NULL AUTO_INCREMENT,
  `nama_bank` varchar(100) DEFAULT NULL,
  `nama_pemilik` varchar(100) DEFAULT NULL,
  `nomor_rekening` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `qris` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_bank`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `bank` */

insert  into `bank`(`id_bank`,`nama_bank`,`nama_pemilik`,`nomor_rekening`,`logo`,`qris`) values 
(2,'Dana','Awendika Rizky Firmansyah','0882005281057','logo_1779019495_965.jpg','qris_1779019568_520.jpeg');

/*Table structure for table `detail_transaksi` */

DROP TABLE IF EXISTS `detail_transaksi`;

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_menu` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_transaksi` (`id_transaksi`),
  KEY `id_menu` (`id_menu`)
  
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `detail_transaksi` */

insert  into `detail_transaksi`(`id_detail`,`id_transaksi`,`id_menu`,`qty`,`subtotal`) values 
(1,1,7,1,20000),
(2,2,7,1,20000),
(3,3,2,1,5000),
(4,4,5,1,11000),
(5,5,5,1,11000),
(6,6,5,1,11000),
(7,7,2,1,5000),
(8,8,3,2,30000),
(9,9,1,1,3000),
(10,9,2,1,5000),
(11,9,3,1,15000),
(12,10,3,1,15000),
(13,11,4,1,13000),
(14,12,5,1,11000),
(15,13,3,1,15000),
(16,14,3,1,15000),
(17,15,6,1,15000),
(18,16,5,1,11000),
(19,17,6,2,30000),
(20,18,2,1,5000),
(21,19,6,1,15000),
(22,20,6,1,15000),
(23,22,5,1,11000),
(24,24,6,1,15000),
(25,25,6,1,15000),
(26,26,6,1,15000),
(27,27,6,1,15000),
(28,28,7,1,20000),
(29,29,5,1,11000);

/*Table structure for table `kategori` */

DROP TABLE IF EXISTS `kategori`;

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `kategori` */

insert  into `kategori`(`id_kategori`,`nama_kategori`,`status`) values 
(2,'Minuman','aktif'),
(3,'Makanan','aktif'),
(4,'Cemilan','aktif');

/*Table structure for table `meja` */

DROP TABLE IF EXISTS `meja`;

CREATE TABLE `meja` (
  `id_meja` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_meja` varchar(20) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_meja`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `meja` */

insert  into `meja`(`id_meja`,`nomor_meja`,`qr_code`) values 
(7,'1','meja-1.png'),
(8,'2','meja-2.png'),
(9,'3','meja-3.png'),
(10,'4','meja-4.png'),
(11,'5','meja-5.png');

/*Table structure for table `menu` */

DROP TABLE IF EXISTS `menu`;

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL AUTO_INCREMENT,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_menu` varchar(100) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  PRIMARY KEY (`id_menu`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `menu` */

insert  into `menu`(`id_menu`,`id_kategori`,`nama_menu`,`harga`,`stok`,`foto`,`status`) values 
(1,2,'Es teh Panas',3000,66,'download (10).jfif','aktif'),
(2,3,'Rames',5000,90,'Nasi rames.jfif','aktif'),
(3,3,'nasi goreng',15000,85,'download (11).jfif','aktif'),
(4,4,'Lumpia Lumer',13000,8,'Lumpia Ubi Ungu Frozen isi 4.jfif','aktif'),
(5,4,'Tempe Mendoan',11000,3,'Menu Harian Ramadhan ke-17 _ Mantap Enak Menu Tempe yang Tinggi Protein.jfif','aktif'),
(6,3,'Bakmie Soerabaya',15000,89,'PAKET BAKMIE GORENG.jfif','aktif'),
(7,2,'Es Matcha Premium',20000,11,'download (12).jfif','aktif');

/*Table structure for table `transaksi` */

DROP TABLE IF EXISTS `transaksi`;

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL AUTO_INCREMENT,
  `no_pesanan` varchar(100) DEFAULT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `nomor_meja` varchar(20) DEFAULT NULL,
  `metode_pembayaran` enum('qris','tunai') DEFAULT NULL,
  `status_pembayaran` enum('pending','dibayar','gagal') DEFAULT 'pending',
  `status_pesanan` enum('menunggu','diproses','diantar','selesai','gagal') DEFAULT 'menunggu',
  `total` int(11) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `waktu_diantar` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  PRIMARY KEY (`id_transaksi`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `transaksi` */

insert  into `transaksi`(`id_transaksi`,`no_pesanan`,`nama_pelanggan`,`nomor_meja`,`metode_pembayaran`,`status_pembayaran`,`status_pesanan`,`total`,`tanggal`,`bukti_pembayaran`,`catatan`,`waktu_diantar`,`waktu_selesai`) values 
(1,'ORD6282','awen','1','qris','dibayar','selesai',20000,'2026-05-17 13:23:14','1778998994Logo_Milad_Nasyiatul_Aisyiyah_95-20260512104844.png','pedas bgt','2026-05-17 13:58:38','2026-05-17 13:58:39'),
(2,'ORD3966','kamto','1','tunai','dibayar','selesai',20000,'2026-05-17 14:08:23','','sambal pisah, kecap jangan kebanyakan','2026-05-17 14:18:07','2026-05-17 16:07:02'),
(3,'ORD7454','andi','1','qris','dibayar','selesai',5000,'2026-05-17 14:18:53','1779002333Merah & Putih Ilustrasi Hari Kesaktian Pancasila Poster (1).png','','2026-05-17 14:22:10','2026-05-17 16:07:04'),
(4,'ORD3021','Slamet Riyadi','1','qris','dibayar','selesai',11000,'2026-05-17 16:06:42','1779008802Merah & Putih Ilustrasi Hari Kesaktian Pancasila Poster (1).png','pedas','2026-05-17 16:10:00','2026-05-17 16:37:57'),
(5,'ORD6165','darwin','1','qris','dibayar','selesai',11000,'2026-05-17 16:38:17','1779010697download (12).jfif','pedas','2026-05-17 16:52:05','2026-05-17 16:52:07'),
(6,'ORD2283','awen','1','tunai','dibayar','selesai',11000,'2026-05-17 16:53:43','','a','2026-05-17 16:53:59','2026-05-17 16:54:01'),
(7,'ORD5362','yandi','1','qris','dibayar','selesai',5000,'2026-05-17 17:47:23','1779014843_6a099cbb1abbc.jfif','','2026-05-17 17:51:09','2026-05-17 18:01:16'),
(8,'ORD2956','jeck','1','qris','dibayar','selesai',30000,'2026-05-17 17:56:28','1779015388_6a099edca1fe4.png','pedas sekali','2026-05-17 17:57:04','2026-05-17 17:57:06'),
(9,'ORD8063','samuel','1','qris','dibayar','selesai',23000,'2026-05-17 18:01:51','1779015711_6a09a01fdbe32.png','sedang pedas','2026-05-17 18:05:20','2026-05-17 18:08:23'),
(10,'ORD6071','anis','3','tunai','dibayar','selesai',15000,'2026-05-17 18:03:00','','','2026-05-17 18:06:23','2026-05-17 18:09:26'),
(11,'ORD2438','yanto','3','tunai','dibayar','selesai',13000,'2026-05-17 18:11:31','','','2026-05-17 18:15:00','2026-05-17 18:18:03'),
(12,'ORD9687','kucing','3','qris','dibayar','selesai',11000,'2026-05-17 19:06:35','1779019595_6a09af4b7d36a.webp','pedas','2026-05-17 19:09:59','2026-05-17 19:15:37'),
(13,'ORD8211','ok','1','qris','dibayar','selesai',15000,'2026-05-17 20:14:39','1779023679_6a09bf3fbf02f.jpeg','ya','2026-05-17 20:18:05','2026-05-18 13:26:30'),
(14,'ORD6618','andi','1','qris','dibayar','selesai',15000,'2026-05-18 16:03:12','1779094992_6a0ad5d0b544f.jpeg','pedas sekalii','2026-05-18 16:07:03','2026-05-18 16:31:39'),
(15,'ORD7244','stecu','1','qris','dibayar','selesai',15000,'2026-05-18 17:07:34','1779098854_6a0ae4e6e7ef1.jfif','tidak pedas','2026-05-18 17:16:53','2026-05-18 17:19:55'),
(16,'ORD3103','dimas','1','tunai','gagal','gagal',11000,'2026-05-18 17:19:24','','',NULL,NULL),
(17,'ORD8059','dinar','1','tunai','dibayar','selesai',30000,'2026-05-18 17:20:32','','','2026-05-18 17:23:53','2026-05-18 17:30:36'),
(18,'ORD7087','kucing','1','tunai','dibayar','selesai',5000,'2026-05-18 17:47:25','','meong','2026-05-18 17:47:55','2026-05-18 17:47:58'),
(19,'ORD7949','niko','5','tunai','dibayar','selesai',15000,'2026-05-18 17:57:07','','','2026-05-18 17:57:48','2026-05-18 17:57:51'),
(20,'ORD7811','wisnu','5','tunai','dibayar','selesai',15000,'2026-05-18 18:03:42','','','2026-05-18 18:05:44','2026-05-18 18:05:49'),
(21,'ORD4739','wisnu','5','tunai','dibayar','selesai',0,'2026-05-18 18:03:43','','','2026-05-18 18:05:46','2026-05-18 18:05:48'),
(22,'ORD6239','test','5','tunai','gagal','gagal',11000,'2026-05-18 18:06:17','','',NULL,NULL),
(23,'ORD4876','test','5','tunai','gagal','gagal',0,'2026-05-18 18:06:18','','',NULL,NULL),
(24,'ORD5613','testt','1','tunai','dibayar','selesai',15000,'2026-05-18 18:08:12','','','2026-05-18 18:11:53','2026-05-18 18:28:18'),
(25,'ORD6727','kucing','1','qris','dibayar','selesai',15000,'2026-05-20 16:35:53','1779269752_6a0d8079016e3.png','tidak pedas','2026-05-20 18:35:29','2026-05-20 18:38:30'),
(26,'ORD2513','Slamet Riyadi','1','tunai','dibayar','selesai',15000,'2026-05-20 18:52:23','','','2026-05-20 18:53:07','2026-05-20 18:53:09'),
(27,'ORD2383','maryati','1','qris','dibayar','selesai',15000,'2026-05-20 21:26:45','1779287205_6a0dc4a54ca34.png','pedas','2026-05-20 21:30:55','2026-05-20 21:33:56'),
(28,'TKW-20260521-9E1C0','fajar',NULL,'tunai','dibayar','selesai',20000,'2026-05-21 13:37:39',NULL,'pedas banget',NULL,NULL),
(29,'TKW-20260521-D8711','setya',NULL,'tunai','dibayar','selesai',11000,'2026-05-21 13:40:30',NULL,'',NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
