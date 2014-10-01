
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


var array_alunos = new Array();


function somaQtdAlunos()
{
	var somaQtd = parseFloat(0);

	$(array_alunos).each(function (index, element) {
			somaQtd++;
	});
	$('#qtd_alunos').html(somaQtd);
}



function insereAlunoTurma(id, nome)
{
	//Verifica se o aluno já está na turma;
	var result = $.grep(array_alunos, function(e){ return e.ra == id; });

	//Se o aluno não existe na turma adiciona
	if(result.length == 0)
	{
		array_alunos.push({
			ra : id,
			nome : nome
		});

		$('#lista_combo').append('<tr id="itemCombo_' + id + '">'+
									'<td>' + id + '</td>'+
									'<td>' + nome + '</td>'+
									'<td><input type="hidden" name="incluirAluno[]" value="'+id+'" >'+
									'</td>'+
									'<td><a class="btn btn-danger btn-small" onClick="removeAlunoTurma('+id+');"><i class="icon-remove"></i></td>' +
								'</tr>');

		somaQtdAlunos();
	}
	console.log("AAA");
	$('#item_' + id).hide();
}



function removeAlunoTurma(id_produto)
{
	var posicao=0;
	$(array_alunos).each(function (index, element) {
		if(id_produto === element.pk_produto)posicao = index;
	});

	array_alunos.splice(posicao,1);

	$('#itemCombo_'+ id_produto).remove();
	$('#item_'+id_produto).show();
	somaQtdAlunos();
}
