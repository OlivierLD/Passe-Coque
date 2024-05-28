# Minimal Nav Server
Le but est ici d'avoir un Raspberry Pi Zero W avec un GPS, et un petit &eacute;cran eInk connect&eacute; dessus.  
Du GPS, on obtiendra :
- La position
- La vitesse
- Le cap

En plus, le Raspberry Pi emmettra son propre r&eacute;seau, pour que d'autres appareils puissent s'y connecter (laptops, tablettes, cell-phones).  
On aura aussi un serveur HTTP, qui h&eacute;bergera des pages web, et servira des requ&ecirc;tes REST.  
On aura de surcro&icirc;t un TCP forwarder, qui permettra &agrave; d'autres programmes tournant sur d'autres appareils (comme OpenCPN, SeaWi, etc) d'acc&eacute;der aux donn&eacute;es NMEA.

## On aura besoin de
- Un Raspberry Pi Zero W
    - Comme [ceci](https://www.raspberrypi.com/products/raspberry-pi-zero-2-w/), et assurez vous d'avoir un header, pour y connecter le petit &eacute;cran.
- Une Micro SD card de 16 Gb 
    - Comme [ceci](https://www.amazon.com/s?k=16gb+sd+card+micro&crid=2R3TGBEH1JZUR&sprefix=16gb+sd+card%2Caps%2C162&ref=nb_sb_ss_ts-doa-p_2_12)
- Un 2.13 eInk screen
    - Comme [celui-l&agrave;](https://learn.adafruit.com/2-13-in-e-ink-bonnet)
- Un GPS dongle
    - Comme [celui-ci](https://www.amazon.com/HiLetgo-G-Mouse-GLONASS-Receiver-Windows/dp/B01MTU9KTF/ref=sr_1_2_sspa?crid=GUP2CACZ6V0I&dib=eyJ2IjoiMSJ9.CwfXI6_E0L91sy8oYTH4yJPSt_RGt3UzZ5z3ifDqQpQmFAUD_zFQAGUJyFfaKXYPfnR4Tkt54eBl2wIVbeSofaDPau1zsm2YIhUTV2FaGE1I8KunRQzoB2Y6m5QzNzaUrG1NXqtdvHIaImSER5XN_B2JuKnrEsgUK9ulHY-OcL2gZc6FdWjVLtsGGg5_0RqH7gSGgBtQeSxz2Nt81BF6Q9zM4rbD9x6YI6x8XcYJGiM.JDIq8BTMU9zUQKa3hY9dEkyzJMOLuFd0wArYS0ywWDo&dib_tag=se&keywords=gps+dongle+usb&qid=1716882582&sprefix=GPS+Dongle%2Caps%2C142&sr=8-2-spons&sp_csd=d2lkZ2V0TmFtZT1zcF9hdGY&psc=1), ou [celui-l&agrave;](https://www.amazon.com/VK-162-G-Mouse-External-Navigation-Raspberry/dp/B01EROIUEW/ref=sr_1_5?crid=GUP2CACZ6V0I&dib=eyJ2IjoiMSJ9.CwfXI6_E0L91sy8oYTH4yJPSt_RGt3UzZ5z3ifDqQpQmFAUD_zFQAGUJyFfaKXYPfnR4Tkt54eBl2wIVbeSofaDPau1zsm2YIhUTV2FaGE1I8KunRQzoB2Y6m5QzNzaUrG1NXqtdvHIaImSER5XN_B2JuKnrEsgUK9ulHY-OcL2gZc6FdWjVLtsGGg5_0RqH7gSGgBtQeSxz2Nt81BF6Q9zM4rbD9x6YI6x8XcYJGiM.JDIq8BTMU9zUQKa3hY9dEkyzJMOLuFd0wArYS0ywWDo&dib_tag=se&keywords=gps+dongle+usb&qid=1716882582&sprefix=GPS+Dongle%2Caps%2C142&sr=8-5).
- Un cable d'alimentation
    - Comme [celui-ci](https://www.amazon.com/Amazon-Basics-Charging-Transfer-Gold-Plated/dp/B07232M876/ref=sr_1_3?crid=13F56Y2EVG6LU&dib=eyJ2IjoiMSJ9.EGqyR87iLe4DQeHcmZ37j2nGqrLMje4cl0jbCPAssgOJrifcZ2DA_Q7xiXmL9zzDvl2VcECnXQvdbDrLdHeUzU0hDrC_MQXUgmE4tVa0Z92gKTBN8pOdJDe39bNJ6gCtJYN7xotNR4uSfKVyE7iqyR2Op8I6Zbl0rHUdbFq-rqDD4dhEcRVrB8CBhtAl3ePzU8M9rcVyO18LMxwg3hCLQcFDx3_5ZRoBO5hkoLqFCW8.yWdG0yZ17k7H-Uc4nUkfoSgkIWihw3xrsQxTW0wt-kk&dib_tag=se&keywords=USB%2Bcable&qid=1716882969&sprefix=usb%2Bcable%2Caps%2C144&sr=8-3&th=1). Le Raspberry Pi a besoin d'une prise de type Micro USB. L'autre extremit&eacute; d&eacute;pend de ce &agrave; quoi vous le connecterez (un allume-cigare est en general USB-A).
- En fonction du GPS dongle que vous avez choisi, il vous faudra peut-&ecirc;tre un adaptateur USB, pour le connecter &agrave; la prise USB du Raspberry Pi Zero.
    - Comme [ceci](https://www.amazon.com/s?k=USB+adapter+micro+to+USB-A&crid=24H0FMF2BMKDA&sprefix=usb+adapter+micro+to+usb-a%2Caps%2C150&ref=nb_sb_noss_2).

## Pour les impatients
- Flashez la carte SD avec [cette image](https://passe-coque.com/disk.images/raspi.sdG.img.gz). Utilisez [Etcher](https://etcher.balena.io/) pour &ccedil;a, c'est gratuit et &ccedil;a marche.
- Ins&eacute;rez la carte SD dans le Raspberry Pi
- Connectez l'&eacute;cran eInk au dessus
- Connectez le GPS
- Connectez l'alimentation
- Et c'est parti !

## Plus de d&eacute;tails
Voyez [ici](https://github.com/OlivierLD/ROB/blob/master/raspberry-sailor/MUX-implementations/NMEA-multiplexer-basic/HOWTO.md).

## Options
Une boite imprim&eacute;e en 3D, d&eacute;crite dans le lien ci-dessus.

