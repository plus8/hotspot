# hotspot
Wifi hotspot for Raspberry Pi Zero W

This is a personal server/hotspot – Designed to be used with a Pi Zero-W and an SSD to store TV and other files I need on the move. It’s got a 256gb SSD and can switch between being a hotspot (AP mode, *not* Ad-Hoc, so it works with Android devices too).

https://www.plus8.net/wp-content/uploads/2022/10/Plus8_raspberry_pi_zerow_hotspot_personal_server_1600.jpg

It only needs a decent USB power socket and you’re good to go. It has a little screen on the front too which tells you which wifi it’s connected to and what it’s IP address is.

It has Samba set up on it too so as well as ssh/sftp access you can also access it from windows devices as well as Apple and Android, Linux etc.

I built this because I needed something which would work in the van on “away missions”, but it’s worked so well that I use it all the time now.

The first version worked with Ad-Hoc mode and I was using it with an ipad (which supports ad-hoc devices very well). It was based on RPiAdHocWiFi but then I discovered that Android devices don’t support ad-hoc mode so I had to start over again. This time around it uses HostAPD and DNSMasq to achieve the same result but as an “Access point” instead. Ad-hoc mode didn’t seem to work with security either whcih i wasn’t super-happy about either, whereas as it is now it uses WPA2-PSK TKIP and seems to run fine. I’ve had no problems accessing it from any device.

It can switch modes (i.e. join your prescribed wifi/s or be a hotspot) without rebooting which is achieved by masking/unmasking the dnsmasq and hostapd services as well as swapping some configs around.

The screen on the front is one of the default cheapo 128 x 32 pixel OLED screens like this, and uses a modified version of OLED_stats to display the current wifi and ip address.

Please see the INSTALL.md file for installation info. It's a bit long but I haven't been able to wrap it up into a script yet.

