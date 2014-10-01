int * envia_pacote_modulo_03(int pacote[])
{
	int sockfd = 0;         /* File Description do Socket */
	int numbytes = 0;       /* Numero de bytes recebidos */
	int i = 0;
	int sin_size = sizeof(struct sockaddr_in);      /* Tamanho da estrutura */
		

	struct sockaddr_in serv_addr;   /* informação de endereço de informação */

#ifdef COMENTARIOS_M1
	//Criando o socket
	printf("Cria Socket\n");
#endif
	if ((sockfd = socket(AF_INET, SOCK_STREAM, 0)) == -1)
	{
		perror("socket");
		exit(1);
	}

	 //printf("Pacote sendo enviado para o modulo 03 - %d - %d\n", sizeof(pacote), (TAM_PKG * sizeof(int)));
	 // printf("ENVIADO: \n");
	 // for(i=0; i<TAM_PKG; i++)
	 // 	printf("%d ",pacote[i]);

	serv_addr.sin_family = AF_INET; /* host byte order */
    serv_addr.sin_port = htons(TCP_PORT_MOD3); // Porta onde o Servidor TCP estará ouvindo
    serv_addr.sin_addr = *((struct in_addr *)he->h_addr);
    bzero(&(serv_addr.sin_zero), 8);/* zera o resto da estrutura */

#ifdef COMENTARIOS_M1
    printf("\nConecta Socket\n");
#endif
	if (connect(sockfd, (struct sockaddr *)&serv_addr, sizeof(struct sockaddr)) == -1) 
    {
    	
		perror("connect");
		close(sockfd);
		printf("Tentando nova conexao em alguns segundos ...\n");
		sleep(5);
		pacote = envia_pacote_modulo_03(pacote);
		return pacote;
    }

	/*
	 * Enviando o pacote para o módulo 03
	 */
#ifdef COMENTARIOS_M1_SOCKET
	printf("Envia Socket\n");
	for(int i = 0; i < TAM_PKG; i++)
		 printf("Enviando_pkg_para_M3[%d] = %d\n",i,pacote[i]);
#endif
	if (send(sockfd, pacote, (TAM_PKG * sizeof(int)), 0) == -1)
    {
        perror("send");
        exit(0);
    }

	/*
	 * Recebendo o pacote do o módulo 03
	 */
#ifdef COMENTARIOS_M1_SOCKET
	  printf("Recebe Socket\n");
#endif
    if ((numbytes=recv(sockfd, pacote, (TAM_PKG * sizeof(int)), 0)) == -1) 
    {
		perror("recv");
		exit(1);
    }
#ifdef COMENTARIOS_M1_SOCKET
     printf("Recebe Socket2\n");
	 for(int i = 0; i < TAM_PKG; i++)
		 printf("Recebi_pkg_no_m3[%d] = %d\n",i,pacote[i]);
#endif
	
	/*
	 * Imprimindo os dados obtidos na transmissão do pacote
	 */ 
	 // printf("\n\nRECEBENDO\n");             
	 // for(int i = 0; i < TAM_PKG; i++)
	 // 	printf("%d ",pacote[i]);

	close(sockfd);
	return pacote;

}


//ZERA PACOTE E COLOCA BASE E NÓ E ATRIBUI VALOR DOS NÓS E BASES ANTERIORES
void monta_pacote(int pacote_enviar[], int num_no, int num_base, int *num_no_ant, int *num_base_ant){
	*num_no_ant = num_no;
	*num_base_ant = num_base;

	for(int i = 0; i< TAM_PKG; i++)
		pacote_enviar[i] = -7;

	pacote_enviar[8] = num_no;
	pacote_enviar[10] = num_base;
	//pacote_enviar[43] = 1;
}
