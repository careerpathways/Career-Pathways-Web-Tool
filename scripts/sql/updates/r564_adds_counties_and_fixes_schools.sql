DROP TABLE IF EXISTS `counties`;
CREATE TABLE `counties` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `county` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=38;
INSERT INTO `counties`(`id`,`county`) values (1,'Benton'),(2,'Clackamas'),(3,'Clatsop'),(4,'Columbia'),(5,'Coos'),(6,'Crook'),(7,'Curry'),(8,'Deschutes'),(9,'Douglas'),(10,'Gilliam'),(12,'Grant'),(13,'Harney'),(14,'Hood River'),(15,'Jackson'),(16,'Jefferson'),(17,'Josephine'),(18,'Klamath'),(19,'Lake'),(20,'Lane'),(21,'Lincoln'),(22,'Linn'),(23,'Malheur'),(24,'Marion'),(25,'Morrow'),(26,'Multnomah'),(27,'Polk'),(28,'Sherman'),(29,'Tillamook'),(30,'Umatilla'),(31,'Union'),(32,'Wallowa'),(33,'Wasco'),(34,'Washington'),(35,'Wheeler'),(36,'Yamhill'),(37,'Baker');

# Update the schools table to map county names to numeric IDs
UPDATE schools s, counties c
SET school_county = c.id 
WHERE c.county = s.school_county;


