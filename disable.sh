#!/bin/bash

echo "Taking wifi down"
echo "If you are executing this script remotely"
echo "over ssh then this will be the last message"
echo "you'll see before the network disconnects you"

ifdown wlan0
sleep 1


echo "Stopping dnsmasq"
service dnsmasq stop
sleep 1

echo "Masking dnsmasq"
systemctl mask dnsmasq
sleep 1


echo "Stopping Hostapd"
service hostapd stop
sleep 1

echo "Masking hostapd"
systemctl mask hostapd
sleep 1


echo "Copying configs for network/interfaces and /etc/dhcpcd.conf"
cp /home/pi/hotspot/interfaces.org /etc/network/interfaces
cp /home/pi/hotspot/dhcpcd.conf.org /etc/dhcpcd.conf
sleep 3


echo "Restarting dhcpcd service"
service dhcpcd restart
sleep 3

echo "Bringing wifi back up"
ifup wlan0
sleep 1

echo "All done"

#ifconfig wlan0 down
#sleep 1
#iwconfig wlan0 mode Auto

