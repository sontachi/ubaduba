//Para transformar int em string (Tentei de outras formas, essa foi uma gambiarra que achei na net, se vc conseguir me ajudar com isso, good =))
string int_string(int number){
    string number_string = "";
    char ones_char;
    int ones = 0, negativo = 0;

    if(number < 0){
    	negativo = 1;
    	number = -number;
    }

    while(true){
        ones = number %	 10;
        switch(ones){
            case 0: ones_char = '0'; break;
            case 1: ones_char = '1'; break;
            case 2: ones_char = '2'; break;
            case 3: ones_char = '3'; break;
            case 4: ones_char = '4'; break;
            case 5: ones_char = '5'; break;
            case 6: ones_char = '6'; break;
            case 7: ones_char = '7'; break;
            case 8: ones_char = '8'; break;
            case 9: ones_char = '9'; break;
        }
        number -= ones;
        number_string = ones_char + number_string;
        if(number == 0){
            break;
        }
        number = number/10;
    }
    if(negativo == 1)number_string = '-' + number_string;
    return number_string;
}

string float_string(float valor){
	string resultado;
	// ex 10.25
	resultado = int_string((int)valor); // COLOCA 10 NA STRING
	resultado.append("."); // CONCATENA COM .

	if(valor < 0)valor = -valor;
	
	int aux = (int)((valor - (float)((int)valor))*100);
	resultado.append(int_string(aux)); // FAZ 10.25 - 10 = 0.25, e 0.25 * 100 = 25 e concatena.

	/*cout << "RESULTADO = ";
	cout << resultado;*/
	return resultado;
}

char* itoa(int value, char* result, int base) {
	// check that the base if valid
	if (base < 2 || base > 36) { *result = '\0'; return result; }

	char* ptr = result, *ptr1 = result, tmp_char;
	int tmp_value;

	do {
		tmp_value = value;
		value /= base;
		*ptr++ = "zyxwvutsrqponmlkjihgfedcba9876543210123456789abcdefghijklmnopqrstuvwxyz" [35 + (tmp_value - value * base)];
	} while ( value );

	// Apply negative sign
	if (tmp_value < 0) *ptr++ = '-';
	*ptr-- = '\0';
	while(ptr1 < ptr) {
		tmp_char = *ptr;
		*ptr--= *ptr1;
		*ptr1++ = tmp_char;
	}
	return result;
}

void envia_alerta(int modulo, int erro, int qtd_erros_parcial, int qtd_parcial, int qtd_erros_total, int qtd_total, int num_no){
	
}

lista_alerta_def * busca(int x, lista_alerta_def *inicio)
{
   if (inicio == NULL) 
      return NULL;
   if (inicio->id == x) 
      return inicio;
   return busca(x, inicio->prox);
}

lista_alerta_def * insereLista(int x, lista_alerta_def **inicio){
	lista_alerta_def *novo = (lista_alerta_def*)malloc(sizeof(lista_alerta_def));
	novo->id = x;
	novo->qtd_erros_parcial = 0;
	novo->qtd_parcial = 0;
	novo->qtd_erros_total = 0;
	novo->qtd_total = 0;
	novo->prox = NULL;

	if(*inicio == NULL)
	{
		*inicio = novo;
	}
	else
	{
		lista_alerta_def *aux = *inicio;
	    while(aux->prox != NULL)aux = aux->prox;
	    aux->prox = novo;
	}    
	return novo;
}

void imprimirLista(lista_alerta_def * paramList)  
{  
     
  while(paramList != NULL)//enquanto nao é o final da lista leia e imprima o conteudo  
  {   
     printf("[No %2d] %d - %d  / %ld - %d\n", paramList->id, paramList->qtd_parcial, paramList->qtd_erros_parcial, paramList->qtd_total, paramList->qtd_erros_total);//imprimindo o valor    
     paramList = paramList->prox; //apontando a lista a proxxima lista     
     // printf("\n");           
  }                     
  printf("\n\n");
}      
