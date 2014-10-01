#!/bin/bash

### BEGIN INIT INFO
# Provides:          boot_laranja
# Required-Start:    $remote_fs $syslog
# Required-Stop:     $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Inicializa e verifica se os modulos estao funcionando
# Description:       n/a
### END INIT INFO

PATH_ARQUIVOS='/root/xpn'
TEMP_DORMENCIA=3

cd /lib/firmware/
echo ttyO1_armhf.com > /sys/devices/bone_capemgr*/slots
echo ttyO2_armhf.com > /sys/devices/bone_capemgr*/slots
echo ttyO4_armhf.com > /sys/devices/bone_capemgr*/slots
echo ttyO5_armhf.com > /sys/devices/bone_capemgr*/slots
cd /dev
ln -s ttyO2 ttyUSB5

cd $PATH_ARQUIVOS
./m3 &
sleep 5
./m2 localhost &
./m1 localhost &

while [ 1 ]
do
	DATA=$(date)

	MOD1=$(pgrep m1)
	if [ $MOD1 > 1 ]; then
		echo "m1"
	else
		./m1 localhost &
		echo "Modulo 01 reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi

        MOD2=$(pgrep m2)
        if [ $MOD2 > 1 ]; then
                echo "m2"
        else
                ./m2 localhost &
                echo "Modulo 02 reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi

	sleep $TEMP_DORMENCIA
#	echo "loop" >> log_script.txt
done

