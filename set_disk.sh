#!/bin/bash

cd /home/pi/hotspot

echo "Getting disk status value"

#status=`cat hotspot_status`
status=`head -n 1 status_disk`

echo "Disk_status: $status"



ismount=$(mountpoint /media/VANDIESEL/)

echo $ismount

if [[ $ismount == *"is a mountpoint"* ]]; then
   echo "It's a mountpoint"
   if [ "$status" = "off" ]; then
      echo "- status is 'off' so attempting to unmount"
      sudo umount /dev/sda1
      exit 1
   fi
else
   echo "It's not a mountpoint"
   if [ "$status" = "on" ]; then
      echo "- status is 'on' so attempting to mount"
      sudo mount /dev/sda1 /media/VANDIESEL
      exit 1
   fi
fi


echo "If you're seeing this then the disk status is '$status' and $ismount"
 






#ip=$(hostname -I | cut -d' ' -f1 )

#if [ "$ip" = "192.168.1.1" ] && [ "$status" = "off" ]; then
#   echo "Hotspot is active, disabling"
#   /bin/bash ./disable.sh
#fi

#if [ "$ip" != "192.168.1.1" ] && [ "$status" = "on" ]; then
#   echo "Hotspot is inactive, switching on"
#   /bin/bash ./enable.sh
#fi







