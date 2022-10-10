

just done a fresh install of (bullseye) with desktop

install this bit if you want the OLED_stats:

https://www.the-diy-life.com/add-an-oled-stats-display-to-raspberry-pi-os-bullseye/


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


