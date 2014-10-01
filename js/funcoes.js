function BuscaItemTabela(objeto)
{
	var nome = $(objeto).val().toLowerCase();
	
	$.each($('table tbody tr'), function(index, element) {
        if($(element).html().toLowerCase().indexOf(nome) == -1)
            $(element).hide();
        else
            $(element).show();
    });
}


var array_produtos = new Array();


function somaValoresCombo()
{
	var soma_kcal = soma_proteinas = soma_carboidratos = soma_gorduras = soma_colesterol = soma_fibras = soma_sodio = parseFloat(0);

	$(array_produtos).each(function (index, element) {
		if(element.pk_produto-1000000 > 0){
			
			soma_kcal = soma_kcal + parseFloat(element.kcal);
			soma_proteinas = soma_proteinas + parseFloat(element.proteinas);
			soma_carboidratos = soma_carboidratos + parseFloat(element.carboidratos);
			soma_gorduras = soma_gorduras + parseFloat(element.gorduras);
			soma_colesterol = soma_colesterol + parseFloat(element.colesterol);
			soma_fibras = soma_fibras + parseFloat(element.fibras);
			soma_sodio = soma_sodio + parseFloat(element.sodio); 
		}
		else{
			element.qtde = $('#produto_combo_'+element.pk_produto).val();
			soma_kcal = soma_kcal + (element.kcal * element.gramas * element.qtde / 100);
			soma_proteinas = soma_proteinas + (element.proteinas * element.gramas * element.qtde / 100);
			soma_carboidratos = soma_carboidratos + (element.carboidratos * element.gramas * element.qtde / 100);
			soma_gorduras = soma_gorduras + (element.gorduras * element.gramas * element.qtde / 100);
			soma_colesterol = soma_colesterol + (element.colesterol * element.gramas * element.qtde / 100);
			soma_fibras = soma_fibras + (element.fibras * element.gramas * element.qtde / 100);
			soma_sodio = soma_sodio + (element.sodio * element.gramas * element.qtde / 100);
		}
	});

	$('#valor_kcal').html(soma_kcal);
	$('#valor_proteinas').html(soma_proteinas);
	$('#valor_carboidratos').html(soma_carboidratos);
	$('#valor_gorduras').html(soma_gorduras);
	$('#valor_colesterol').html(soma_colesterol);
	$('#valor_fibras').html(soma_fibras);
	$('#valor_sodio').html(soma_sodio);
}



function insereProdutoCombo(id, nome, medida, gramas, prod_kcal, prod_proteinas, prod_carboidratos, prod_gorduras, prod_colesterol, prod_fibras, prod_sodio)
{
	array_produtos.push({
		pk_produto : id,
		nome : nome,
		medida : medida,
		gramas : gramas,
		qtde : 0,
		kcal : prod_kcal,
		proteinas : prod_proteinas,
		carboidratos : prod_carboidratos,
		gorduras : prod_gorduras,
		colesterol : prod_colesterol,
		fibras : prod_fibras,
		sodio : prod_sodio
	});

	$('#lista_combo').append('<tr id="itemCombo_' + id + '">'+
								'<td>' + nome + '</td>'+
								'<td><input type="hidden" name="incluirProduto[]" value="'+id+'" >'+
								    '<input type="text"  id="produto_combo_'+id+'" class = "campoQtd input-mini" name = "'+id +'" data-mask="9?9999999" value="0" class="input-mini" maxlengh="10" height="10px" onkeyup="somaValoresCombo('+prod_kcal+');"> '+ medida +' ('+ gramas +'g)'+
								'</td>'+
								'<td><a class="btn btn-danger btn-small" onClick="removeProdutoCombo('+id+');"><i class="icon-remove"></i></td>' +
							'</tr>');

	somaValoresCombo();

	$('#item_' + id).hide();
}

function insereCardapio(id, nome, medida, gramas, prod_kcal, prod_proteinas, prod_carboidratos, prod_gorduras, prod_colesterol, prod_fibras, prod_sodio)
{
	array_produtos.push({
		pk_produto : id,
		nome : nome,
		medida : medida,
		gramas : gramas,
		qtde : 0,
		kcal : prod_kcal,
		proteinas : prod_proteinas,
		carboidratos : prod_carboidratos,
		gorduras : prod_gorduras,
		colesterol : prod_colesterol,
		fibras : prod_fibras,
		sodio : prod_sodio
	});
	if( (id-1000000) >0){
		$('#lista_combo').append('<tr id="itemCombo_' + id + '">'+
									'<td><input type="text" class="input-xmini" name="num_comb_'+id+'" value="0" data-mask="9?9">'+
									'<td>' + nome + '(Combo)</td>'+
									'<td><input type="hidden" name="incluirProduto[]" value="'+id+'" >'+
									'1</td>'+
									'<td><a class="btn btn-danger btn-small" onClick="removeProdutoCombo('+id+');"><i class="icon-remove"></i></td>' +
								'</tr>');
		
	}else{
		if(medida == "Líquido")linha_quantidade = '<input type="text" id="produto_combo_'+id+'" class = "input-mini campoQtd" name = "'+id+'" data-mask="9?999999" value = "0"  maxlengh="6" height="10px" onkeyup="somaValoresCombo('+prod_kcal+');"> ml';
		else linha_quantidade= '<input type="text" id="produto_combo_'+id+'" class = "input-mini campoQtd" name = "'+id+'" data-mask="9?999999" value = "0"  maxlengh="6" height="10px" onkeyup="somaValoresCombo('+prod_kcal+');"> '+ medida +' ('+ gramas +'g)'
		
		$('#lista_combo').append('<tr id="itemCombo_' + id + '">'+
									'<td><input type="text" class="input-xmini" name="num_comb_'+id+'" value="0" data-mask="9?9">'+
									'<td>' + nome + '</td>'+
									'<td><input type="hidden" name="incluirProduto[]" value="'+id+'" >'+
									linha_quantidade +
									'</td>'+
									'<td><a class="btn btn-danger btn-small" onClick="removeProdutoCombo('+id+');"><i class="icon-remove"></i></td>' +
								'</tr>');
	}
	

	somaValoresCombo();

	$('#item_' + id).hide();
}



function removeProdutoCombo(id_produto)
{
	var posicao=0;
	$(array_produtos).each(function (index, element) {
		if(id_produto === element.pk_produto)posicao = index;
	});

	array_produtos.splice(posicao,1);

	$('#itemCombo_'+ id_produto).remove();
	$('#item_'+id_produto).show();
	somaValoresCombo();
}

function infoMedida(objeto)
{
	var texto;
	texto = $(objeto).children("option[value='"+$(objeto).val()+"']").attr('medida');
	$('#medida_prod').html(texto);
}