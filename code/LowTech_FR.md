# Navigation Low Tech
[En anglais](./LowTech.md)

## Pr&eacute;ambule

Le monde a &eacute;t&eacute; d&eacute;couvert en bateau. Et &agrave; l'&eacute;poque, l'&eacute;lectricit&eacute; n'&eacute;tait en g&eacute;n&eacute;ral - voire uniquement - disponible que pendant les orages.  
Les navigateurs (comme Colomb, Cook, et les autres) utilisaient des techniques de navigation **no-tech**.  
Ces techniques fonctionnent toujours aujourd'hui, et m&eacute;riteraient s&ucirc;rement de ne pas &ecirc;tre oubli&eacute;es. Mais cette section illustre autre chose, il s'agit ici d'utiliser des technologies modernes, &agrave; partir de petits instruments bon march&eacute;.  
Il est n&eacute;cessaire de ma&icirc;triser ces technologies pour les mettre en &oelig;uvre.  
C'est bien l&agrave; toute la diff&eacute;rence entre un outil et une bo&icirc;te noire. La bo&icirc;te noire d&eacute;cide toute seule, et quand elle d&eacute;cide de se mettre en botte, vous l'avez dans l'os. L'outil fait ce qu'on lui dit de faire. Si &ccedil;a ne marche pas, c'est probablement parce qu'on s'en sert mal. Et on doit pouvoir arranger &ccedil;a.

Pour la navigation, on a besoin de :
- conna&icirc;tre notre position
    - et &eacute;ventuellement de la porter sur une carte
- savoir o&ugrave; on va
- savoir comment est le vent
- savoir ce que font les autres bateaux
- etc...

Pour la position, des techniques comme le GPS peuvent &ecirc;tre une solution.  
Des compas &eacute;lectroniques existent, &ccedil;a peut nous dire o&ugrave; on va.  
Pour la vitesse du bateau, la vitesse et la direction du vent, il existe des &eacute;quipements &eacute;lectroniques.  
Les donn&eacute;es &eacute;mises par ces instruments sont en g&eacute;n&eacute;ral en conformit&eacute; avec un des plus vieux standards informatiques, appel&eacute; NMEA (National Marine Electronics Association).

C'est cette technologie qui est utilis&eacute;e dans les Trackers et Chart Plotters du march&eacute; ; mais voyons si on peut envisager d'autres solutions.

## Possibilit&eacute;s 
Pour rassembler et utiliser les donn&eacute;es &eacute;mises par les &eacute;quipements &eacute;lectroniques, on va avoir besoin d'une sorte d'ordinateur, et de comp&eacute;tences en programmation.

### Ordinateurs
Dans ce domaine, le vainqueur &eacute;vident pourrait &ecirc;tre le Raspberry Pi.  
C'est un petit ordinateur single-board, construit et con&ccedil;u en Angleterre, par Eben Upton ; il fonctionne sous Linux, et il est plus puissant que l'ordinateur que j'avais sur mon bureau il y a 40 ans... En plus, sa consommation en &eacute;nergie est ridicule. Il en existe plusieurs mod&egrave;les, le plus petit convient (Raspberry Pi Zero W - `W` c'est pour wireless), &ccedil;a co&ucirc;te moins de 20 Euros.  

Alternatives au Raspberry Pi : <https://pallavaggarwal.in/raspberry-pi-alternatives-clones/>

### Programmation
On peut utiliser plusieurs langages de programmation. C, Java, Python, ... Encore une fois, comme le Raspberry Pi tourne sous Linux, tous les langages qui y sont impl&eacute;ment&eacute;s peuvent faire l'affaire.  
La communication entre composant &eacute;crits dans les langages diff&eacute;rents peut poser un probl&egrave;me..., r&eacute;solu en utilisant des protocoles comme TCP.  
Beaucoup de fournisseurs de composants (comme BMP180, BME180, etc) donnent aussi des exemples de code &eacute;crits Python.

On utilisera principalement Java et Python. Mais &ccedil;a n'emp&ecirc;che pas d'autres langages de rejoindre la sc&egrave;ne.


. . .
