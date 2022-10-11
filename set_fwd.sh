#!/bin/bash

cd /home/pi/hotspot

echo "Getting IP Forwarding status value"

#status=`cat hotspot_status`
status=`head -n 1 status_fwd`

echo "status_fwd: $status"

#ip=$(hostname -I | cut -d' ' -f1 )

fwd=$(sudo iptables -t nat -v -L POSTROUTING -n --line-number | grep MASQUERADE)

echo "fwd result: $fwd"

if [ "$fwd" == "" ] && [ "$status" = "on" ]; then
#if [ "$fwd" == "" ]; then
   echo "IP forwarding is not active and should be, enabling"
   /bin/bash ./ip_fwd_enable.sh
   exit 1
fi

if [ "$fwd" != "" ] && [ "$status" = "off" ]; then
#if [ "$fwd" != "" ]; then
   echo "IP forwarding is active and should not be, disabling"
   /bin/bash ./ip_fwd_disable.sh
   exit 1
fi

echo "If you can read this the IP forwarding is '$status' and is meant to be"






