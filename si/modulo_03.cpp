#include "sensor_header.h"

#include <sys/time.h>
#include <time.h>

//#define SEM_KEY 1338
#define SHM_KEY_RECEBE	1342
#define SHM_KEY_ENVIA	1345

//Diretivas de compilação para habilitar os prints na tela para depuração
// #define COMENTARIOS_N1
// #define COMENTARIOS_N1_ESCRITA
// #define COMENTARIOS_N1_LEITURA
// #define COMENTARIOS_N1_SOCKET
// #define COMENTARIOS_EXECUCAO

#define TIMEOUT_LEITURA_SERIAL 1000000
#define TIMEOUT_ESCRITA_SERIAL 40000

/*
 * MICRO_PER_SECOND define o numero de microsegundos em um segundo
 */
#define MICRO_PER_SECOND	1000000

/*
 * ALERTA - Variáveis de controle
 */
int num_serial_erro, num_serial_total;


/************************************* SERIAL - GLOBAIS *****************************************/

int wait_flag=TRUE; // Flag usada para receber o sinal de que tem algo na serial para ser capturada
struct termios newtio;
struct sigaction saio;
char caracter_a_enviar, caracter_a_receber;
int fd, res, i;
char devicename[80];

/************************************* SERIAL - GLOBAIS *****************************************/

/***************************** MEMORIA COMPARTILHADA - GLOBAIS **********************************/

/*
 * Ids e ponteiros para endereços das memórias compartilhadas do indice dos produtores, consumidores e do buffer.
 */
int	g_shm_id_recebe;
int	*g_shm_addr_recebe;

int	g_shm_id_envia;
int	*g_shm_addr_envia;

/***************************** MEMORIA COMPARTILHADA - GLOBAIS **********************************/


/************************************* SERIAL - FUNÇÕES *****************************************/
bool conexao_serial()
{
	char device[] = "/dev/ttyUSB0";
	strcpy(devicename,device);
	fd = -1;

	while(fd < 0)
	{
		if(num_serial_total > 100){
			num_serial_total = 0;
			num_serial_erro = 0;
		}
		num_serial_total++;

		fd = open(devicename, O_RDWR); // Segunda tentativa de conexão com a porta serial indicada
		if(fd < 0)
		{
			printf("\n\n");
			perror(devicename);
			printf("\nNao foi possivel abrir a porta serial.\nTentando novamente daqui 5 segundos ...");

			/*
			<ALERTA>
			Depois de N tentativas eh melhor mandar um alerta, mas nao sempre!
			*/
			num_serial_erro++;
			if(num_serial_erro == NUM_CONEXAO_MODULOS && num_serial_total <= 100){
				envia_alerta(3, 102, num_serial_erro, num_serial_total, 0, 0, 0);
				num_serial_erro = 0;
				num_serial_total = 0;
			}
			sleep(5);	
		}
	}

	//install the serial handler before making the device asynchronous
	saio.sa_handler = signal_handler_IO;
	sigemptyset(&saio.sa_mask);   //saio.sa_mask = 0;
	saio.sa_flags = 0;
	saio.sa_restorer = NULL;
	sigaction(SIGIO,&saio,NULL);
	
	fcntl(fd, F_SETOWN, getpid()); // allow the process to receive SIGIO
	fcntl(fd, F_SETFL, FASYNC); // make the file descriptor asynchronous

	// set new port settings for canonical input processing
	newtio.c_cflag = BAUDRATE | CS8 | CLOCAL | CREAD;
	newtio.c_iflag = IGNPAR;
	newtio.c_oflag = 0;
	newtio.c_lflag = 0;
	newtio.c_cc[VMIN]=1;
	newtio.c_cc[VTIME]=0;
	tcsetattr(fd,TCSANOW,&newtio);

	tcflush(fd, TCIOFLUSH);

	

	return true;
}

void desconectar_serial()
{
	close(fd);
}

int trata_escrita_serial(int *pkt_enviar)
{
	int i;

#ifdef COMENTARIOS_N1_ESCRITA
	struct timeval inicio,fim;      /* Para receber os tempos de RTT dos pacotes trafegados */
    float tempo_total = 0;          /* Recebe o tempo total do RTT */
    float media_tempo = 0;          /* Calcula a média dos RTTs */
	/*
    * Obtendo o tempo de início de transmissão
    */
		
	gettimeofday( &inicio, NULL );
#endif


	for(i = 0; i < TAM_PKG; i++)
	{
		usleep(5);
		caracter_a_enviar = static_cast<char>(pkt_enviar[i]);

#ifdef COMENTARIOS_N1_ESCRITA
		// printf("enviando_para_serial[%d] = %x \n",i, caracter_a_enviar);
#endif
		if(write(fd,&caracter_a_enviar,1) == 0)
		{
#ifdef COMENTARIOS_N1_ESCRITA
			printf(" ops_01 ");
#endif
			if(write(fd,&caracter_a_enviar,1) == 0)
			{
				desconectar_serial();
				if(conexao_serial())
				{
					if(write(fd,&caracter_a_enviar,1) == 0)
					{
#ifdef COMENTARIOS_N1_ESCRITA
						printf(" ops_02 ");
#endif
						desconectar_serial();
						return 1; // 1 -> Avisando que deu erro na transmissão
					}
				}
			}
		}
#ifdef COMENTARIOS_N1_ESCRITA
		//printf("\n");
#endif
	}

#ifdef COMENTARIOS_N1_ESCRITA
	/*
	* Obtendo o tempo final de transmissão
	*/   
	gettimeofday( &fim, NULL );

	/*
	* Calculando o tempo total de transmissão do pacote
	*/               
	tempo_total = (float)(fim.tv_sec  - inicio.tv_sec) * (float)MICRO_PER_SECOND;
	tempo_total += (fim.tv_usec - inicio.tv_usec);
	printf("Tempo Escrita = %.6f\n",tempo_total);
#endif

	return 0; // 0 -> Avisando que transmitiu com sucesso

}

/*
 * Função: controla_escrita_serial
 * Parâmetro: 
 *		int *pkt_enviar --> Endereço de memoria com o conteúdo que será enviado para os nos.
 * Retorno:
 *		0 -> OK
 *		1 -> Transmissao para a serial com problemas
 * Descricao:
 *		A execução principal cria dois filhos, um para ser o responsável por enviar o pacote
 * ao no e outro que tem a simples funcao de dormir por um determinado tempo. Se o segundo filho
 * que foi colocado para dormir acordar primeiro significa que o filho responsavel por transmitir
 * o pacote demorou muito e deve ser morto, caso contrário, mata-se o filho que esta dormindo.
 */
int controla_escrita_serial(int *pkt_enviar)
{
	pid_t timeout, filho, pid_terminou;
	int flag_erro_escrita = 0; // 0 -> Sem erro / 1 -> Com erro 
	
	// Criando filho responsavel por transmitir o pacote
	filho = fork();
	if(filho == 0)
	{
		// Chamando a funcao responsavel pela escrita na serial
		flag_erro_escrita = trata_escrita_serial(pkt_enviar);
		// ao terminar de transmitir, matar o filho!
		exit(0);
	}
	
	// Criando filho responsavel pelo timeout
	timeout = fork();
	if(timeout == 0)
	{
		// Colocando o filho para dormir
		usleep(TIMEOUT_ESCRITA_SERIAL);

#ifdef COMENTARIOS_N1_ESCRITA
		printf("TimeOUT (Escrita) .... \n");
#endif
		// ao terminar o sono do filho, mata-lo!
		exit(0);
	}
#ifdef COMENTARIOS_N1_ESCRITA
	printf("Filho_escritor = %d / Timeout = %d - Aguardando eles terminarem!!\n",filho, timeout);
#endif
	// Aguardando um dos filhos terminar.
	pid_terminou = wait(NULL);

#ifdef COMENTARIOS_N1_ESCRITA
	printf("O filho_escritor que morreu foi: %d\n",pid_terminou);
#endif

	// Verificando se o filho que morreu é o que estava transmitindo ou o responsavel pelo timeout
	// Se foi o timeout, entao matar o outro filho e avisar do erro usando a flag de controle.
	if(pid_terminou == timeout)
	{

#ifdef COMENTARIOS_N1_ESCRITA
		printf("Timeout disparado e matando filho_escritor ... \n");
#endif
		kill(filho, SIGKILL);
		flag_erro_escrita = 1;
		
#ifdef COMENTARIOS_N1_ESCRITA
		printf("Filho_escritor morto!\n");
#endif
	}
	// Se foi o filho da transmissao significa que a transmissao foi OK e devemos continuar, matando
	// o filho do timeout e restornando para quem chamou sem acusar erro.
	else
	{

#ifdef COMENTARIOS_N1_ESCRITA
		printf("Filho_escritor morreu sozinho!\n");
#endif

		kill(timeout, SIGKILL);
		
#ifdef COMENTARIOS_N1_ESCRITA
		printf("Timeout morto tb!\n");
#endif
	}
	return flag_erro_escrita;
}




int envia_dados(int *pkt_enviar)
{
	// pid_t pid_trata_serial;
	int flag_erro_escrita = 0; // 0 -> Sem erro / 1 -> Com erro

	// pid_trata_serial = fork();

	// if(pid_trata_serial == 0)
	// {
		if(controla_escrita_serial(pkt_enviar) == 0)
		{
			usleep(100);
		}
		else
			flag_erro_escrita = 1; //Indicando que ocorreu algum erro na escrita
	// }
	// else
	// {
	// 	while(wait(NULL) != pid_trata_serial); // Esperando o trata serial morrer ....
	// }
		
		return flag_erro_escrita;
}


void trata_leitura_serial(int *pkt_resposta)
{
	int flag_ok = 1; //0 -> Pacote recebido ok / 1 -> Pacote recebido com problemas
	int posicao = 0;
	int aux_pacote_resposta;

#ifdef COMENTARIOS_N1_LEITURA
	struct timeval inicio,fim;      /* Para receber os tempos de RTT dos pacotes trafegados */
    float tempo_total = 0;          /* Recebe o tempo total do RTT */
    float media_tempo = 0;          /* Calcula a média dos RTTs */
	/*
    * Obtendo o tempo de início de transmissão
    */
		
	gettimeofday( &inicio, NULL );
#endif

#ifdef COMENTARIOS_N1
	printf(" ******* Tratando a resposta na serial  %x ******** \n", pkt_resposta);
#endif
	while (posicao < TAM_PKG)
	{
#ifdef COMENTARIOS_N1
		printf("%d -> ",posicao);
#endif
		//Fazendo a leitura da serial
		res = read(fd,&caracter_a_receber,1);
		if (res > 0)
		{
#ifdef COMENTARIOS_N1
			printf("%x - ",caracter_a_receber);
#endif

			pkt_resposta[posicao] = static_cast<int>(caracter_a_receber);
			if(pkt_resposta[posicao] < 0)
			{
				pkt_resposta[posicao] = static_cast<int>(caracter_a_receber & 0xFF);
			}

			//ARRUMAR ERRO
			if(pkt_resposta[posicao] != -9){
				flag_ok = 0;
			}

#ifdef COMENTARIOS_N1
			printf("recebendo_da_serial[%d] = %x ,\n", posicao, pkt_resposta[posicao]);
#endif
		}
		else
		{
#ifdef COMENTARIOS_N1
			printf("[%d] Recebeu com erro!! \n\n", posicao);
			perror("[ ] Ao receber pacote de resposta do no:");
#endif
			flag_ok = 1;
			break;
		}
		posicao++;
		//printf("Esperando resposta da serial\n\n");
	}
	if(flag_ok == 1)
	{
		pkt_resposta[8] = -1;
	}
#ifdef COMENTARIOS_N1
	else
	{
		printf("\n\n********************* PKT recebido pela serial *******************\n\n");
		for(int i = 0; i < TAM_PKG; i++)
			printf("pk_recebido_NA_serial[%d] = %d\n",i, pkt_resposta[i]);
	}
#endif


#ifdef COMENTARIOS_N1_LEITURA
	/*
	* Obtendo o tempo final de transmissão
	*/   
	gettimeofday( &fim, NULL );

	/*
	* Calculando o tempo total de transmissão do pacote
	*/               
	tempo_total = (float)(fim.tv_sec  - inicio.tv_sec) * (float)MICRO_PER_SECOND;
	tempo_total += (fim.tv_usec - inicio.tv_usec);
	printf("Tempo Leitura = %.6f\n",tempo_total);
#endif
}

/*
 * Função: controla_leitura_serial
 * Parâmetro: 
 *		int *pkt_resposta --> Endereço de memoria que sera preenchida pela resposta do pacote do no.
 * Retorno:
 *		Nada
 * Descricao:
 *		A execução principal cria dois filhos, um para ser o responsável por receber o pacote
 * do no e outro que tem a simples funcao de dormir por um determinado tempo. Se o segundo filho
 * que foi colocado para dormir acordar primeiro significa que o filho responsavel por receber
 * o pacote demorou muito e deve ser morto, caso contrário, mata-se o filho que esta dormindo.
 */
void controla_leitura_serial(int *pkt_resposta)
{
	pid_t timeout, filho, pid_terminou;
	
	// Criando o filho que recebera o pacote do no
	filho = fork();
	if(filho == 0)
	{
		//Recebe o valor da serial
		trata_leitura_serial(pkt_resposta);
		//Morte do filho
		exit(0);
	}
	
	// Criando o filho que dormira e controlara o tempo maximo para receber um pacote
	timeout = fork();
	if(timeout == 0)
	{
		// Dorme por um determinado tempo
		usleep(TIMEOUT_LEITURA_SERIAL);

#ifdef COMENTARIOS_N1_LEITURA
		printf("TimeOUT .... \n");
#endif
		// Morre
		exit(0);
	}
#ifdef COMENTARIOS_N1_LEITURA
	printf("Filho = %d / Timeout = %d - Aguardando eles terminarem!!\n",filho, timeout);
#endif
	// Aguarda um dos filhos morrer e recebe seu PID
	pid_terminou = wait(NULL);

#ifdef COMENTARIOS_N1_LEITURA
	printf("O filho que morreu foi: %d\n",pid_terminou);
#endif

	// Verifica se o filho que terminou foi o do timeout
	// Se sim, matar o filho da recepcao e responder com pacote de erro.
	if(pid_terminou == timeout)
	{

#ifdef COMENTARIOS_N1_LEITURA
		printf("Timeout disparado e matando filho ... \n");
#endif
		kill(filho, SIGKILL); // Matando outro filho
		pkt_resposta[8] = -1; // Sinalizando erro na recepcao de valores na serial
		
#ifdef COMENTARIOS_N1_LEITURA
		printf("Filho morto!\n");
#endif
	}
	else
	{

#ifdef COMENTARIOS_N1_LEITURA
		printf("Filho morreu sozinho!\n");
#endif
		// Matando o filho do timeout
		kill(timeout, SIGKILL);
		
#ifdef COMENTARIOS_N1_LEITURA
		printf("Timeout morto tb!\n");
#endif
	}
}

void recebe_dados(int *pkt_resposta)
{
	pid_t pid_trata_serial;

	int *retorno_leitura_serial, flag_pkg_controle = 1; // 0 -> Pacote com informações / 1 -> Pacote com erro


	//usleep(30000);

#ifdef COMENTARIOS_N1
	printf("\n\n\n PKG vindo do M3 %x\n\n", pkt_resposta);
	for(int i = 0; i < TAM_PKG; i++)
		printf("Recebi pkg do M3 [%d] = %d\n", i, pkt_resposta[i]);

	printf("\n\n\n IMPRIMINDO PKG RECEBIDO \n\n");

#endif

	pid_trata_serial = fork();

	if(pid_trata_serial == 0)
	{
		controla_leitura_serial(pkt_resposta);
		usleep(100);
		
#ifdef COMENTARIOS_N1
		printf("Leitura ok da serial, retornando os valores *************\n");
		for(int i = 0; i < TAM_PKG; i++)
		{
			printf("Recebi da serial [%d] = %d\n",i,pkt_resposta[i]);
		}
#endif
		exit(0);
	}
	else
		while(wait(NULL) != pid_trata_serial); // Esperando o trata serial morrer ....
}

//
/***************************************************************************
* signal handler. sets wait_flag to FALSE, to indicate above loop that     *
* characters have been received.                                           *
***************************************************************************/
//
void signal_handler_IO (int status)
{
#ifdef COMENTARIOS_N1
	printf("received SIGIO signal.\n");
#endif
	wait_flag = FALSE;
}

/************************************* SERIAL - FUNÇÕES *****************************************/


/*
 * Função que o processo filho executa para receber e enviar as mensagens
 */
void trata_conexao(int fd_sock, int *pacote_recebido, int *pacote_resposta)
{
	int numbytes_recv = 0, numbytes_send = 0; /* Variáveis que receberão a quantidade de bytes que foram transmitidos */

	//if(!conexao_serial())
	//	if(!conexao_serial())
	//	{
	//		printf("\n\n(FUNCAO: trata_conexao):Impossivel estabelecer conexao com a serial!\n\n");
	//		exit(1);
	//	}

    /*
    * Loop de comunicacao entre modulo 3 e os outros
    */
	
	// while(1)
	// {
		tcflush(fd, TCIOFLUSH);
		if ((numbytes_recv = recv(fd_sock, pacote_recebido, (TAM_PKG * sizeof(int)), 0)) == -1)
		{
				perror("recv");
				// break;
		}

		if(numbytes_recv > 0)
		{

#ifdef COMENTARIOS_N1_SOCKET
			printf("Pacote recebido NO modulo 03 - %d\n",numbytes_recv);
			for(int i = 0; i < TAM_PKG; i++)
				printf("Recebi_pkg_do_modulo[%d] = %d\n",i,pacote_recebido[i]);
#endif
			if(envia_dados(pacote_recebido) == 0)
			{
				recebe_dados(pacote_resposta);
				if(pacote_resposta[8] == -1)
					pacote_resposta[10] = pacote_recebido[8];
			}			

#ifdef COMENTARIOS_N1_SOCKET
			else
				printf("Erro na transmissao para a serial (envio do pacote para os nos)\n\n");
		
			for(int i = 0; i < TAM_PKG; i++)
				printf("Recebi_pkg_no_m3_da_seria[%d] = %d\n",i,pacote_resposta[i]);
			
			pacote_resposta[8] = -1;

			printf ("Voltando pacote para o modulo solicitante ...\n");                
#endif
                
			if ((numbytes_send = send(fd_sock, pacote_resposta,  (TAM_PKG * sizeof(int)), 0)) == -1)
			{
					perror("send");
					return;
			}
		}
		else
		{
#ifdef COMENTARIOS_EXECUCAO
			printf("\n\n\n********************** Recebi ZERO ***********************\n\n");
#endif
			// break;
		}
	// }
#ifdef COMENTARIOS_EXECUCAO
    printf ("\n\n\nFinalizando conexão ...\n");
#endif
	
}




/*
 * Função que controlará 
 */
void recebe_conexao_dos_outros_modulos()
{
	int sockfd, new_fd;             /* File Descriptions dos Sockets que serão criados */
	struct sockaddr_in my_addr;     /* Informações do endereço do servidor */
	struct sockaddr_in their_addr;  /* Informações do endereço do cliente que está se comunicando com o Servidor */
	int sin_size;                   /* Tamanho da mensagem */
	int *pacote_recebido, *pacote_resposta;
	
	/*
	 * Criando o segmento de memoria compartilhada do indice dos produtores e salvando seu id em g_shm_id
	 * Atribuindo seu endereço de memória para o ponteiro g_shm_addr
	 * Inicializando (tamanho do buffer-1) para o indice
	 * int shmget(key_t key, int size, int shmflg)
	 */

	/******************************************/
	if( (g_shm_id_envia = shmget( SHM_KEY_ENVIA, (sizeof(int)*TAM_PKG), IPC_CREAT | 0666)) == -1 ) {
		fprintf(stderr,"Impossivel criar o segmento de memoria compartilhada!\n");
		//exit(1);
		/*
		<ALERTA>
		*/
		envia_alerta(3, 103, 1, 2, 0, 0, 0);
	}
	if( (g_shm_addr_envia = (int *)shmat(g_shm_id_envia, NULL, 0)) == (int *)-1 ) {
		fprintf(stderr,"Impossivel associar o segmento de memoria compartilhada!\n");
		//exit(1);
		/*
		<ALERTA>
		*/
		envia_alerta(3, 104, 1, 2, 0, 0, 0);
	}
	pacote_recebido = g_shm_addr_envia;
	
	/******************************************/

	if( (g_shm_id_recebe = shmget( SHM_KEY_RECEBE, (sizeof(int)*TAM_PKG), IPC_CREAT | 0666)) == -1 ) {
		fprintf(stderr,"Impossivel criar o segmento de memoria compartilhada!\n");
		//exit(1);
		/*
		<ALERTA>
		*/
		envia_alerta(3, 103, 2, 2, 0, 0, 0);
	}
	if( (g_shm_addr_recebe = (int *)shmat(g_shm_id_recebe, NULL, 0)) == (int *)-1 ) {
		fprintf(stderr,"Impossivel associar o segmento de memoria compartilhada!\n");
		//exit(1);
		/*
		<ALERTA>
		*/
		envia_alerta(3, 104, 2, 2, 0, 0, 0);
	}
	pacote_resposta = g_shm_addr_recebe;
		

	/******************************************/
	
	/*
	* Criando um Socket do tipo TCP
	*/
	if ((sockfd = socket(AF_INET, SOCK_STREAM, 0)) == -1) {
		perror("socket");
		//exit(1);
		/*
		<ALERTA>
		*/
		envia_alerta(3, 105, 1, 1, 0, 0, 0);
	}
	my_addr.sin_family = AF_INET;           /* host byte order */
	my_addr.sin_port = htons(TCP_PORT_MOD3);   /* Porta onde o Servidor TCP estará ouvindo */
	my_addr.sin_addr.s_addr = INADDR_ANY;   /* auto-fill IP */
	bzero(&(my_addr.sin_zero), 8);          /* zerando o resto da estrutura */

	/*
	* Associa o socket criado
	*/
	if (bind(sockfd, (struct sockaddr *)&my_addr, sizeof(struct sockaddr)) == -1) {
		perror("bind");
		//exit(1);
		/*
		<ALERTA>
		Esta eh o mais importante, pois eh o que mais dah problema
		*/
		envia_alerta(3, 106, 1, 1, 0, 0, 0);
	}

	/*
	* Habilita o Socket para ouvir, ou esperar conexões
	*/
	if (listen(sockfd, BACKLOG) == -1) {
		perror("listen");
		//exit(1);
		/*
		<ALERTA>
		*/
		envia_alerta(3, 107, 1, 1, 0, 0, 0);
	}

	conexao_serial();
	printf("ABRIU SERIAL\n");

	/*
	* Loop que receberá as conexões, criará um processo filho e voltará a ficar pronto para receber uma conexão
	*/	
	while(1) 
	{                  
		sin_size = sizeof(struct sockaddr_in);
		for(int i = 0; i< TAM_PKG; i++)
		{
			pacote_recebido[i] = -8;
			pacote_resposta[i] = -9;
		}

#ifdef COMENTARIOS_EXECUCAO
		printf ("Aguardando Conexoes ... no m3 \n\n");
#endif
		/*
		* Aceita conexões de clientes
		*/
		if ((new_fd = accept(sockfd, (struct sockaddr *) &their_addr, (socklen_t *) &sin_size)) == -1) 
		{
			perror("accept");
			continue;
		}
#ifdef COMENTARIOS_EXECUCAO
		printf("O servidor aceitou uma requisicao de conexao vinda de: %s\n", inet_ntoa(their_addr.sin_addr));
#endif
		/*printf("[MAIN] Memorias: Resposta - %x / Recebido - %x \n\n", pacote_recebido, pacote_resposta);
		sleep(5);*/

		trata_conexao(new_fd, pacote_recebido, pacote_resposta);
		close(new_fd);
	}

	close(sockfd);                          /* o processo pai não necessita mais deste descritor */       
	while(waitpid(-1,NULL,WNOHANG) > 0);    /* Limpando os processos filhos */
	/*
	 * Removendo as memorias compartilhadas
	 */
	if( shmctl(g_shm_id_envia,IPC_RMID,NULL) != 0 ) {
		    fprintf(stderr,"Impossivel remover o segmento de memoria compartilhada id envia!\n");
		    //exit(1);
			/*
			<ALERTA>
			*/
			envia_alerta(3, 108, 1, 2, 0, 0, 0);
	}
	if( shmctl(g_shm_id_recebe,IPC_RMID,NULL) != 0 ) {
		    fprintf(stderr,"Impossivel remover o segmento de memoria compartilhada id recebe!\n");
		    //exit(1);
			/*
			<ALERTA>
			*/
			envia_alerta(3, 108, 2, 2, 0, 0, 0);
	}

	desconectar_serial();

}



void funcao_teste_pisca_pisca()
{
	int *pacote_recebido, *pacote_resposta;

	/*
	 * Criando o segmento de memoria compartilhada do indice dos produtores e salvando seu id em g_shm_id
	 * Atribuindo seu endereço de memória para o ponteiro g_shm_addr
	 * Inicializando (tamanho do buffer-1) para o indice
	 * int shmget(key_t key, int size, int shmflg)
	 */

	/******************************************/
	if( (g_shm_id_envia = shmget( SHM_KEY_ENVIA, (sizeof(int)*TAM_PKG), IPC_CREAT | 0666)) == -1 ) {
		fprintf(stderr,"Impossivel criar o segmento de memoria compartilhada!\n");
		exit(1);
	}
	if( (g_shm_addr_envia = (int *)shmat(g_shm_id_envia, NULL, 0)) == (int *)-1 ) {
		fprintf(stderr,"Impossivel associar o segmento de memoria compartilhada!\n");
		exit(1);
	}
	pacote_recebido = g_shm_addr_envia;
	
	/******************************************/

	if( (g_shm_id_recebe = shmget( SHM_KEY_RECEBE, (sizeof(int)*TAM_PKG), IPC_CREAT | 0666)) == -1 ) {
		fprintf(stderr,"Impossivel criar o segmento de memoria compartilhada!\n");
		exit(1);
	}
	if( (g_shm_addr_recebe = (int *)shmat(g_shm_id_recebe, NULL, 0)) == (int *)-1 ) {
		fprintf(stderr,"Impossivel associar o segmento de memoria compartilhada!\n");
		exit(1);
	}
	pacote_resposta = g_shm_addr_recebe;
		

	/******************************************/

	pacote_recebido[8] = 4;
	pacote_recebido[46] = 1;

	if(!conexao_serial())
		if(!conexao_serial())
		{
			printf("\n\n(FUNCAO: funcao_teste_pisca_pisca):Impossivel estabelecer conexao com a serial!\n\n");
			exit(1);
		}

	for(i = 0; i < 1000; i++)
	{
		usleep(10000);
		envia_dados(pacote_recebido);
		//pkg2 = obj_serial->recebe_dados(0);
		recebe_dados(pacote_resposta);
		for(int i = 0; i < TAM_PKG; i++)
		{
			printf("Recebi_pkg_m3[%d] = %d\n",i,pacote_resposta[i]);
		}
	}
}

bool verifica_pacote_recebido(int pkg_recebido[TAM_PKG])
{
	if(pkg_recebido[8] == -1)
		return false;
	return true;
}

bool verificacao_base_no()
{
	int *pacote_a_enviar, *pacote_resposta;
	
	int pacote_aux[TAM_PKG];
	int i;


	int sequencia_consulta_base_sensor = 0; //Contador para saber qual query enviar
	float rssi_down, rssi_up, vten;

	conexao_serial();
	sleep(2);
		
	/*
	 * Criando o segmento de memoria compartilhada do indice dos produtores e salvando seu id em g_shm_id
	 * Atribuindo seu endereço de memória para o ponteiro g_shm_addr
	 * Inicializando (tamanho do buffer-1) para o indice
	 * int shmget(key_t key, int size, int shmflg)
	 */
	/******************************************/
	if( (g_shm_id_envia = shmget( SHM_KEY_ENVIA, (sizeof(int)*TAM_PKG), IPC_CREAT | 0666)) == -1 ) {
		fprintf(stderr,"Impossivel criar o segmento de memoria compartilhada!\n");
		return false;
	}
	if( (g_shm_addr_envia = (int *)shmat(g_shm_id_envia, NULL, 0)) == (int *)-1 ) {
		fprintf(stderr,"Impossivel associar o segmento de memoria compartilhada!\n");
		return false;
	}
	pacote_a_enviar = g_shm_addr_envia;
	for(int i = 0; i< TAM_PKG; i++)
		pacote_a_enviar[i] = 0;

	/******************************************/
	if( (g_shm_id_recebe = shmget( SHM_KEY_RECEBE, (sizeof(int)*TAM_PKG), IPC_CREAT | 0666)) == -1 ) {
		fprintf(stderr,"Impossivel criar o segmento de memoria compartilhada!\n");
		return false;
	}
	if( (g_shm_addr_recebe = (int *)shmat(g_shm_id_recebe, NULL, 0)) == (int *)-1 ) {
		fprintf(stderr,"Impossivel associar o segmento de memoria compartilhada!\n");
		return false;
	}
	pacote_resposta = g_shm_addr_recebe;
	for(int i = 0; i< TAM_PKG; i++)
		pacote_resposta[i] = 0;

	printf("\n\nInicializando envios pela serial, primeiro pacote ... \n");

	//Query para encontrar a dupla no x base iguais

			for(i = 0; i < TAM_PKG; i++){
				pacote_a_enviar[i] = -9;
				pacote_resposta[i] = -8;
			}


			pacote_a_enviar[10] = 0;
			pacote_a_enviar[8] = 1;

			//Verificando principalmente os possíveis erros de envio de pacote
			printf("\n***************** Base = %d X No = %d *****************\n",pacote_a_enviar[10], pacote_a_enviar[8]);

			for(i = 0; i < TAM_PKG; i++){
				pacote_aux[i]=pacote_a_enviar[i];
				// printf("%d ", pacote_aux[i]);
			}

			// for(i = 0; i < TAM_PKG; i++)printf("P AUX = %d\n", pacote_aux[i]);

			if(envia_dados(pacote_aux) == 0) // Tenta primeiro envio
			{
				printf("--> [OK] PRIMEIRO pacote enviado.\n");
				recebe_dados(pacote_resposta); // recebe primeira resposta
			}
			else // primeiro envio não deu certo, tentar novamente
			{
				printf("--> [NOK] PRIMEIRO pacote NAO enviado ... tentar novamente!\n");
				for(i = 0; i < TAM_PKG; i++)pacote_aux[i]=pacote_a_enviar[i];
				if(envia_dados(pacote_aux) == 0) // tentando segundo envio
				{
					printf("----> [OK] SEGUNDO pacote enviado.\n");
					recebe_dados(pacote_resposta); // recebe resposta da segunda tentativa
				}
				else
				{
					printf("----> [NOK] SEGUNDO pacote NAO enviado!\n");
					printf("--> [ERRO] Base = %d X No = %d: [ERRO]\n",pacote_a_enviar[10], pacote_a_enviar[8]);
					printf(" ********************************* [ERRO] ********************************* \n\n");
					// continue;
				}			
			}
	
			//Verificando erros de pacotes recebidos
			if(!verifica_pacote_recebido(pacote_resposta)) // verificando se o pacote que chegou é válido
			{
				//pacote não eh válido, portanto, tentar mandar novamente para obter um pacote válido
				//inicia-se aqui a nova tentativa de envio e recebimento de algo válido
				printf("------> [ERRO] PRIMEIRO pacote de resposta invalido. \n");
				
				for(i = 0; i < TAM_PKG; i++)pacote_aux[i]=pacote_a_enviar[i];
				if(envia_dados(pacote_aux) == 0)
				{
					printf("------>  [OK] SEGUNDO envio de pacote. \n");
					recebe_dados(pacote_resposta);
				}
				else
				{
					printf("------> [ERRO] SEGUNDO pacote NAO enviado\n");
					printf("--> [ERRO] Base = %d X No = %d: [ERRO]\n",pacote_a_enviar[10], pacote_a_enviar[8]);
					printf(" ********************************* [ERRO] ********************************* \n\n");
					// continue;
				}
				if(!verifica_pacote_recebido(pacote_resposta)) // verificando se o pacote que chegou é válido
				{
					printf("------> [ERRO] SEGUNDA resposta invalida! \n");
					printf("--> [ERRO] Base = %d X No = %d: [ERRO]\n",pacote_a_enviar[10], pacote_a_enviar[8]);
					printf(" ********************************* [ERRO] ********************************* \n\n");
					// continue;
				}
				else
				{
					printf("------> [OK] SEGUNDA resposta veio CORRETA ...\n");
					if(pacote_resposta[0] > 128)
						rssi_down = ((pacote_resposta[0]-256)/2)-74;
					else
						rssi_down = (pacote_resposta[0]/2)-74;

					if(pacote_resposta[2] > 128)
						rssi_up = ((pacote_resposta[2]-256)/2)-74;
					else
						rssi_up = (pacote_resposta[2]/2)-74;

					printf("--> RSSI: [UP] = %.2f / [DOWN] = %.2f \n",rssi_up, rssi_down);

					if(pacote_resposta[22] == 10)
					{
						vten =  0.003223 * ((pacote_resposta[23]*256) + pacote_resposta[24]) * 11;
						printf("--> [Alimentacao]: %.2f\n", vten);
					}
					else if(pacote_resposta[28] == 21)
					{
						vten =  0.003223 * ((pacote_resposta[29]*256) + pacote_resposta[30]) * 11;
						printf("--> [Alimentacao]: %.2f\n", vten);
					}
					else
						printf("--> [Alimentacao]: Sem informacoes! [22] = %d / [28] = %d\n", pacote_resposta[22], pacote_resposta[28]);

					printf("[OK] Base = %d X No = %d: [OK]\n ********************************** [OK] ********************************** \n\n",pacote_a_enviar[10], pacote_a_enviar[8]);
				}

			}
			else
			{
				printf("------> [OK] PRIMEIRA resposta veio CORRETA ...\n");

				if(pacote_resposta[0] > 128)
					rssi_down = ((pacote_resposta[0]-256)/2)-74;
				else
					rssi_down = (pacote_resposta[0]/2)-74;

				if(pacote_resposta[2] > 128)
					rssi_up = ((pacote_resposta[2]-256)/2)-74;
				else
					rssi_up = (pacote_resposta[2]/2)-74;

				printf("--> RSSI: [UP] = %.2f / [DOWN] = %.2f \n",rssi_up, rssi_down);

				if(pacote_resposta[22] == 10)
				{
					vten =  0.003223 * ((pacote_resposta[23]*256) + pacote_resposta[24]) * 11;
					printf("--> [Alimentacao]: %.2f\n", vten);
				}
				else if(pacote_resposta[28] == 21)
				{
					vten =  0.003223 * ((pacote_resposta[29]*256) + pacote_resposta[30]) * 11;
					printf("--> [Alimentacao]: %.2f\n", vten);
				}
				else
					printf("--> [Alimentacao]: Sem informacoes! [22] = %d / [28] = %d\n", pacote_resposta[22], pacote_resposta[28]);

				printf("[OK] Base = %d X No = %d: [OK]\n ********************************** [OK] ********************************** \n\n",pacote_a_enviar[10], pacote_a_enviar[8]);
				}

	desconectar_serial();
	/*
	 * Removendo as memorias compartilhadas
	 */
	if( shmctl(g_shm_id_recebe,IPC_RMID,NULL) != 0 ) {
		    fprintf(stderr,"Impossivel remover o segmento de memoria compartilhada id recebe!\n");
		    return false;
	}

	if( shmctl(g_shm_id_envia,IPC_RMID,NULL) != 0 ) {
		    fprintf(stderr,"Impossivel remover o segmento de memoria compartilhada id envia!\n");
		    return false;
	}

	return true;
}



int main()
{
	if(verificacao_base_no())
		printf("Inicializacao OK!\n\n");
	else
	{
		printf("Problemas na inicializacao!\n\n");
		return 1;
	}
	num_serial_erro = 0;
	num_serial_total = 0;
	// funcao_teste_pisca_pisca();
	recebe_conexao_dos_outros_modulos();	
	return 0;
}