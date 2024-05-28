# Low Tech Navigation
[In French](./LowTech_FR.md)

## Preamble

The world has been discovered by boats. And at that time, electricity was mostly - if not only - available through thunderstorms.  
Navigators (like Columbus, Cook, and others) used at their time **no-tech** navigation.  
The techniques they were using still work today, and would certainlly deserve not to be forgotten. But this section is about something else, it is about using modern technologies, from small and cheap instruments.  
You need to master the technology in order too put it to work.  
And this is all the different between a tool and a black-box. The black box decides on its own, and when it decides to fail, you're screwed. The tool does what it is told to. If it does not work, it's probably because it's not been used as it should. And that can probably be fixed.

For navigation, we need to:
- know our position
    - possibly plot it on a chart
- know were we are going
- know what the wind looks like
- know what the boats around are doing
- etc...

For the position, techniques like GPS could be a solution.  
Electronic compasses exist, that will tell us where we're going.  
For the boat speed, wind speed and direction, electronic equipments are also available.  
The data emitted by those devices usually follow one of the oldest IT standards, named NMEA (National Marine Electronics Association).

This is what's used by Trackers and Chart Plotters available on the market; but let's see if we could come up with another solution.

## Possibilities
To gather and use all the data emitted by the electronic equipments, we would need some kind of computer, and some programming skills.

### Computers
In this area, the obvious winner could be the Raspberry Pi.  
It is a small single-board computer, built and designed in England, by Eben Upton; it runs on Linux, and it is more powerful than the computer I had on my desk 40 years back... On top of that, its energy consumption is below ridiculous. It come in several models, the smallest one can do the job (Raspberry Pi Zero W - `W` stands for wireless), it's less than 20 Euros.  

- Raspberry Pi web site at <https://www.raspberrypi.com/>  
- Raspberry Pi Alternatives: <https://pallavaggarwal.in/raspberry-pi-alternatives-clones/>

### Programming
Several programming languages can be used. C, Java, Python, ... Again, as the Raspberry Pi runs on Linux, whatever language supported on it will do the job.  
Communication between components written in different languages could be an issue..., solved by using protocols like TCP.  
Many vendors of electronic components (like BMP180, BME180, etc) usually provide code samples written in Python.

We will be mostly using Java and Python. But this does bot prevent other languages to join the show.

## Several Projects
- [Minimal Nav Server](./nav.server/README.md)
    - With a GPS, an eInk screen.
- . . .


. . .
