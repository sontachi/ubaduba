#!/bin/bash

### BEGIN INIT INFO
# Provides:          boot_azul
# Required-Start:    $remote_fs $syslog
# Required-Stop:     $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Inicializa e verifica se os modulos estao funcionando
# Description:       n/a
### END INIT INFO

PATH_ARQUIVOS='/root/xpn'
TEMP_DORMENCIA=3

cd $PATH_ARQUIVOS

/etc/init.d/apache2 start

sleep 5

while [ 1 ]
do
	DATA=$(date)

	MOD1=$(pgrep ac_az_verde)
	if [ $MOD1 > 1 ]; then
		echo "ac_az_verde.php"
	else
		./sync_php/ac_az_verde.php &
		echo "ac_az_verde reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi

	MOD2=$(pgrep ac_la_az)
	if [ $MOD2 > 1 ]; then
		echo "ac_la_azul.php"
	else
		./sync_php/ac_la_az.php &
		echo "ac_la_azul reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi


	MOD3=$(pgrep azul_verde)
	if [ $MOD3 > 1 ]; then
		echo "azul_verde.php"
	else
		./sync_php/azul_verde.php &
		echo "azul_verde reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi


	MOD4=$(pgrep consolidado_tr)
	if [ $MOD4 > 1 ]; then
		echo "consolidado_tr_verde.php"
	else
		./sync_php/consolidado_tr_verde.php &
		echo "consolidado_tr_verde reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi

	MOD5=$(pgrep consolidado_ve)
	if [ $MOD5 > 1 ]; then
		echo "consolidado_verde.php"
	else
		./sync_php/consolidado_verde.php &
		echo "consolidado_verde reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi

	MOD6=$(pgrep la_azul)
	if [ $MOD6 > 1 ]; then
		echo "la_azul.php"
	else
		./sync_php/la_azul.php &
		echo "la_azul reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi

	MOD7=$(pgrep la_consolidado)
	if [ $MOD7 > 1 ]; then
		echo "la_consolidado.php"
	else
		./sync_php/la_consolidado.php &
		echo "la_consolidado reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi

	MOD8=$(pgrep verde_supe)
	if [ $MOD8 > 1 ]; then
		echo "verde_supe.php"
	else
		./sync_php/verde_supe.php &
		echo "verde_supe reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi

	MOD9=$(pgrep gerar_pdf)
	if [ $MOD9 > 1 ]; then
		echo "gerar_pdf.php"
	else
		./gera_pdf/gerar_pdf.php &
		echo "la_consolidado reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi

	MOD10=$(pgrep alerta_envio)
	if [ $MOD10 > 1 ]; then
		echo "alerta_envio.php"
	else
		./alertas/alerta_envio.php &
		echo "alerta_envio reiniciado em: $DATA" >> log_reinializacao_modulos.txt
	fi


	sleep $TEMP_DORMENCIA
#	echo "loop" >> log_script.txt
done

