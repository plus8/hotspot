

To start off with I just did a fresh install of (bullseye) with desktop.

Before you start connect the pi to whatever your home wifi is if that's available. It will revert to these
settings automatically when the AP is disabled and they're easier to set up before. You can also put several 
entries for wifis with varying priorities in your wpa_supplicant file. e.g:

```
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
```

The priority affects which one it will connect to so I start with the sketchiest (my mobile phone), then second 
sketchiest (rugged phone with hotspot) and then the van's internal 3g router. The mobile phone options would only
be on in case of a problem with the internal router so if they exist it will try to connect to them in preference.

We'll be relying on these settings later so set them up in advance. If you only have one wifi in play just auth with 
it and you should be good to go. You can check your /etc/wpa_supplicant/wpa_supplicant file to see what's stored
in there and tweak it if needed.


--------------------------------



install this bit if you want the OLED_stats:
===============================================


Follow this tutorial to get the OLED stuff working and then we'll tweak it to work how we want: 
https://www.the-diy-life.com/add-an-oled-stats-display-to-raspberry-pi-os-bullseye/

Clone/install the above OLED_stats project into your home folder ( i.e. ~/OLED_stats ) just like 
this hotspot folder ( i.e. ~/hotspot ). You can make it work in other folders too but
perhaps modify paths once you've got it working.

I guess you may want to enable the i2c bus using raspi-config too - though it *should* be enabled 
automatically later on:

```
sudo raspi-config
```

Then in the 'interface options' bit enable the i2c interface [NOT NEEDED, DONE BY BLINKA INSTALL]
You probably want to enable the ssh interface too if you haven't already. Most of this setup can be done via an ssh window.

So once you've got the oled_stats example working, carry on..

clone this repo into your home folder:

```
cd ~
git clone [this rpo url]
```

copy stats2.py and dostats.sh from the hotspot folder to the OLED_stats folder:

```
cd ~
cd hotspot
cp stats2.py ../OLED_Stats/
cp dostats.sh ../OLED_Stats/
cd ../OLED_Stats/
```

and test it:

```
python3 stats2.py
```

some stuff should come up on the OLED screen if it all worked \o/.

If you don't have a wifi configured there will probably be some missing data or it'll say it's connected to VANDIESEL wifi,
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

```
# interfaces(5) file used by ifup(8) and ifdown(8)
# Include files from /etc/network/interfaces.d:
source /etc/network/interfaces.d/*
```

and not much else. If that's the case then carry on, the below should work. If that's not the case you may have to 
freestyle a bit.

Create the "original" interfaces file based on your current config:

```
cd ~/hotspot
cp /etc/network/interfaces interfaces.org
```

If you look in the interfaces.ap file you should see the settings your AP will be using. I used 192.168.0 at home
so to avoid clashes I'm using 192.168.1 range for this. It should look a bit like this:

```
allow-hotplug wlan0
iface wlan0 inet static
address 192.168.1.1
netmask 255.255.255.0
network 192.168.1.0
```


Next up the the /etc/dhcpcd.conf file. Same deal: copy your dhcpcd.conf file into the hotspot folder and rename it 
dhcpcd.conf.org

```
cd ~/hotspot
cp /etc/dhcpcd.conf dhcpcd.conf.org
```

They're your "backups"/original configs and won't be messed with by the script. They'll just be swapped out for other scripts
while the hotspot is active.

Now we need to make the dhcpcd.conf file which will be used by the AP, and all we need to do is copy the one you just created
to a different name, and add the following text at the end, so:

```
cd ~/hotspot
cp dhcpcd.conf.org dhcpcd.conf.ap
nano dhcpcd.conf.ap
```

then right at the bottom add on it's own line:

```
denyinterfaces wlan0
```
close and save with CTRL+X & y.




Next up let's have a look at the actual AP side of things. Starting with installing HostAPD and DNSmasq.

(based on this indestructable: https://www.instructables.com/Using-a-Raspberry-PI-Zero-W-As-an-Access-Point-and/ )

```
sudo apt-get install dnsmasq hostapd
```

then stop both services:

```
sudo systemctl stop dnsmasq
sudo systemctl stop hostapd
```

These services clash with your normal networking and whilst we could start and stop them each time, that doesn't persist
beyond a reboot, so we mask them instead. That way if you set the hotspot to be in hotspot mode then reboot it, it'll
still be in hotspot mode when it wakes up. Same goes for wifi client mode.

The enable/disable script masks and unmasks the services as needed though so as long as you don't reboot you can just 
carry on as is with them stopped for now.

currently there's no hostapd config so let's have a look at the one from the hotspot folder:

```
nano hostapd.conf
```

It should look a bit like this:

```
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
```

You can change the ssid to be whatever you want yours to be, same goes for the password.

Once you're happy with the settings, save and close with CTRL+X & y.

Copy the config to the hostapd folder with:

```
cd ~/hotspot
sudo cp hostapd.conf /etc/hostapd/
```

Now to configure dnsmasq:

```
cd ~/hotspot
sudo cp /etc/dnsmasq.conf /etc/dnsmasq.conf.org
sudo cp dnsmasq.conf /etc/
```

If you look in the dnsmasq file

```
nano dnsmasq.conf
```

you'll see something like the following:

```
interface=wlan0
dhcp-range=192.168.1.2,192.168.1.50,255.255.255.0,24h
```

This handles the DHCP side of things and since we've defined the AP as 192.168.1.1, this
assigns addresses from 192.168.1.2 upwards. There's lots of interesting settings in the dnsmasq.conf.org 
file so have a look.

Dnsmasq also reads from your /etc/hosts file by default, however if you want your AP to be directly 
addressable like that you will need to add an entry to yours hosts file which uses the 192.168.1.1 ip address,
NOT just 127.0.0.1. e.g:

```
127.0.0.1       localhost
::1             localhost ip6-localhost ip6-loopback
ff02::1         ip6-allnodes
ff02::2         ip6-allrouters

#127.0.0.1	vanpi
192.168.1.1     vanpi
192.168.1.1     VANPI2
192.168.1.1     hotspot
```

the top few lines are auto generated but the last 3 mean that if you join the AP's wifi network it's DNS
will automatically resolve "hotspot", "vanpi" or whatever to the ip address of 192.168.1.1.
So that means you can join the wifi and ping or ssh pi@hotspot and it will resolve magically. Or if you 
set up the web-management console for this you would just join the wifi and enter http://hotspot into your 
browser and it would bring up the control interface, no IP addresses. This is thanks to dnsmasq reading 
directly from the hosts file, so you can add any arbitrary entries you like there e.g. wifi cam or 
whatever (as long as they're on static IPs).

You can edit your hosts file with:

```
sudo nano /etc/hosts
```

And add the relevant lines for the hostname/s you want to use. Save and exit with CTRL+X & y



Notice there's a commented-out entry for the hostname (vanpi) under 127.0.0.1, if that's there on yours you need to comment 
that out with a # and add an entry below for the same hostname on 192.168.1.1 if you want it to respond to that
remotely. You can test this by joining the hotspot from a laptop and then try pinging the names:

*You may need to reboot after hosts changes if you've already logged in remotely. 

but then:

```
user@host:~$ ping vanpi
PING vanpi (192.168.1.1) 56(84) bytes of data.
64 bytes from vanpi (192.168.1.1): icmp_seq=1 ttl=64 time=0.601 ms
64 bytes from vanpi (192.168.1.1): icmp_seq=2 ttl=64 time=11.1 ms
64 bytes from vanpi (192.168.1.1): icmp_seq=3 ttl=64 time=2.22 ms
^C
--- vanpi ping statistics ---
3 packets transmitted, 3 received, 0% packet loss, time 2002ms
rtt min/avg/max/mdev = 0.601/4.660/11.159/4.643 ms
user@host:~$ ssh pi@vanpi
The authenticity of host 'vanpi (192.168.1.1)' can't be established.
ECDSA key fingerprint is SHA256:0um9ojblahblahblabhz2A9y+0LNR2i3bhamB64.
Are you sure you want to continue connecting (yes/no)? yes
Warning: Permanently added 'vanpi' (ECDSA) to the list of known hosts.
pi@vanpi's password: 
Linux vanpi 5.15.61-v7+ #1579 SMP Fri Aug 26 11:10:59 BST 2022 armv7l

The programs included with the Debian GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
Last login: Mon Oct 10 22:05:48 2022
pi@vanpi:~ $ 

```

[hacker voice] "I'm in" :)

If you did good it'll look like above. If you did bad you'll get a reply but it will
be to 127.0.0.1 like below:

```
PING vanpi (127.0.1.1) 56(84) bytes of data.
64 bytes from yourlaptop (127.0.1.1): icmp_seq=1 ttl=64 time=0.017 ms
64 bytes from yourlaptop (127.0.1.1): icmp_seq=2 ttl=64 time=0.026 ms
64 bytes from yourlaptop (127.0.1.1): icmp_seq=3 ttl=64 time=0.036 ms
^C
--- vanpi ping statistics ---
3 packets transmitted, 3 received, 0% packet loss, time 2030ms
rtt min/avg/max/mdev = 0.017/0.026/0.036/0.008 ms$
```

the top few lines are auto generated but the last 3 mean that if you join the AP's wifi network it's DNS
will automatically resolve "hotspot", "VANPI2" or whatever to the ip address of 192.168.1.1.
So that means you can join the wifi and ssh pi@hotspot and it will resolve magically. Or if you set up 
the web-management console for this you would just join the wifi and enter http://hotspot into your browser
and it would bring up the control interface, no IP addresses. This is thanks to dnsmasq reading directly
from the hosts file, so you can add any arbitrary entries you like there e.g. wifi cam or whatever
(as long as they're on static IPs).

You can edit your hosts file with:

```
sudo nano /etc/hosts
```

And add the relevant lines for the hostname/s you want to use. Save and exit with CTRL+X & y


Then we *should* be all set. Let's see if it works:

```
sudo ./enable.sh
```

It should do a bunch of stuff which takes about 30 sec but it can take a few minutes for the 
hotspot to come online.

You should see it appear in your wifi list and should be able to connect with your laptop or
another pi.

Once connected you can ssh into the machine with:

```
ssh pi@192.168.1.1
```

And that ought to be that.

To disable the hotspot and revert to your previous settings, ssh into the machine and run the
disable script:

```
cd ~/hotspot
sudo ./disable.sh
```

It'll do some stuff and then should be back on whatever network it was previously configured for.


Making the OLED stuff work:
----------------------------------

Ok but we want the helpful OLED display too, right? so let's open up the stats2.py we copied into the
OLED_stats folder earlier:

```
cd ~
cd OLED_Stats/
nano stats2.py
```

and somewhere near the bottom there should be an entry like this:

```
    except:
      ssid = 'VANDIESEL'
```

Change that to be whatever you set your AP's SSID to. It's a manual fallback for the script when it
can't figure out what SSID it's on. It can say whatever you likebut it makes sense to show the SSID
from the hostAPD settings. This will hopefully be automated in future but I suck at python.

Save and exit with CTRL+X & y

then test it with:

```
python3 stats2.py
```

The screen should light up with the current wifi and ip address.

do CTRL+C to exit the oled script. Notice how it needed ending? That's gonanbe relevant in a moment.


Ok so let's make the dostats.sh script executable and test it:

(still in the OLD_Stats folder)
```
chmod +x dostats.sh
./dostats.sh
```

the screen should go blank momentarily and then and it should show the info.


If that worked let's make sure the screen keeps updating automatically by adding an entry, to cron:

```
sudo nano /etc/crontab
```

then at the bottom before the last # add in this line:

```
*/5 *     * * *   pi    /home/pi/OLED_Stats/dostats.sh &
```

so that means every 5 mins, run the dostats.sh script we copied over earlier under the pi user.

If you find te cron bit doesn't work - try copy and pasting the actual command part (i.e.
 "/home/pi/OLED_Stats/dostats.sh" into the console from your home folder, if it breaks you should
see why.

Ok so let's make the dostats.sh script executable and test it:






Making it more useful
===================================

Ok so whilst this is fun it's not super convenient unless you always have a linux/ssh-able laptop to hand.

So what I did is installed a minimal webserver and made a really simple control page for it. You don't have
to use it but it means I can control the hotspot, mount and unmount the attached SSD and also reboot the
server from a very convenient phone interface.

We just install the base apache and php and then set up a symbolic link to the www folder of ~/hotspot

We enable write permission for a text file in the hotspot folder which can be set from on/off, and then
we have a cron entry which runs a script which checks that textfile, and if it says "on" then it bringt he
hotspot up (if not active) and if the textfile says "off" then it switches the hotspot off (if active).

that might have to be for tomorrow though.. (today: 10/10/22)




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





Troubleshooting:
=================================
- if you can see the AP's SSID there but can't connect to it at all it's probably to do with hostAPD

- if you can see the AP's SSID there and can connect to it but can't stay connected that's likely to do with
  dnsmasq. Try connecting from a phone and if you get "ip conection failure" or it times out while "trying to
obtain ip address" then it's to do with DHCP and assigning IP addresses should be in /etc/dnsmasq.conf

- if you can connect to the wifi but can't connect to the hostname (e.g. pi@vanpi ) you should still be able
  to connect using the ip address e.g. ssh pi@192.168.1.1





Tips to speed up your PI, esp a Zero:
=========================================
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







Other cron stuff for later
============================================

* *     * * *   root    /home/pi/hotspot/set_disk.sh

* *     * * *   root    /home/pi/hotspot/set_hotspot.sh

*/2 *     * * *   root    /home/pi/hotspot/set_shutdown.sh

*/5 *     * * *   root    /home/pi/OLED_Stats/dostats.sh &


