
using namespace std;

class banco_sensor
{
	MYSQL conexao;
	MYSQL_RES *resp;
	MYSQL_ROW linhas;
	// MYSQL_FIELD *campos;

	char hostname[20];
	char usuario[20];
	char senha[20];
	char base_dados[50];
public:
	banco_sensor(void);
	banco_sensor(char base[50]);
	void banco_sensor_cfg(char nome_host[50], char usuario[50], char senha[10], char base_dados[20]);
	~banco_sensor(void);
	void conectar();
	void desconectar();
	MYSQL_RES * manda_query_consulta(string query_enviar);
	MYSQL_RES * manda_query_consulta_sem_conect(string query_enviar);
	int manda_query_acao(string query_enviar, char *nome_tabela, char *pk_nome, char *pk_valor);
	int manda_query_insert(string query_enviar);
};

