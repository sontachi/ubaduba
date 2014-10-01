// #include "sensor_header.h"

// #define PRINT_BANCO 1

banco_sensor::banco_sensor(void)
{
	strcpy(hostname,"192.168.1.100");
	strcpy(usuario,"root");
	strcpy(senha,"root");
	//strcpy(base_dados,"sensor");
	strcpy(base_dados,"sistema");
	//printf("\n Infos[main]: %s %s %s %s \n",hostname, usuario, senha, base_dados);
}

banco_sensor::banco_sensor(char base[50])
{
	strcpy(hostname,"localhost");
	strcpy(usuario,"root");
	strcpy(senha,"AgroXP01");
	strcpy(base_dados,base);
	//printf("\n Infos[main]: %s %s %s %s \n",hostname, usuario, senha, base_dados);
}

void banco_sensor::banco_sensor_cfg(char nome_host[50], char user[50], char pass[10], char bd_base_dados[20])
{

	strcpy(hostname,nome_host);
	strcpy(usuario,user);
	strcpy(senha,pass);
	strcpy(base_dados,bd_base_dados);
#ifdef PRINT_BANCO
	printf("\n Infos[main]: %s %s %s %s \n",hostname, usuario, senha, base_dados);
#endif
}


banco_sensor::~banco_sensor(void)
{
	// mysql_free_result(resp);
}

void banco_sensor::conectar()
{
	int flag_erro = 0;
	mysql_init(&conexao);
	while(! mysql_real_connect(&conexao,hostname,usuario,senha,base_dados,0,NULL,0))
	{
		printf("Tentando conexao com o BD (%s, %s, %s, %s)\n",hostname, usuario, senha, base_dados);
		sleep(5);
		flag_erro = 1;
	}
	if(flag_erro == 1)envia_alerta(0, 109, 0, 0, 0, 0, 0);
}

void banco_sensor::desconectar()
{
	mysql_close(&conexao);
	//printf("\nDesconectado!!\n\n");
}

//Função para comandos que retornam linhas (select, show, etc)
MYSQL_RES * banco_sensor::manda_query_consulta(string query)
{
	char * query_enviar = new char[query.length() + 1];


	strcpy(query_enviar,query.c_str());
	conectar();
	if(mysql_query(&conexao,query_enviar))
	{
		/*
		<ALERTA>
		*/
		delete query_enviar;
		cout << query; 
		printf("Erro: %s\n", mysql_error(&conexao));
		desconectar();
		return NULL;
	}
	else
	{
		resp = mysql_store_result(&conexao);
		delete query_enviar;
		desconectar();
		return resp;
	}

}

//Para testar chamando ela daqui mesmo (SEM CONECT E DESCONECT)
MYSQL_RES * banco_sensor::manda_query_consulta_sem_conect(string query)
{
	char * query_enviar = new char[query.length() + 1];


	strcpy(query_enviar,query.c_str());
	if(mysql_query(&conexao,query_enviar))
	{
		/*
		<ALERTA>
		*/
		delete query_enviar;
		cout << query; 
		printf("Erro: %s\n", mysql_error(&conexao));
		return NULL;
	}
	else
	{
		resp = mysql_store_result(&conexao);
		delete query_enviar;
		return resp;
	}

}

//Função para comandos que não retornam linhas (update, delete)
int banco_sensor::manda_query_insert(string query)
{
	char * query_enviar = new char[query.length() + 1];
	int resultado = -1;
	strcpy(query_enviar,query.c_str());

	string query_select;

	conectar();

	resultado = mysql_query(&conexao,query_enviar); // Executa a query de insert
	if(resultado == 0) // Insert efetuado corretamente
	{
		delete query_enviar;
		desconectar();
		return 1; // SUCESSO
	}
	else
	{
		/*
		<ALERTA>
		*/
		delete query_enviar;
		cout << query; 
		printf("\nErro: %s\n\n", mysql_error(&conexao));
		desconectar();
		return 0; // ERRO
	}

}


//Função para comandos que não retornam linhas (update, delete)
int banco_sensor::manda_query_acao(string query, char *nome_tabela, char *pk_nome, char *pk_valor)
{
	char * query_enviar = new char[query.length() + 1];
	int resultado = -1;
	strcpy(query_enviar,query.c_str());

	string query_select;

	//Monta select para verificar se o dado já existe, se existir a ação será feita normalmente.
	query_select = "SELECT * FROM ";
	query_select.append(nome_tabela);
	query_select.append(" WHERE ");
	query_select.append(pk_nome);
	query_select.append(" = ");
	query_select.append(pk_valor);


	conectar();

	resp = manda_query_consulta_sem_conect(query_select);

	if(mysql_num_rows(resp) > 0){ //Existe dado
		mysql_free_result(resp);

		resultado = mysql_query(&conexao,query_enviar); // Executa a query de ação
		if(resultado == 0) // Ação efetuada corretamente, 
		{
			delete query_enviar;
			desconectar();
			return 1; // SUCESSO
		}
		else
		{
			/*
			<ALERTA>
			*/
			delete query_enviar;
			cout << query; 
			printf("\nErro: %s\n\n", mysql_error(&conexao));
			desconectar();
			return 0; // ERRO
		}
	}
	else{
		delete query_enviar;
		desconectar();
		return 0; // ERRO - PK NÃO ENCONTRADO NA TABELA
	}
}

