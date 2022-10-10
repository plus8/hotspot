

just done a fresh install of (bullseye) with desktop

Before you start connect the pi to whatever your home wifi is if that's available. It will revert to these
settings automatically. You can also put several entries for wifis with varying priorities in your
wpa_supplicant file. e.g:

  ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
  update_config=1
  country=GB

  network={
          ssid="MOTOG5S"
          scan_ssid=1
          psk="SOMEKEY"
          key_mgmt=WPA-PSK
          priority=100
  }

  network={
          ssid="UNIWAF40"
          scan_ssid=1
          psk="SOMEKEY"
          key_mgmt=WPA-PSK
          priority=80
  }

  network={
          ssid="VANFI"
          scan_ssid=1
          psk="SOMEKEY"
          key_mgmt=WPA-PSK
          priority=60
  }

The priority affects which one it will connect to so I start with the sketchiest (my mobile phone), then second 
sketchiest (rugged phone with hotspot) and then the van's internal 3g router.

We'll be relying on these settings later so set them up in advance. If you only have one wifi in play just auth with 
it and you should be good to go. You can check your /etc/wpa_supplicant/wpa_supplicant file to see what's stored
in there and tweak it if needed.


--------------------------------



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


The Networking stuff:
-------------------------------------

Even though most of the networking stuff is no longer done using /etc/network/interfaces, for the purposes of what we're 
doing here with hostAPD it seems to work fine. There's probably a better way (e.g. Network Manager) but I've not figured that out yet, so this is how it is for now.

The way this works is that it has an "org" copy of each config file (for when you're attached to a wifi/lan as a client) and
an "ap" version which is configured for the device in access point mode. 

There's a script to enable the hotspot (run with sudo ./enable.sh) and one to disable it (sudo ./disable.sh)

When you enable, first it stops various services and the wifi interface, swaps the configs over, 
then restarts the network and unmasks the dnsmasq and hostapd services. They do the actual clever stuff re the wifi.
Disabling is just the same but reversed.

So first thing to do is look at your current /etc/network interfaces file which, on a fresh pi install, should look 
a bit like below:

  # interfaces(5) file used by ifup(8) and ifdown(8)
  # Include files from /etc/network/interfaces.d:
  source /etc/network/interfaces.d/*

and not much else. If that's the case then carry on, the below should work. If that's not the case you may have to 
freestyle a bit.

#If you *do* have other stuff in there which you need then you will need to copy those extra bits over into the 
#hotspot/interfaces.org file. That way when the hotspot is disabled your interface file will be restored to how it is now.

Create the "original" interfaces file based on your current config:

  cd ~/hotspot
  cp /etc/network/interfaces interfaces.org


If you look in the interfaces.ap file you should see the settings your AP will be using. I used 192.168.0 at home
so to avoid clashes I'm using 192.168.1 range for this. It should look a bit like this:

  allow-hotplug wlan0
  iface wlan0 inet static
  address 192.168.1.1
  netmask 255.255.255.0
  network 192.168.1.0



Next up the the /etc/dhcpcd.conf file. Same deal: copy your dhcpcd.conf file into the hotspot folder and rename it 
dhcpcd.conf.org

 cd ~/hotspot
 cp /etc/dhcpcd.conf dhcpcd.conf.org

They're your "backups"/original configs and won't be messed with by the script. They'll just be swapped out for other scripts
while the hotspot is active.

Now we need to make the dhcpcd.conf file which will be used by the AP, and all we need to do is copy the one you just created
to a different name, and add the following text at the end, so:

  cd ~/hotspot
  cp dhcpcd.conf.org dhcpcd.conf.ap
  nano dhcpcd.conf.ap

then right at the bottom add on it's own line:

  denyinterfaces wlan0

close and save with CTRL+X & y.




Next up let's have a look at the actual AP side of things. Starting with installing HostAPD and DNSmasq.

(based on this indestructable: https://www.instructables.com/Using-a-Raspberry-PI-Zero-W-As-an-Access-Point-and/ )

  sudo apt-get install dnsmasq hostapd

then stop both services:

  sudo systemctl stop dnsmasq
  sudo systemctl stop hostapd

These services clash with your normal networking and whilst we could start and stop them each time, that doesn't persist
beyond a reboot, so we mask them instead. That way if you set the hotspot to be in hotspot mode then reboot it, it'll
still be in hotspot mode when it wakes up. Same goes for wifi client mode.

The enable/disable script masks and unmasks the services as needed though so as long as you don't reboot you can just 
carry on as is with them stopped for now.

currently there's no hostapd config so let's have a look at the one from the hotspot folder:

  nano hostapd.conf


It should look a bit like this:

  # the interface used by the AP
  interface=wlan0
  # "g" simply means 2.4GHz band
  hw_mode=g
  # the channel to use
  channel=10
  # limit the frequencies used to those allowed in the country
  ieee80211d=1
  # the country code
  country_code=FR
  # 802.11n support
  ieee80211n=1
  # QoS support, also required for full speed on 802.11n/ac/ax
  wmm_enabled=1

  # the name of the AP
  ssid=VANDIESEL
  # 1=wpa, 2=wep, 3=both
  auth_algs=1
  # WPA2 only
  wpa=2
  wpa_key_mgmt=WPA-PSK
  rsn_pairwise=CCMP
  wpa_passphrase=YOURWIFIPASSWORD

You can change the ssid to be whatever you want yours to be, same goes for the password.

Once you're happy with the settings, save and close with CTRL+X & y.

Copy the config to the hostapd folder with:

  sudo cp hostapd.conf /etc/hostapd/

Then you may wish to reboot or if you're feeling brave just fire it up and see if it works:

  sudo ./enable.sh

It should do a bunch of stuff which takes about 30 sec but it can take a few minutes for the 
hotspot to come online.

You should see it appear in your wifi list and should be able to connect with your laptop or
another pi.

Once connected you can ssh into the machine with:

  ssh pi@192.168.1.1


And that ought to be that.

To disable the hotspot and revert to your previous settings, ssh into the machine and run the
disable script:

  cd ~/hotspot
  sudo ./disable.sh


It'll do some stuff and then should be back on whatever network it was previously configured for.





I'm writing this as i do the install on a clean machine. 


resuming tomorrow.. whilst also hopefully adding routing so it works on a pi2.





TODO:
=================================

- add a note about adding hostnames to the /etc/hosts file using the AP ip address so that when you log in the dns
  automatically resolves to whatever you call your machine. e.g. 
  192.168.1.1    vanpi3

- Change stats2.py to use the hostname instead of VANDIESEL2, or the SSID from hostAPD, so it doesn't need setting manually

- maybe look and see if Network Manager is a better way of doing the networking stuff.

- BUILD AN INSTALL SCRIPT WHICH DOES ALL THE CONFIG COPYING



NOTES:
=================================

- You could probably make it work with udhcpd too (like the RPIAdhocwifi uses) but here' we're using dnsmasq
which works great.



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


