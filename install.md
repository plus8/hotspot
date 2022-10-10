

just done a fresh install of (bullseye) with desktop

install this bit if you want the OLED_stats:

https://www.the-diy-life.com/add-an-oled-stats-display-to-raspberry-pi-os-bullseye/

Clone/install the OLED_stats in your home folder ( i.e. ~/OLED_stats ) just like 
this hotspot folder ( i.e. ~/hotspot ). You can make it work in other folders too but
perhaps modify paths once you've got it working.

I guess you may want to enable the i2c bus using raspi-config too - idk if it's required but I did:

  sudo raspi-config

Then in the 'interface options' bit enable the i2c interface [NOT NEEDED, DONE BY BLINKA INSTALL]
You probably want to enable the ssh interface too if you haven't already. Most of this setup can be done via an ssh window.

So once you've got the oled_stats example working, carry on..

clone this repo into your home folder:
  cd ~
  git clone [this rpo url]

copy stats2.py from the hotspot folder to the OLED_stats folder:

  cd ~
  cd hotspot
  cp stats2.py ../OLED_Stats/
  cd ../OLED_Stats/
  python3 stats2.py

some stuff should come up on the OLED screen if it all worked. 

If you don't have a wifi configured there will probably be some missing data or it'll say it's connected to VANDISEL wifi,
 don't worry about that for now.






I'm writing this as i do the install on a clean machine. 


resuming tomorrow.. whilst also hopefully adding routing so it works on a pi2.





Tips to speed up your PI, esp a Zero:

Reduce number of desktops:
- start menu/preferences/Main menu Editor
- look in the "preferences" section and you should see a bunch of greyed out options with access to fun stuff.
	- the one we want here is openbox configuration manager (but I also enable the rest)
- open openbox config manager and go to the "desktops" section, set this to 1
	- multiple desktops are fun and lovely on a powerful machine but on a little old pi it's usually gonna struggle and affect responsiveness with more than one desktop

Overclock the machine:
- esp if somewhere it's not going to overheat there ought to be no issue with a little overclock.
  on my pi zero "personal file server" it seems to run at 800mhz just fine with normal temps.
- Look in config.txt - there's some commented out code "arm_freq" re overclocking, but the overclocking speeds (8-900mhz) only seem to apply to older models (pre Pi4),
 otherwise for a pi4, use a value of 2000 and over-volt a bit so for a pi4, at the bottom of config.txt put:

over_voltage=6
arm_freq=2000

My pi4 running at 2000mhz on a single desktop works surprisingly well as a daily machine and with an 8cm pc fan across the top of the open casing, and wired into the 5v supply, the Pi stays cool and the fan is barely audible from more than a few cm away.

Ever since electricty prices shot up we've been looking for ways to reduce power usage and the pi uses a fair bit less power than the PC (~10w vs ~80-100).


