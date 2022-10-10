#!/bin/bash

cd /home/pi/hotspot

echo "Getting shutdown status value"

#status=`cat hotspot_status`
status=`head -n 1 status_shutdown`

echo "Shutdown_status: $status"

ip=$(hostname -I | cut -d' ' -f1 )


if [ "$status" = "clear" ]; then
   echo "Clear - do nothing"
   #/bin/bash ./disable.sh
   exit 1
fi

if [ "$status" = "reboot" ]; then
   echo "Reboot the server"
   echo "clear" > status_shutdown
   sudo reboot
   exit 1
fi

if [ "$status" = "shutdown" ]; then
   echo "Shutdown the server"
   echo "clear" > status_shutdown
   #/bin/bash ./enable.sh
   sudo shutdown -h now
   exit 1
fi


echo "If you can read this the shutdown situation is '$status' and is probably meant to be"






