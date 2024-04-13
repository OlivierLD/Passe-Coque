-- Doc at https://www.mysqltutorial.org/mysql-basics/mysql-auto_increment/
--        https://www.w3schools.com/php/php_mysql_create_table.asp           <- Good for mySQLi
--
-- Table structure for tables:
--    `nl-subscribers`, News Letter Subscribers
--    `pc-mebers`
-- For MySQL Data Types, see https://www.w3schools.com/mysql/mysql_datatypes.asp
--

CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(320) NOT NULL
);

-- Make email a unique key
CREATE TABLE IF NOT EXISTS `nl-subscribers` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'First and Last Name of the Subscriber',
    `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Email of the Subscriber',
    `active`  boolean default TRUE,
    CONSTRAINT UK_email UNIQUE (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT = 'News Letter Subscribers';


INSERT INTO `nl-subscribers`(`name`, `email`)
VALUES('Joe Shmow', 'joe.show@mysqltests.org');

INSERT INTO `nl-subscribers`(`name`, `email`)
VALUES('Job Larigou', 'job.larigou@mysqltests.org');

SELECT * FROM `nl-subscribers`;

CREATE TABLE IF NOT EXISTS `pc-members` (
    id INT AUTO_INCREMENT PRIMARY KEY, -- reference is NOT unique enough...
    `reference` INT,
    `command-date` TIMESTAMP,      -- Format: YYYY-MM-DD hh:mm:ss
    `command-status` VARCHAR(32),
    `member-first-name` VARCHAR(64),
    `member-last-name` VARCHAR(64),
    `card-url` VARCHAR(512),
    `payer-first-name` VARCHAR(64),
    `payer-last-name` VARCHAR(64),
    `payer-email` VARCHAR(64),
    `company-name` VARCHAR(64),
    `paid-with` VARCHAR(32),
    `fee-category` VARCHAR(32),
    `fee-amount` FLOAT,
    `promo-code` VARCHAR(16),
    `promo-amount` FLOAT,
    `phone` VARCHAR(16),
    `email` VARCHAR(64),
    `address` VARCHAR(128),
    `zip` VARCHAR(8),
    `birth-date` DATE,  -- format 'YYYY-MM-DD'
    `city` VARCHAR(32),
    `sailing-experience` VARCHAR(256),
    `boat-building-exp` VARCHAR(256),
    `comments` VARCHAR(512)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT = 'Passe-Coque members';

CREATE TABLE IF NOT EXISTS PC_MEMBERS (
    ID INT AUTO_INCREMENT PRIMARY KEY, -- reference is NOT unique enough...
    REFERENCE INT,
    COMMAND_DATE TIMESTAMP,      -- Format: YYYY-MM-DD hh:mm:ss
    COMMAND_STATUS VARCHAR(32),
    MEMBER_FIRST_NAME VARCHAR(64),
    MEMBER_LAST_NAME VARCHAR(64),
    CARD_URL VARCHAR(512),
    PAYER_FIRST_NAME VARCHAR(64),
    PAYER_LAST_NAME VARCHAR(64),
    PAYER_EMAIL VARCHAR(64),
    COMPANY_NAME VARCHAR(64),
    PAID_WITH VARCHAR(32),
    FEE_CATEGORY VARCHAR(32),
    FEE_AMOUNT FLOAT,
    PROMO_CODE VARCHAR(16),
    PROMO_AMOUNT FLOAT,
    PHONE VARCHAR(16),
    EMAIL VARCHAR(64),
    ADDRESS VARCHAR(128),
    ZIP VARCHAR(8),
    BIRTH_DATE DATE,  -- format 'YYYY-MM-DD'
    CITY VARCHAR(32),
    SAILING_EXPERIENCE VARCHAR(256),
    BOAT_BUILDING_EXP VARCHAR(256),
    COMMENTS VARCHAR(512)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT = 'Passe-Coque members';

-- Exemple, from
-- 75163629;28/11/2023 21:43;Validé;Gambier;Sophie;https://www.helloasso.com/associations/passe-coque-l-association-des-passeurs-d-ecoute/adhesions/adhesion-simple/carte-adherent?cardId=75163629&ag=75163629;Gambier;Sophie;sophie.gambier01@gmail.com;;Carte bancaire;Passeur d'Ecoute;50,00;;;0769455969;sophie.gambier01@gmail.com;sophie.gambier01@gmail.com;44300;08/12/2001;Nantes;Equipière confirmée;aucune;

INSERT INTO `pc-members` (
    `reference`, 
    `command-date`,
    `member-first-name`, 
    `member-last-name`, 
    `email`, 
    `birth-date`,
    `sailing-experience`)     
VALUES (75163629, 
       '2023-11-28 21:43:00', 
       'Sophie', 
       'Gambier', 
       'sophie.gambier01@gmail.com', 
       '2011-12-08',
       'Equipière confirmée');

select `member-first-name`, `member-last-name`, `birth-date` from `pc-members`;

CREATE TABLE IF NOT EXISTS PC_NUMBERS (
    ID VARCHAR(32) PRIMARY KEY,
    AMOUNT INT DEFAULT 0,
    DESCRIPTION VARCHAR(128)
);
INSERT INTO PC_NUMBERS (ID, AMOUNT, DESCRIPTION) VALUES ('NB_VIEWS', 0, 'Number of views of the site');

CREATE TABLE IF NOT EXISTS PC_TRACKER (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    EVENT_DATE TIMESTAMP,
    CLIENT_IP VARCHAR(64),
    APP_CODE_NAME VARCHAR(64),
	BROWSER_NAME VARCHAR(64),
	PRODUCT VARCHAR(64),
	BROWSER_VERSION VARCHAR(64),
	BROWSER_LANGUAGE VARCHAR(64),
	PLATFORM VARCHAR(64),
	USER_AGENT VARCHAR(64),
	LATITUDE FLOAT,
    LONGITUDE FLOAT
);

SELECT COUNT(CLIENT_IP) AS NB_HIT, MIN(EVENT_DATE) AS SINCE, PLATFORM, BROWSER_LANGUAGE from PC_TRACKER GROUP BY CLIENT_IP ORDER BY 1 DESC;

-- 
-- For username and password, provate spaces, etc
--
CREATE TABLE IF NOT EXISTS PC_USERS (
    USERNAME VARCHAR(64) PRIMARY KEY,
    DISPLAY_NAME VARCHAR(64) COMMENT 'The name to display when greeting the user',
    PASSWORD VARCHAR(64),
    ADMIN_PRIVILEGES BOOLEAN DEFAULT FALSE,
    SOME_CONTENT VARCHAR(512) COMMENT 'Whatever you want goes here'
);

INSERT INTO PC_USERS (USERNAME, DISPLAY_NAME, PASSWORD, SOME_CONTENT) 
              VALUES ('olivier@lediouris.net', 'Oliv', sha1('c2h5oh'), 'Akeu Coucou!');
INSERT INTO PC_USERS (USERNAME, DISPLAY_NAME, PASSWORD, ADMIN_PRIVILEGES, SOME_CONTENT) 
              VALUES ('admin@passe-coque.com ', 'Administrator', sha1('manager'), TRUE, 'For tests, with Admin Privileges');

CREATE TABLE IF NOT EXISTS NL_SUBSCRIBERS (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    NAME varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'First and Last Name of the Subscriber',
    EMAIL varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Email of the Subscriber',
    SUBSCRIPTION_DATE TIMESTAMP,
    ACTIVE  boolean default TRUE,
    CONSTRAINT UK_email UNIQUE (EMAIL)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT = 'News Letter Subscribers';

-- Migration
BEGIN;
INSERT INTO NL_SUBSCRIBERS (NAME, EMAIL, SUBSCRIPTION_DATE, ACTIVE) 
SELECT `name`, `email`, CURRENT_TIMESTAMP(), `active` FROM `nl-subscribers`;
COMMIT;


-- March 9, 2024
CREATE TABLE IF NOT EXISTS PASSE_COQUE_MEMBERS (
    EMAIL VARCHAR(64) PRIMARY KEY,
    LAST_NAME VARCHAR(64) NOT NULL,
    FIRST_NAME VARCHAR(64) NOT NULL,
    TARIF VARCHAR(64),
    AMOUNT INT,
    TELEPHONE VARCHAR(32),
    FIRST_ENROLLED TIMESTAMP,
    NEWS_LETTER_OK BOOLEAN DEFAULT TRUE,
    PASSWORD VARCHAR(64),
    ADMIN_PRIVILEGES BOOLEAN DEFAULT FALSE,
    SOME_CONTENT VARCHAR(512) COMMENT 'Whatever you want goes here'
);

CREATE TABLE IF NOT EXISTS MEMBER_FEES (
    EMAIL VARCHAR(64),
    YEAR INT,
    CONSTRAINT FEES_PK PRIMARY KEY(EMAIL, YEAR),
    CONSTRAINT FEES_FK_MEMBERS FOREIGN KEY (EMAIL) REFERENCES PASSE_COQUE_MEMBERS (EMAIL) ON DELETE CASCADE
);

-- Complements. 
-- Subscribers not in members
SELECT * 
FROM NL_SUBSCRIBERS NL
WHERE TRIM(NL.EMAIL) NOT IN (SELECT TRIM(M.EMAIL) FROM PASSE_COQUE_MEMBERS M)
ORDER BY NL.SUBSCRIPTION_DATE DESC
LIMIT 0, 100;

-- Members not in subscribers
SELECT * 
FROM PASSE_COQUE_MEMBERS PCCM
WHERE TRIM(PCCM.EMAIL) NOT IN (SELECT TRIM(NL.EMAIL) FROM NL_SUBSCRIBERS NL)
LIMIT 0, 100;

-- Un-subscribed ones...
SELECT * 
FROM PASSE_COQUE_MEMBERS PCCM
WHERE TRIM(PCCM.EMAIL) IN (SELECT TRIM(NL.EMAIL) FROM NL_SUBSCRIBERS NL WHERE NL.ACTIVE = FALSE)
LIMIT 0, 100;

-- Members, and their fee years
SELECT PCM.FIRST_NAME, PCM.LAST_NAME, PCM.EMAIL, MF.YEAR 
FROM PASSE_COQUE_MEMBERS PCM, MEMBER_FEES MF 
WHERE MF.EMAIL = PCM.EMAIL
ORDER BY 3, 4;

SELECT PCM.FIRST_NAME, 
       PCM.LAST_NAME, 
       PCM.EMAIL, 
       COUNT(MF.YEAR) AS NB_FEES, 
       MAX(MF.YEAR) AS LAST_FEE
FROM PASSE_COQUE_MEMBERS PCM, MEMBER_FEES MF 
WHERE MF.EMAIL = PCM.EMAIL
GROUP BY PCM.EMAIL
ORDER BY 3;

-- Output to a file, from PHP
SELECT *
  INTO OUTFILE './tmp/result.csv'
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
 LINES TERMINATED BY '\n'
  FROM PC_USERS;

-- NL Targets  
SELECT EMAIL, CONCAT(FIRST_NAME, ' ', LAST_NAME) AS NAME, EMAIL, NEWS_LETTER_OK 
FROM PASSE_COQUE_MEMBERS 
WHERE EMAIL LIKE '%' AND (LAST_NAME LIKE '%Le%Diouris%'
                       OR LAST_NAME LIKE '%Allais%' 
                       OR FIRST_NAME LIKE '%Pierre-Jean%')
AND NEWS_LETTER_OK = TRUE;

CREATE VIEW MEMBERS_AND_FEES AS
SELECT PCM.FIRST_NAME, PCM.LAST_NAME, PCM.EMAIL, MF.YEAR 
FROM PASSE_COQUE_MEMBERS PCM, MEMBER_FEES MF 
WHERE MF.EMAIL = PCM.EMAIL
ORDER BY 3, 4;

SELECT * FROM MEMBERS_AND_FEES WHERE LAST_NAME LIKE 'pahun';

-- Check constraints... Not working.
CREATE TABLE IF NOT EXISTS TEST_CHECK (
  ID INT AUTO_INCREMENT PRIMARY KEY,
  WACKY_STUFF VARCHAR(64) DEFAULT NULL CHECK (WACKY_STUFF IS NULL OR WACKY_STUFF IN ('AKEU', 'COUCOU', 'LARIGOU'))
);

INSERT INTO TEST_CHECK (WACKY_STUFF) VALUES ('AKEU');
INSERT INTO TEST_CHECK (WACKY_STUFF) VALUES ('POUET');

SELECT * FROM TEST_CHECK;

-- See triggers in MySQL...

CREATE TABLE IF NOT EXISTS THE_FLEET (
    BOAT_NAME VARCHAR(64),      -- "Remora",
    ID VARCHAR(64) PRIMARY KEY, -- "remora",
    PIX_LOC VARCHAR(128),       -- "/images/boats/remora.sq.png",
    BOAT_TYPE VARCHAR(32),      -- "Arcachonnais",
    CATEGORY VARCHAR(16) DEFAULT 'NONE' CHECK (CATEGORY IN ('CLUB', 'TO_GRAB', 'EX_BOAT', 'NONE')),  -- "TO_GRAB",
    BASE VARCHAR(64)            -- "Saint&#8209;Philibert"
);
