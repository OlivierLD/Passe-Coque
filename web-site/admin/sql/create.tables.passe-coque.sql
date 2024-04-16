-- Doc at https://www.mysqltutorial.org/mysql-basics/mysql-auto_increment/
--        https://www.w3schools.com/php/php_mysql_create_table.asp           <- Good for mySQLi
--
-- Table structure for tables:
--    `nl-subscribers`, News Letter Subscribers
--    `pc-mebers`
-- For MySQL Data Types, see https://www.w3schools.com/mysql/mysql_datatypes.asp
--


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

-- See triggers in MySQL...

-- To populate, see insert.boats.sql
CREATE TABLE IF NOT EXISTS THE_FLEET (
    BOAT_NAME VARCHAR(64),      -- "Remora",
    ID VARCHAR(64) PRIMARY KEY, -- "remora",
    PIX_LOC VARCHAR(128),       -- "/images/boats/remora.sq.png",
    BOAT_TYPE VARCHAR(32),      -- "Arcachonnais",
    CATEGORY VARCHAR(16) DEFAULT 'NONE' CHECK (CATEGORY IN ('CLUB', 'TO_GRAB', 'EX_BOAT', 'NONE')),  -- "TO_GRAB",
    BASE VARCHAR(64)            -- "Saint&#8209;Philibert"
);

CREATE TABLE IF NOT EXISTS REFERENTS (
    EMAIL VARCHAR(64) NOT NULL,
    BOAT_ID VARCHAR(64) NOT NULL,
    TELEPHONE VARCHAR(32),
    CONSTRAINT REFERENTS_PK PRIMARY KEY(EMAIL, BOAT_ID),
    CONSTRAINT REFERENTS_FK_MEMBERS FOREIGN KEY (EMAIL) REFERENCES PASSE_COQUE_MEMBERS (EMAIL) ON DELETE CASCADE,
    CONSTRAINT REFERENTS_FK_BOATS FOREIGN KEY (BOAT_ID) REFERENCES THE_FLEET (ID) ON DELETE CASCADE
);

SELECT EMAIL, FIRST_NAME, LAST_NAME, TELEPHONE FROM PASSE_COQUE_MEMBERS WHERE UPPER(LAST_NAME) LIKE '%ALLAIS%';

CREATE VIEW BOATS_AND_REFERENTS AS
SELECT M.EMAIL, M.FIRST_NAME, M.LAST_NAME,
       B.BOAT_NAME, B.BOAT_TYPE,
       R.BOAT_ID, R.TELEPHONE
FROM PASSE_COQUE_MEMBERS M,
     THE_FLEET B,
     REFERENTS R
WHERE R.BOAT_ID = B.ID AND
      R.EMAIL = M.EMAIL;       
       
INSERT INTO REFERENTS VALUES ('benjamin.rasseneur@laposte.net', 'ar-mor-van', 'unknown');
-- INSERT INTO REFERENTS VALUES ('smenuet@evoceane.fr', 'babou', 'unknown'); -- Not found in members
INSERT INTO REFERENTS VALUES ('2l2telecnav@gmail.com', 'coevic-2', '+33 7 56 85 44 38');
INSERT INTO REFERENTS VALUES ('lepecheur.g@gmail.com', 'coraxy', 'unknown');
INSERT INTO REFERENTS VALUES ('jeff.allais@hotmail.fr', 'eh-tak', '+33 6 35 59 18 80');
INSERT INTO REFERENTS VALUES ('marmajour@gmail.com', 'gwenillig', 'unknown');
INSERT INTO REFERENTS VALUES ('isseo.asso@gmail.com', 'imagine', 'unknown');
INSERT INTO REFERENTS VALUES ('patrick.potiron1@orange.fr', 'jolly-jumper', 'unknown');
INSERT INTO REFERENTS VALUES ('garciaalain299@gmail.com', 'jolly-jumper', 'unknown');
INSERT INTO REFERENTS VALUES ('l.brochard.archi@gmail.com', 'heure-bleue', 'unknown');
INSERT INTO REFERENTS VALUES ('yann.legrand56@icloud.com', 'la.reveuse' , '+33 6 75 00 80 15');
INSERT INTO REFERENTS VALUES ('alain.hahusseau@sfr.fr', 'la.reveuse' , '+33 7 88 72 41 71');
INSERT INTO REFERENTS VALUES ('alain.hahusseau@sfr.fr', 'ma-enez' , '+33 7 88 72 41 71');
INSERT INTO REFERENTS VALUES ('germain.regis@orange.fr', 'manu-oviri', '+33 6 83 58 47 43');
INSERT INTO REFERENTS VALUES ('tiagocampostc@hotmail.com', 'melkart', 'unknown');
INSERT INTO REFERENTS VALUES ('bouan.g@gmail.com', 'melvan', '+33 6 34 33 44 71');
INSERT INTO REFERENTS VALUES ('andres.juan@wanadoo.fr', 'mirella', 'unknown');  -- Jean Larie ?
INSERT INTO REFERENTS VALUES ('marine.prevet@atelierkopal.fr', 'passpartout', 'unknown');
INSERT INTO REFERENTS VALUES ('contact@voyageenpatrimoine.fr', 'passpartout', 'unknown');
INSERT INTO REFERENTS VALUES ('421jimmypahun@gmail.com', 'pordin-nancq', '+33 6 14 11 20 09');
-- Referent Saigane ?
-- Referent Saudade ?
INSERT INTO REFERENTS VALUES ('mlemeni@yahoo.fr', 'stiren', '+33 7 77 60 78 89');
INSERT INTO REFERENTS VALUES ('morisseauguillaume@msn.com', 'taapuna', 'unknown');
INSERT INTO REFERENTS VALUES ('bmenuet@wanadoo.fr', 'tokad-2', 'unknown');
INSERT INTO REFERENTS VALUES ('litimat1@free.fr', 'trehudal', '+33 6 62 34 88 86');
-- INSERT INTO REFERENTS VALUES ('smenuet@evoceane.fr', 'tri-yann', 'unknown'); -- Not in members... email address.
-- Twist again ?
-- Wanita Too ?

SELECT * FROM BOATS_AND_REFERENTS ORDER BY BOAT_ID;

-- TODO BOAT_CLUB_MEMBERS
