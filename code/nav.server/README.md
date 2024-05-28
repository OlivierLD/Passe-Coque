# Minimal Nav Server
The goal here is to have a Raspberry Pi Zero W with a GPS connected to it, and a small eInk screen attached on top.  
It will get from the GPS:
- The position
- The speed
- The heading

In addition, it will emit its own network, so other devices (laptops, tablets, cell-phones) can connect to it.  
In will also act as an HTTP server, also serving web pages and REST requests.  
A TCP forwarder will also be avaialble, so the NMEA data can be used from other programs running on other devices (like OpenCPN, SeaWi, etc).

## What you will need
- A Raspberry Pi Zero W
    - like [this](https://www.raspberrypi.com/products/raspberry-pi-zero-2-w/), and make sure you have a header, to plug the screen in.
- A 16 Gb Micro SD card
    - Like [this](https://www.amazon.com/s?k=16gb+sd+card+micro&crid=2R3TGBEH1JZUR&sprefix=16gb+sd+card%2Caps%2C162&ref=nb_sb_ss_ts-doa-p_2_12)
- A 2.13 eInk screen
    - Like [this one](https://learn.adafruit.com/2-13-in-e-ink-bonnet)
- A GPS dongle
    - Like [this](https://www.amazon.com/HiLetgo-G-Mouse-GLONASS-Receiver-Windows/dp/B01MTU9KTF/ref=sr_1_2_sspa?crid=GUP2CACZ6V0I&dib=eyJ2IjoiMSJ9.CwfXI6_E0L91sy8oYTH4yJPSt_RGt3UzZ5z3ifDqQpQmFAUD_zFQAGUJyFfaKXYPfnR4Tkt54eBl2wIVbeSofaDPau1zsm2YIhUTV2FaGE1I8KunRQzoB2Y6m5QzNzaUrG1NXqtdvHIaImSER5XN_B2JuKnrEsgUK9ulHY-OcL2gZc6FdWjVLtsGGg5_0RqH7gSGgBtQeSxz2Nt81BF6Q9zM4rbD9x6YI6x8XcYJGiM.JDIq8BTMU9zUQKa3hY9dEkyzJMOLuFd0wArYS0ywWDo&dib_tag=se&keywords=gps+dongle+usb&qid=1716882582&sprefix=GPS+Dongle%2Caps%2C142&sr=8-2-spons&sp_csd=d2lkZ2V0TmFtZT1zcF9hdGY&psc=1), or [this](https://www.amazon.com/VK-162-G-Mouse-External-Navigation-Raspberry/dp/B01EROIUEW/ref=sr_1_5?crid=GUP2CACZ6V0I&dib=eyJ2IjoiMSJ9.CwfXI6_E0L91sy8oYTH4yJPSt_RGt3UzZ5z3ifDqQpQmFAUD_zFQAGUJyFfaKXYPfnR4Tkt54eBl2wIVbeSofaDPau1zsm2YIhUTV2FaGE1I8KunRQzoB2Y6m5QzNzaUrG1NXqtdvHIaImSER5XN_B2JuKnrEsgUK9ulHY-OcL2gZc6FdWjVLtsGGg5_0RqH7gSGgBtQeSxz2Nt81BF6Q9zM4rbD9x6YI6x8XcYJGiM.JDIq8BTMU9zUQKa3hY9dEkyzJMOLuFd0wArYS0ywWDo&dib_tag=se&keywords=gps+dongle+usb&qid=1716882582&sprefix=GPS+Dongle%2Caps%2C142&sr=8-5).
- An alimentation cable
    - Like [this one](https://www.amazon.com/Amazon-Basics-Charging-Transfer-Gold-Plated/dp/B07232M876/ref=sr_1_3?crid=13F56Y2EVG6LU&dib=eyJ2IjoiMSJ9.EGqyR87iLe4DQeHcmZ37j2nGqrLMje4cl0jbCPAssgOJrifcZ2DA_Q7xiXmL9zzDvl2VcECnXQvdbDrLdHeUzU0hDrC_MQXUgmE4tVa0Z92gKTBN8pOdJDe39bNJ6gCtJYN7xotNR4uSfKVyE7iqyR2Op8I6Zbl0rHUdbFq-rqDD4dhEcRVrB8CBhtAl3ePzU8M9rcVyO18LMxwg3hCLQcFDx3_5ZRoBO5hkoLqFCW8.yWdG0yZ17k7H-Uc4nUkfoSgkIWihw3xrsQxTW0wt-kk&dib_tag=se&keywords=USB%2Bcable&qid=1716882969&sprefix=usb%2Bcable%2Caps%2C144&sr=8-3&th=1). The Raspberry Pi needs a Micro USB socket. The other end depends on what you will plug it in (a cig-lighter is usually USB-A).
- Depending on the GPS dongle you choose, you may need a USB adapter to plug it in the Raspberry Pi Zero USB socket
    - Like [this](https://www.amazon.com/s?k=USB+adapter+micro+to+USB-A&crid=24H0FMF2BMKDA&sprefix=usb+adapter+micro+to+usb-a%2Caps%2C150&ref=nb_sb_noss_2).

## For the impatient
- Flash the SD card with a [provided image](). Use [Etcher](https://etcher.balena.io/) for this, it's free and it works just great.
- Put the SD card in the Raspberry Pi
- Plug the eInk screen on top
- Connect the GPS
- Connect the power supply
- You're done!

## More in details
See [here](https://github.com/OlivierLD/ROB/blob/master/raspberry-sailor/MUX-implementations/NMEA-multiplexer-basic/HOWTO.md).

## Options
3D printed enclosure, described through the link above.



