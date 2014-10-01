#include "sensor_header.h"

// #define COMENTARIOS

void remove_velhos_valores(char qtd_dias[3], char nome_tabela[34], char data_base[21], char fk_sensor[3])
{
	string querys;
	char bd_sensor_consolidado[50], bd_sensor[50], nome_coluna[11];
	strcpy(bd_sensor_consolidado,"azul_consolidado");
	strcpy(bd_sensor,"azul_consolidado");
	strcpy(nome_coluna, "fk_sensor");
	banco_sensor *database_sensor = new banco_sensor(bd_sensor);
	banco_sensor *database_sensor_consolidado = new banco_sensor(bd_sensor_consolidado);
	MYSQL_RES *resp;
	MYSQL_ROW linhas;
	MYSQL_FIELD *campos;

	int result;

	querys = "DELETE FROM ";
	querys.append(nome_tabela);
	querys.append(" WHERE data < (DATE_SUB(\'");
	querys.append(data_base);
	querys.append("\', INTERVAL ");
	querys.append(qtd_dias);
	querys.append(" DAY)) AND fk_sensor = ");
	querys.append(fk_sensor);
	querys.append(";");

#ifdef COMENTARIOS
	printf("\n\nQuery = ");
	cout << querys;
	printf("\n\n");
#endif

	//resp = database_sensor->manda_query(querys);
	result = database_sensor_consolidado->manda_query_acao(querys, nome_tabela, nome_coluna, fk_sensor);
}

void consolida_valores(char intervalo[3], char fk_sensor[3], char fk_perfil[3])
{
	string querys;
	char base_dados[50];
	strcpy(base_dados,"azul_consolidado");
	banco_sensor *bd_sensor = new banco_sensor(base_dados);
	MYSQL_RES *resp;
	MYSQL_ROW linhas;
	MYSQL_FIELD *campos;
	FILE *arq_com_data;
	char nome_tabela[100];
	char data_do_arquivo[21];
	char hora[21], minuto_bd[3], qtd_dias_dados_no_banco[3];
	char nome_arquivo[60];	
	char data_maxima[21];
	int result;

	// if(!strcmp(intervalo,"10")) // para 15 dias, intervalos de 10 minutos consolidados
	// {
	// 	strcpy(nome_tabela,"consultas_sensor_historico_15dias");
	// 	strcpy(nome_arquivo,"arquivo_data_azul_consolidado_num_");
		// strcat(nome_arquivo,fk_perfil);
		// strcat(nome_arquivo,"_");
		// strcat(nome_arquivo,fk_sensor);
		// strcat(nome_arquivo,"_15dias.txt");
	// }
	// else 
	if(!strcmp(intervalo,"10")) // para 30 dias, intervalos de 60 minutos consolidados
	{
		strcpy(nome_tabela,"consultas_sensor_historico_30dias");
		strcpy(nome_arquivo,"arquivo_data_azul_consolidado_num_");
		strcat(nome_arquivo,fk_perfil);
		strcat(nome_arquivo,"_");
		strcat(nome_arquivo,fk_sensor);
		strcat(nome_arquivo,"_mes.txt");
	}
	printf("\n\n************************* ---------------------------- *****************************\n\n");
	printf("\n\n------------------------ ***************************** ---------------------------- \n\n");
	printf("\n--> Nome da tabela = %s\n--> Nome do arquivo = %s\n", nome_tabela, nome_arquivo);

	
	arq_com_data = fopen (nome_arquivo , "r");
	if (arq_com_data != NULL)
	{
#ifdef COMENTARIOS
		printf("Arquivo existe ... pegar valor\n\n");
#endif
		if ( fgets (data_do_arquivo , 21 , arq_com_data) != NULL )
		{
			if(strlen(data_do_arquivo) < 19)
			{
#ifdef COMENTARIOS
				printf("Arquivo vazio ou com informacao errada! %d\n\n", strlen(data_do_arquivo));
#endif
				fclose(arq_com_data);
				exit(1);
			}
			else
			{
#ifdef COMENTARIOS
				printf("Arquivo com conteudo! %d %s\n\n", strlen(data_do_arquivo), data_do_arquivo);
#endif
				fclose(arq_com_data);
			}

		}
		else
		{
#ifdef COMENTARIOS
			printf("erro na leitura \n\n");
#endif
			fclose(arq_com_data);
			exit(1);
		}
	}
	else
	{
#ifdef COMENTARIOS
		printf("Arquivo NAO existe ... pegar primeiro valor do BD\n\n");
#endif
		querys = "SELECT min(data) FROM consultas_sensor_historico";
		resp = bd_sensor->manda_query_consulta(querys);
		linhas=mysql_fetch_row(resp);
		strcpy(data_do_arquivo,linhas[0]);
		
		data_do_arquivo[14] = '0';
		data_do_arquivo[15] = '0';
		data_do_arquivo[17] = '0';
		data_do_arquivo[18] = '0';

		querys="SELECT DATE_FORMAT('"; 	querys.append(data_do_arquivo);	querys.append("', '%i');");
		resp = bd_sensor->manda_query_consulta(querys);
		linhas=mysql_fetch_row(resp);
		strcpy(minuto_bd,linhas[0]);



#ifdef COMENTARIOS
		printf("domindo 2 segundos ... %c %c / %s \n",data_do_arquivo[17],data_do_arquivo[18], data_do_arquivo);
		sleep(2);
#endif

	}

	printf("--> Data obtida = %s\n",data_do_arquivo);

	querys="SELECT DATE_FORMAT('"; 	querys.append(data_do_arquivo);	querys.append("', '%Y-%m-%d %H:%i:%s');");
	resp = bd_sensor->manda_query_consulta(querys);
	linhas=mysql_fetch_row(resp);
	strcpy(hora,linhas[0]);

#ifdef COMENTARIOS
	printf("Hora obtida = %s\n\n",hora);
#endif

	querys = "SELECT max(data) FROM consultas_sensor_historico";
	resp = bd_sensor->manda_query_consulta(querys);
	linhas=mysql_fetch_row(resp);
	strcpy(data_maxima,linhas[0]);

	data_maxima[14] = '0';
	data_maxima[15] = '0';
	data_maxima[17] = '0';
	data_maxima[18] = '0';

	if(strcmp(data_maxima,hora))
	{
		querys = "insert into ";
		querys.append(nome_tabela);
		querys.append(" (`fk_sensor`, `valor_lido`, `data`)  (select fk_sensor, fk_perfil, ROUND(avg(valor_lido),2), (DATE_ADD('");
		querys.append(data_do_arquivo);
		querys.append("', INTERVAL ");
		querys.append(intervalo);
		querys.append(" MINUTE))  from consultas_sensor_historico as x where data > (DATE_ADD('");
		querys.append(data_do_arquivo);
		querys.append("', INTERVAL 0 MINUTE)) AND data <= (DATE_ADD('");
		querys.append(data_do_arquivo);
		querys.append("', INTERVAL ");
		querys.append(intervalo);
		querys.append(" MINUTE)) AND x.fk_sensor = fk_sensor AND x.fk_perfil = fk_perfil AND fk_sensor = ");
		querys.append(fk_sensor);
		querys.append("'AND fk_perfil = ");
		querys.append(fk_perfil);
		querys.append(");");

		// querys = "insert into ";
		// querys.append(nome_tabela);
		// querys.append(" (`fk_sensor`, `valor_lido`, `data`)  (select fk_sensor, ROUND(avg(valor_lido),2), (DATE_ADD('");
		// querys.append(data_do_arquivo);
		// querys.append("', INTERVAL ");
		// querys.append(intervalo);
		// querys.append(" MINUTE))  from consultas_sensor_historico where data > (DATE_ADD('");
		// querys.append(data_do_arquivo);
		// querys.append("', INTERVAL 0 MINUTE)) AND data <= (DATE_ADD('");
		// querys.append(data_do_arquivo);
		// querys.append("', INTERVAL ");
		// querys.append(intervalo);
		// querys.append(" MINUTE)) AND fk_sensor = ");
		// querys.append(fk_sensor);


#ifdef COMENTARIOS
		printf("\n\nQuery = ");
		cout << querys;
		printf("\n\n");
#endif

		result = bd_sensor->manda_query_insert(querys);
		
		querys="SELECT DATE_ADD('"; querys.append(data_do_arquivo);	querys.append("', INTERVAL ");
		querys.append(intervalo);
		querys.append(" MINUTE)");
		resp = bd_sensor->manda_query_consulta(querys);
		linhas=mysql_fetch_row(resp);
		strcpy(data_do_arquivo,linhas[0]);

#ifdef COMENTARIOS
		printf("%s ---> ",data_do_arquivo);
#endif

		querys="SELECT DATE_FORMAT('"; 	querys.append(data_do_arquivo);	querys.append("', '%Y-%m-%d %H:%i:%s');");
		resp = bd_sensor->manda_query_consulta(querys);
		linhas=mysql_fetch_row(resp);
		strcpy(hora,linhas[0]);

#ifdef COMENTARIOS
		printf("hora: %s \n",hora);
#endif
	}


	while(strcmp(data_maxima,hora))
	{
		querys = "insert into ";
		querys.append(nome_tabela);
		querys.append(" (`fk_sensor`, `fk_perfil` , `valor_lido`, `data`)  (select fk_sensor, fk_perfil, ROUND(avg(valor_lido),2), (DATE_ADD('");
		querys.append(data_do_arquivo);
		querys.append("', INTERVAL ");
		querys.append(intervalo);
		querys.append(" MINUTE))  from consultas_sensor_historico as x where data > (DATE_ADD('");
		querys.append(data_do_arquivo);
		querys.append("', INTERVAL 0 MINUTE)) AND data <= (DATE_ADD('");
		querys.append(data_do_arquivo);
		querys.append("', INTERVAL ");
		querys.append(intervalo);
		querys.append(" MINUTE)) AND x.fk_sensor = fk_sensor AND x.fk_perfil = fk_perfil AND fk_sensor = ");
		querys.append(fk_sensor);
		querys.append(" AND fk_perfil = ");
		querys.append(fk_perfil);
		querys.append(" );");

#ifdef COMENTARIOS
		printf("\n\nQuery = ");
		cout << querys;
		printf("\n\n");
#endif

		result = bd_sensor->manda_query_insert(querys);

		querys="SELECT DATE_ADD('"; querys.append(data_do_arquivo);	querys.append("', INTERVAL ");
		querys.append(intervalo);
		querys.append(" MINUTE)");
		resp = bd_sensor->manda_query_consulta(querys);
		linhas=mysql_fetch_row(resp);
		strcpy(data_do_arquivo,linhas[0]);

#ifdef COMENTARIOS
		printf("%s ---> ",data_do_arquivo);
#endif

		//querys="SELECT DATE_FORMAT('"; 	querys.append(data_do_arquivo);	querys.append("', '%H:%i:%s');");
		querys="SELECT DATE_FORMAT('"; 	querys.append(data_do_arquivo);	querys.append("', '%Y-%m-%d %H:%i:%s');");
		resp = bd_sensor->manda_query_consulta(querys);
		linhas=mysql_fetch_row(resp);
		strcpy(hora,linhas[0]);

#ifdef COMENTARIOS
		printf("hora: %s \n",hora);
		printf("data_max: %s \n",data_maxima);
		printf("[dentro] strcmp --> %d\n", strcmp(data_maxima,hora));
#endif
	}



	arq_com_data = fopen (nome_arquivo , "w+");
	if (arq_com_data != NULL)
	{
		fprintf(arq_com_data,"%s",data_do_arquivo);
		printf("--> Ultima data = %s\n\n",data_do_arquivo);
#ifdef COMENTARIOS
		printf("Arquivo atualizado - %s\n\n", nome_arquivo);
#endif
	}
	else
	{
#ifdef COMENTARIOS
		printf("Erro ao criar/abrir o arquivo - %s\n", nome_arquivo);
#endif
	}


	// strcpy(qtd_dias_dados_no_banco, "2"); // deixa os dados por X dias guardados no BD
	// remove_velhos_valores(qtd_dias_dados_no_banco,nome_tabela, data_do_arquivo, fk_sensor);

	fclose(arq_com_data);
	delete(bd_sensor);
}





int main(int argc, char *argv[])
{
	char intervalo[3];
	string querys;
	char base_dados[50];
	strcpy(base_dados,"azul_consolidado");
	banco_sensor *bd_sensor = new banco_sensor(base_dados);
	MYSQL_RES *resp;
	MYSQL_ROW linhas;
	MYSQL_FIELD *campos;

// 	if(strcmp(argv[1],"10") && strcmp(argv[1],"60"))
// 	{
// #ifdef COMENTARIOS
// 		printf("Intervalo inadequado!!\n\n");
// #endif
// 		exit(1);
// 	}
	
	querys="SELECT fk_sensor, fk_perfil FROM consultas_sensor WHERE 1 order by fk_sensor";
	cout << querys;
	resp = bd_sensor->manda_query_consulta(querys);
	
	// strcpy(intervalo,argv[1]);
	strcpy(intervalo,"10");

#ifdef COMENTARIOS
	printf("Intervalo = %s\n",intervalo);
#endif

	while ((linhas=mysql_fetch_row(resp)) != NULL)
	{
		consolida_valores(intervalo,linhas[0], linhas[1]);
	}
	delete(bd_sensor);
}
