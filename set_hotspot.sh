#!/bin/bash

cd /home/pi/hotspot

echo "Getting hotspot status value"

#status=`cat hotspot_status`
status=`head -n 1 status_hotspot`

echo "Hotspot_status: $status"

ip=$(hostname -I | cut -d' ' -f1 )


if [ "$ip" = "192.168.1.1" ] && [ "$status" = "off" ]; then
   echo "Hotspot is active and should not be, disabling"
   /bin/bash ./disable.sh
   exit 1
fi

if [ "$ip" != "192.168.1.1" ] && [ "$status" = "on" ]; then
   echo "Hotspot is inactive and should be active, switching on"
   /bin/bash ./enable.sh
   exit 1
fi

echo "If you can read this the hotspot is '$status' and is meant to be"






