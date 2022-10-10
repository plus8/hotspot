#!/bin/bash

echo "Taking wifi down"
echo "If you are executing this script remotely"
echo "over ssh then this will be the last message" 
echo "you'll see before the network disconnects you" 
ifdown wlan0
sleep 1


echo "Unmasking dnsmasq"
systemctl unmask dnsmasq
sleep 1

echo "Starting dnmasq"
service dnsmasq start
sleep 1


echo "Unmasking hostapd"
systemctl unmask hostapd
sleep 1

#echo "Starting hostapd"
#service hostapd start
#sleep 1


echo "Copying configs for network/interfaces and /etc/dhcpcd.conf"
cp /home/pi/hotspot/interfaces.ap /etc/network/interfaces
cp /home/pi/hotspot/dhcpcd.conf.ap /etc/dhcpcd.conf
sleep 3


echo "Restarting dhcpcd service"
service dhcpcd restart
sleep 3

echo "Bringing wifi back up"
ifup wlan0
sleep 3

echo "Starting hostapd"
service hostapd start
sleep 1

echo "All done"
