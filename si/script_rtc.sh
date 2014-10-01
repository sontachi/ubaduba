#!/bin/bash

### BEGIN INIT INFO
# Provides:          start rtc
# Required-Start:    $remote_fs $syslog
# Required-Stop:     $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Inicializa e verifica se os modulos estao funcionando
# Description:       n/a
### END INIT INFO

echo ds1307 0x68 > /sys/class/i2c-adapter/i2c-1/new_device
hwclock -s -f /dev/rtc1
hwclock -w