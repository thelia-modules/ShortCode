
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- short_code
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `short_code`;

CREATE TABLE `short_code`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `tag` VARCHAR(55) NOT NULL,
    `event` VARCHAR(255) NOT NULL,
    `active` TINYINT,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `short_code_U_1` (`tag`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
