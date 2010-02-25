-- -----------------------------------------------------
-- Table `b8_words`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `b8_words` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `word` VARCHAR(30) NOT NULL ,
  `ham` BIGINT UNSIGNED NULL ,
  `spam` BIGINT UNSIGNED NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `b8_words_word` (`word` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `b8_categories`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `b8_categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `category` VARCHAR(4) NULL ,
  `total` BIGINT UNSIGNED NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `b8_categories_category` (`category` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Data for table `b8_categories`
-- -----------------------------------------------------
INSERT INTO `b8_categories` (`id`, `category`, `total`) VALUES (1, 'ham', 0);
INSERT INTO `b8_categories` (`id`, `category`, `total`) VALUES (2, 'spam', 0);

