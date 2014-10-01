#include "sensor_header.h"

// #define COMENTARIOS_M1
// #define COMENTARIOS_M1_SOCKET
// #define COMENTARIOS_M1_VALORES_SENSORES

#define INTERVALO_VERIFICACAO_SENSORES 700000
#define NUM_TENTATIVAS 5
/*
 * Variaveis globais para o socket
 */
struct hostent *he;


/*
 * Funcoes genericas usadas entre m2 e m1
 * envia_pacote_modulo3, int_string, float_string, monta_pacote, entre outras
 */

#include "funcoes_m1_m2.cpp"


bool verifica_parametros(int argc, char *argv[])
{
	/*
	* Verifica a quantidade de parametros passados via linha de comando
	*/
	if (argc != 2) 
	{
			fprintf(stderr,"usage: ./m2 <ip do servidor>\n");
			return false;
	}
        
	/*
	* Converte o IP do servidor
	*/
	if ((he=gethostbyname(argv[1])) == NULL) /* pegando informacoes do host */
	{
			perror("gethostbyname");
			return false;
	}
	return true;
}

int verifica_professor (int pacote[], int pk_turma){
	banco_sensor *bd_sensor = new banco_sensor();
	string querys;
	MYSQL_RES *resp;

	int num_rows;

	querys = "SELECT p.rp FROM professores p INNER JOIN turmas t ON p.rp = t.rp INNER JOIN tags ON p.rp = usuario AND tipo = 2 WHERE pk_turma = ";
	querys.append(int_string(pk_turma));
	querys.append(" AND p35 = ");
	querys.append(int_string(pacote[35]));
	querys.append(" AND p36 = ");
	querys.append(int_string(pacote[36]));
	querys.append(" AND p37 = ");
	querys.append(int_string(pacote[37]));
	querys.append(" AND p38 = ");
	querys.append(int_string(pacote[38]));
	querys.append(" AND p39 = ");
	querys.append(int_string(pacote[39]));
	querys.append(" AND p40 = ");
	querys.append(int_string(pacote[40]));
	querys.append(" AND p41 = ");
	querys.append(int_string(pacote[41]));
	querys.append(" AND p42 = ");
	querys.append(int_string(pacote[42]));
	querys.append(" AND p43 = ");
	querys.append(int_string(pacote[43]));
	querys.append(" AND p44 = ");
	querys.append(int_string(pacote[44]));

	resp = bd_sensor->manda_query_consulta(querys);

	if(resp)
	{	
		num_rows = mysql_num_rows(resp);

		if(num_rows>0) return 1;
	}
	return 0;

}


void atualiza_flag(int pk_horario, int flag){
	banco_sensor *bd_sensor = new banco_sensor;
	MYSQL_RES *resp;
	MYSQL_ROW linhas;
	string query;
	int result;


	query = "UPDATE horarios SET flag =";
	query.append(int_string(flag));
	query.append(" WHERE pk_horario = ");
	query.append(int_string(pk_horario));

	result = bd_sensor->manda_query_insert(query);

}

int check_aluno(int pacote[], int pk_turma, int pk_horario, int flag_check){
	banco_sensor *bd_sensor = new banco_sensor();
	string querys;
	MYSQL_RES *resp;
	MYSQL_ROW linhas;
	int result, num_rows;

	querys = "SELECT a.ra FROM alunos a INNER JOIN rel_turmas_alunos t ON a.ra = t.ra INNER JOIN tags ON a.ra = usuario AND tipo = 1 WHERE fk_turma = ";
	querys.append(int_string(pk_turma));
	querys.append(" AND p35 = ");
	querys.append(int_string(pacote[35]));
	querys.append(" AND p36 = ");
	querys.append(int_string(pacote[36]));
	querys.append(" AND p37 = ");
	querys.append(int_string(pacote[37]));
	querys.append(" AND p38 = ");
	querys.append(int_string(pacote[38]));
	querys.append(" AND p39 = ");
	querys.append(int_string(pacote[39]));
	querys.append(" AND p40 = ");
	querys.append(int_string(pacote[40]));
	querys.append(" AND p41 = ");
	querys.append(int_string(pacote[41]));
	querys.append(" AND p42 = ");
	querys.append(int_string(pacote[42]));
	querys.append(" AND p43 = ");
	querys.append(int_string(pacote[43]));
	querys.append(" AND p44 = ");
	querys.append(int_string(pacote[44]));

	resp = bd_sensor->manda_query_consulta(querys);

	if(resp)
	{	
		num_rows = mysql_num_rows(resp);

		if(num_rows>0){
			linhas=mysql_fetch_row(resp);

			if(flag_check == 0)
			{
				querys = "INSERT INTO presentes (fk_horario, ra, flag) VALUES (";
				querys.append(int_string (pk_horario));
				querys.append(", '");
				querys.append(linhas[0]);
				querys.append("', 0)");
				
			}

			else
			{
				querys = "UPDATE presentes set flag = 1 WHERE fk_horario = ";
				querys.append(int_string (pk_horario));
				querys.append(" AND ra = '");
				querys.append(linhas[0]);
				querys.append("' ");
			}

			result = bd_sensor->manda_query_insert(querys);

			return 1;
		}
	}
	return 0;
}

void acende_apaga_verde(int id_leitor, int tipo){
	// TIPO = 1 -> SLEEP = 2, ALUNO ENTRANDO
	// TIPO = 2 -> SLEEP = 1 2 VEZES, ALUNO SAINDO 
	// TIPO = 3 -> SLEEP = 0.5 3 VEZES, PROFESSOR LIBERANDO ENTRADA
	// TIPO = 4 -> SLEEP = 2 1 VEZES, PROFESSOR LIBERANDO SAIDA
	// TIPO = 5 -> SLEEP = 0.5 3 VEZES, PROFESSOR FECHANDO SAIDA


	int pkg_enviar[52], *pkg_receber, time_sleep, qtd;

	if(tipo == 1){
		time_sleep = 2;
		qtd = 1;
	}
	else if(tipo == 2){
		time_sleep = 0.5;
		qtd = 2;
	}
	else if(tipo == 3){
		time_sleep = 0.5;
		qtd = 3;
	}
	else if(tipo == 4){
		time_sleep = 1;
		qtd = 2;
	}
	else if(tipo == 5){
		time_sleep = 0.5;
		qtd = 3;
	}

	for(int i = 0; i < qtd*2; i++){
		//Acende a luz verde
		for(int j = 0; j < TAM_PKG; j++)pkg_enviar[j] = 0;
		pkg_enviar[8] = id_leitor;
		pkg_enviar[50] = 1;
		pkg_receber = envia_pacote_modulo_03(pkg_enviar);

		sleep(time_sleep);
	}

}

string converte(int pacote_resposta[], int pos){
	
	float adh = (float)pacote_resposta[pos+1];
	float adl = (float)pacote_resposta[pos+2];

	float AD, Vout, valor;

	AD = (adh * 256 + adl);
	Vout = 0.003223 * AD;
	valor = (Vout*100);

	return float_string(valor);		
}

void atualiza_valores_sensor(int sensores[][2], int pacote[]){
	banco_sensor *bd_sensor = new banco_sensor;
	MYSQL_RES *resp;
	MYSQL_ROW linhas;
	string query, valor_0, valor_1;
	int result;

	if(sensores[0][1] == 1){
		valor_0 = converte(pacote, 19);
	}
	else{
		valor_0 = converte(pacote, 16);
	}

	if(sensores[1][1] == 1){
		valor_1 = converte(pacote, 19);
	}
	else{
		valor_1 = converte(pacote, 16);
	}

	printf("\n");

	query = "INSERT INTO consultas_sensor_historico (pk_consulta, fk_sensor, valor_lido, data) VALUES (NULL,";
	query.append(int_string(sensores[0][0]));
	query.append(",");
	query.append(valor_0);
	query.append(", CURRENT_TIMESTAMP)");
	result = bd_sensor->manda_query_insert(query);

	query = "INSERT INTO consultas_sensor_historico (pk_consulta, fk_sensor, valor_lido, data) VALUES (NULL,";
	query.append(int_string(sensores[1][0]));
	query.append(",");
	query.append(valor_1);
	query.append(", CURRENT_TIMESTAMP)");
	result = bd_sensor->manda_query_insert(query);
}

void teste(){
	banco_sensor *bd_sensor = new banco_sensor();
	string querys;
	MYSQL_RES *resp, *resp_sensor;
	MYSQL_ROW linhas,linhas_sensor;
	int pkg_enviar[TAM_PKG], *pkg_receber, i, pausa = 0, num_rows, num_rows_sensor;
	int pk_horario, flag, pode_entrar, fk_turma, id_leitor;
	int sensores[2][2];
	int flag_ok = 0;

	while(1)
	{
		querys="SELECT pk_horario, flag, fk_turma, id_leitor, CASE WHEN date_add(data_inicio_real, interval 20 minute) > NOW() AND flag = 1 THEN '1' WHEN date_add(data_inicio_real, interval 20 minute) > NOW() AND flag = 1 THEN '2' ELSE '0' END AS pode_entrar FROM horarios INNER JOIN salas ON fk_sala = pk_sala WHERE data_inicio <= NOW() and data_fim > NOW() AND flag != 3";
		resp = bd_sensor->manda_query_consulta(querys);
		i = 0;
		cout << querys;
		if(resp)
		{	
			num_rows = mysql_num_rows(resp);
			if(num_rows>0)
			{
		        while ((linhas=mysql_fetch_row(resp)) != NULL)
		        {
		        	flag_ok = 0;

		        	pk_horario = atoi(linhas[0]);
					flag = atoi(linhas[1]);
					fk_turma = atoi(linhas[2]);
					id_leitor = atoi(linhas[3]);
					pode_entrar = atoi(linhas[4]);

					for(int k = 0; k < NUM_TENTATIVAS; k++)
					{
						for(int j = 0; j < TAM_PKG; j++)pkg_enviar[j] = 0;
						pkg_enviar[8] = id_leitor;

						pkg_receber = envia_pacote_modulo_03(pkg_enviar);

						if(pkg_receber[0] != -9)
						{
							break;
							flag_ok = 1;
						}
					}

					if(flag_ok = 1)
					{

						querys = "SELECT sen.pk_sensor, sen.fk_sensor_tipo FROM sensores AS sen INNER JOIN salas s ON sen.fk_sala = s.pk_sala INNER JOIN consultas_sensor AS con ON con.fk_sensor = sen.pk_sensor WHERE sen.excluido = 0 AND TIME_TO_SEC(TIMEDIFF(NOW(), con.data)) >= (sen.intervalo_consulta) AND s.id_leitor = ";
						querys.append(int_string(id_leitor));
						querys.append(" ORDER BY pk_sala");
						resp_sensor = bd_sensor->manda_query_consulta(querys);

						num_rows_sensor = mysql_num_rows(resp_sensor);
						if(num_rows_sensor > 0)
						{
							i = 0;
							while(linhas_sensor = mysql_fetch_row(resp_sensor))
							{
								sensores[i][0] = atoi(linhas_sensor[0]);
								sensores[i][1] = atoi(linhas_sensor[1]);
								i++;
							}

							atualiza_valores_sensor(sensores, pkg_receber);
						}


						if(pode_entrar==2) atualiza_flag(pk_horario, 2);

						if(flag == 0)
						{
							if(verifica_professor(pkg_receber, fk_turma) == 1)
							{
								printf("PROFESSOR FLAG AGORA É 1\n");
								atualiza_flag(pk_horario, 1);

								acende_apaga_verde(id_leitor,3);

							}
						}
						else if(flag==1 && pode_entrar ==1)
						{
							printf("LEITURA ALUNO\n");
							if(check_aluno(pkg_receber, fk_turma, pk_horario,0 )==1){
								acende_apaga_verde(id_leitor, 1);
							} 
						}
						else if(flag == 1 && pode_entrar == 0)
						{
							if(verifica_professor(pkg_receber, fk_turma) == 1)
							{
								printf("PROFESSOR LIBERA SAIDA -> FLAG = 2\n");
								acende_apaga_verde(id_leitor, 4);
								atualiza_flag(pk_horario, 2);
							} 
						}
						else if(flag==2)
						{
							if(check_aluno(pkg_receber, fk_turma, pk_horario, 1) == 1)
							{
								acende_apaga_verde(id_leitor, 2);
							} 
							else
							{
								if(verifica_professor(pkg_receber, fk_turma) == 1)
								{
									acende_apaga_verde(id_leitor, 5);

									atualiza_flag(pk_horario, 3);
								}
							}
						}
					}
				}
			}
			else 
			{
				for(int k = 0; k < NUM_TENTATIVAS; k++)
				{
					for(int j = 0; j < TAM_PKG; j++)pkg_enviar[j] = 0;
					pkg_enviar[8] = 1;
					//SEMPRE OLHA PARA O ID 1
					pkg_receber = envia_pacote_modulo_03(pkg_enviar);

					if(pkg_receber[0] != -9)
					{
						break;
						flag_ok = 1;
					}
				}				
				
				if(flag_ok = 1)
				{
					querys = "SELECT sen.pk_sensor, sen.fk_sensor_tipo FROM sensores AS sen INNER JOIN salas s ON sen.fk_sala = s.pk_sala INNER JOIN consultas_sensor AS con ON con.fk_sensor = sen.pk_sensor WHERE sen.excluido = 0 AND TIME_TO_SEC(TIMEDIFF(NOW(), con.data)) >= (sen.intervalo_consulta) AND s.id_leitor = 1 ORDER BY pk_sala";
					cout << querys;

					resp_sensor = bd_sensor->manda_query_consulta(querys);

					num_rows_sensor = mysql_num_rows(resp_sensor);

					if(num_rows_sensor > 0)
					{
						i = 0;
						while(linhas_sensor = mysql_fetch_row(resp_sensor))
						{	
							sensores[i][0] = atoi(linhas_sensor[0]);
							sensores[i][1] = atoi(linhas_sensor[1]);
							printf("\n\n%d %d\n", i, sensores[i][0]);
							i++;
						}

						atualiza_valores_sensor(sensores, pkg_receber);
					}
				}
			}
		}	
		usleep(250000);
		printf("\n\n");
    }

	// i = 0;
	// while(1)
	// {
	// 	pausa = 0;
	// 	// printf("\nEnviando o pacote numero %d\n",i);
	// 	for(int j = 0; j < TAM_PKG; j++)
	// 		pkg_enviar[j] = 0;

	// 	pkg_enviar[8] = 1;

	// 	pkg_receber = envia_pacote_modulo_03(pkg_enviar);

	// 	if(pkg_receber[0] > 0){
	// 		for(int j = 0; j < TAM_PKG; j++)
	// 		{
	// 			printf("%d ", pkg_receber[j]);
	// 		}
		
	// 		printf("Aperte %d\n", i);
	// 		getchar();
	// 	}
	// 	i++;
	// 	// sleep(1000000);
	// }
}

int main(int argc, char *argv[])
{
	if(!verifica_parametros(argc,argv))
	{
		printf("Verifica Nok\n\n");
		exit(1);
	}
	else
	{
		printf("Verifica ok\n\n");
	}

	// verifica_sensores();
	teste();
	// funcao_pisca_pisca();
	// printf("AAAAAAAA\n");

	return 0;
}