
CREATE TABLE `Journalist` (
    `id` int UNSIGNED PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    -- közös aliason nem szerepelhet két újságíró
    -- kereséshez is jó, mert indexelt.
    `alias` VARCHAR(30) UNIQUE,
    `group` VARCHAR(30) NOT NULL
)DEFAULT CHARSET utf8;



-- id generátor az optiomális azonosító léptetésért.
CREATE TRIGGER `idGenerator`
BEFORE INSERT ON `Journalist`
FOR EACH ROW
BEGIN 
    DECLARE `newId` int;
    SELECT MAX(`id`) + 1 INTO `newId` FROM `Journalist`;
    
    IF `newId` is NULL THEN
        SET `newId` = 1;
    END IF;

    SET NEW.`id` = `newId`;
END;