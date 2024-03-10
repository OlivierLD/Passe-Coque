# 9 Mars 2024
Migration d'un fichier Excel vers la base de donn&eacute;es

Deux tables sont créées :

```sql
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
```
## An Admin user is added at the end
- With `password`, `admin`, `comments`.

Le traitement du fichier Excel g&eacute;n&egrave;re un script comme [celui-ci](./create.pc.members.v3.sql).  
L'en t&ecirc;te du ficher rel&egrave;ve des doublons dans les adresses email.  
L'adresse email est la cl&eacute; primaire de la table `PASSE_COQUE_MEMBERS`.

---
