#include <termios.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/signal.h>
#include <sys/types.h>
#include <mysql/mysql.h> //MySQL - Diretiva de compilação necessária -lmysqlclient (apt-get install libmysqlclient-dev)
#include <string.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/wait.h>
#include <netdb.h>
#include <signal.h>             /* for kill(), sigsuspend(), others */

#include <sys/ipc.h>            /* for all IPC function calls */
#include <sys/sem.h>            /* for semget(), semop(), semctl() */
#include <sys/shm.h>

//Bibliotecas para usar o objeto string
#include <cstdlib>
#include <iostream>


#define BAUDRATE B9600
#define FALSE 0
#define TRUE 1

#define TAM_PKG 52

/*
 * Portas para o TCP
 */
//Modulo 01
#define TCP_PORT_MOD1         4951
//Modulo 02
#define TCP_PORT_MOD2         4952
//Modulo 03
#define TCP_PORT_MOD3         4953

 //Numero de tentativas para alertas
 #define NUM_ENVIO_PACOTES 3
 #define NUM_ACIONAMENTO 10
 #define NUM_CONEXAO_SERIAL 10
 #define NUM_CONEXAO_MODULOS 10



//Quantidade máxima de acionamentos/sensores por Nó
 #define AC_MAX_POR_NO 10
 #define SEN_MAX_POR_NO 10

/*
 * Quantidade de clientes esperando resposta do pedido de conexão
 */
#define BACKLOG 10

/*
 * Identificador da empresa que este módulo está sendo implantado.
 */
#define ID_EMPRESA 3
#define STRINGIZE2(s) #s
#define STRINGIZE(s) STRINGIZE2(s)
#define ID_EMPRESA_STRING " " STRINGIZE(ID_EMPRESA) 


//Protótipos de funções
void signal_handler_IO (int status);
void envia_alerta(int, int, int, int, int, int, int);
//int wait_flag=TRUE;

using namespace std;

 /*
 *Lista Ligada para alertas
 */
typedef struct lista_alerta{
 	int id;
 	int qtd_erros_parcial;
 	int qtd_parcial;
 	int qtd_erros_total;
 	unsigned long qtd_total;
 	struct lista_alerta *prox;
 }lista_alerta_def;


//Includes do Projeto
//#include "serial_sensor.h" // dmesg | grep -i tty
#include "banco_sensor.h"
#include "banco_sensor.cpp"
#include "funcoes.cpp"

//Linha de compilacao
// g++ -o bla sensor_header.h sensor.cpp bd_teste.cpp -lmysqlclient
