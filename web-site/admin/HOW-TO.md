# Doc pour le web d&eacute;veloppeur

Sans surprise, la racine du site est `index.html`.

Pour le moment (Dec 2023), il n'y a dans le code <u>AUCUNE</u> d&eacute;pendance vis-&agrave;-vis d'un framework (pas de WordPress, JQuery, ReactJS, Underscore, Knockout) externe.

Le site est une SPA (Single Page Application). &Ccedil;a permet de r&eacute;duire la taille des ressources de mani&egrave;re substancielle.

Autres fichiers reli&eacute;s &agrave; l'index&nbsp;:
- `passe-coque.css`
- `passe-coque.js`
- `passe-coque.menu.css`

Deux menus sont disponibles&nbsp;:
- Un menu hi&eacute;rarchique et d&eacute;roulant
- Une barre lat&eacute;rale de navigation, activ&eacute;e avec un hamburger (&#8801;)

L'un est destin&eacute; aux cell-phones (la barre lat&eacute;rale), l'autre aux autres p&eacute;riph&eacute;riques.  
<del>La distinction est faite dans un code en JavaScript, dans le `window.onload`, dans le fichier `index.html`,
en fonction des valeurs de `window.innerWidth` et `window.innerHeight`.</del>  
La distinction est faite dans `passe-coque.css`, en fonction de la largeur de l'&eacute;cran.

Les menus (les deux) sont compos&eacute;s de links, distingu&eacute;s par leurs `id`.   
Les actions &agrave; prendre sur un click sont prises par la fonction `clack`, en fonction des `ids` et de la langue courante (FR ou EN).
La fonction `clack`, d&eacute;finie elle aussi dans `passe-coque.js`.  

Si l'`id` a une valeur de `_21` et la langue est `FR`, alors le contenu d'un fichier `21_FR.html` est lu 
dynamiquement, et affich&eacute; dans la `<div id="current-content">`.

**_Warning!:_ des exceptions &agrave; cette r&egrave;gle existent. Voir le code de la fonction `clack`, dans le fichier `passe-coque.js`.**

Chargement du site&nbsp;:  
Voir dans `index.html`, le code (javascript) du `window.onload` y charge (dynamiquement) `1_FR.html`.  
On peut changer la langue par defaut au chargement avec un QS param `lang`. La langue par defaut est FR. On peut charger:  
`http://passe-coque.com/?lang=EN`.

Les images du haut de la home page ont un rapport de `3.5:1`. Au format `jpg`, une dimension de `1500 x 429` semble &ecirc;tre la bonne.

La traduction (des menus) est assurr&eacute;e par la fonction `updateMenu`, d&eacute;finie dans `passe-coque.js`.   

**Tout le site** se comporte comme une SPA (Single Page Application). La page `index.html` reste toutjours affich&eacute;e, le contenu 
de la `div` "`current-content`" est chang&eacute; dynamiquement en fonction des choix de l'utilisateur. Encore une fois,
voir &agrave; ce sujet le code de la fonction `clack`, dans le fichier `passe-coque.js`. Idem pour le Boat-Club, la fonction s'appelle `clack_pcc`.

Les photos des membres du bureau ont un rapport de 1:1 (genre 400 x 400).

**La flotte**&nbsp;: La liste des bateaux est un JSON Object `THE_FLEET` dans `passe-coque.js`.  
Les fonctions `updateFilter` et `fillOutFleet` etablissent la liste des bateaux, en fonction de la valeur des filtres 
(radio-boutons).  
Les images de bateau ont une taille de 376 &times; 376, rapport 1:1, obviously.

**Les actu** sont g&eacute;r&eacute;es par le JSON Array `INFO_SECTION` dans `passe-coque.js`, et la fonction `fillOutActu`.  

> Traduction des actus.  
La version par d&eacute;faut est la version fran&ccedil;aise. La traduction - &eacute;ventuelle - est assur&eacute;e dynamiquement par la fonction `translate` (dans `passe-coque.js`).
C'est un peu compliqu&eacute;, mais &ccedil;a apporte plus de souplesse (quand il n'est pas n&eacute;cessaire de traduire, par exemple).

### Local servers
NodeJS: (&agrave; partir de `web-site`, l&agrave; o&ugrave; se trouvent les fichiers `server.js` et `package.json`)
```
$ npm start            
```
php:
```
$ php -S 127.0.0.1:8000            
```

### Mises &agrave; jour du site
`ovh.com`  
- FTP/SSH help: [https://help.ovhcloud.com/csm/fr-web-hosting-ftp-storage-connection?id=kb_article_view&sysparm_article=KB0052702](https://help.ovhcloud.com/csm/fr-web-hosting-ftp-storage-connection?id=kb_article_view&sysparm_article=KB0052702)  
- FTP and SFTP server: ftp.cluster030.hosting.ovh.net  
- FTP port: 21  
- SFTP port: 22  
- FTP link to cluster: ftp://passecc@ftp.cluster030.hosting.ovh.net:21/  
- Home directory path: /home/passecc  

Dans FileZilla:  
- Username: passecc  
- Password: PasseCoque123  
- Server: ftp.cluster030.hosting.ovh.net  
- Port 22 (ou 21)  

### php & mySQL
Les technologies utilis&eacute;es sont "impos&eacute;es" par l'h&eacute;bergeur, ce sont - sans surprise :
- HTML5 / CSS3 / ES6
- PHP (voir [php.net](https://www.php.net/))
- MySQL (voir [mysql.com](https://www.mysql.com/))

Voir dans le r&eacute;pertoire `admin/sql`, il y a des exemples fonctionnels.

### HelloAsso
- HelloAsso [https://www.helloasso.com/associations/passe-coque-l-association-des-passeurs-d-ecoute/administration/ma-page-publique](https://www.helloasso.com/associations/passe-coque-l-association-des-passeurs-d-ecoute/administration/ma-page-publique)
    - Passeur d'&eacute;coute
        - Mes adhesions
            - Administrer
                - Personnalisation
                    - Tarifs, etc, . . .

Idem pour "Mon Compte" > "Mon profil", "Mes crowdfunding" > "Modifier un montant", et le reste !

La suite arrive...

En cas de panne&nbsp;: Envoyez-moi un <a class="mail-box list-link" href="mailto:olivier@lediouris.net?subject=Heeeeelp!&body=Au secours, je suis perdu !" target="email">message</a> !

### DB Export, Backup
- Log into phpMyAdmin, [https://phpmyadmin.cluster030.hosting.ovh.net/index.php?route=/table/sql&db=passecc128](https://phpmyadmin.cluster030.hosting.ovh.net/index.php?route=/table/sql&db=passecc128)
- Select the source database on the left pane (passecc128).
- Click on the Export tab in the top center pane.
- Select Quick or Custom export method (Quick will do).
- Choose the format you'd like to save the file as from the dropdown menu (like SQL)
- Click the Go or Export button to continue.

### REST Services, returning JSON payloads
Look into the code, for the parts using a `Content-Type: application/json;`.  
Also see how they're invoked (from a JavaScript `fetch`).

### Development Env
- [Setup MySQL](https://www.prisma.io/dataguide/mysql/setting-up-a-local-mysql-database#setting-up-mysql-on-macos) locally.
- [php debugger in Visual Studio](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug)


---
