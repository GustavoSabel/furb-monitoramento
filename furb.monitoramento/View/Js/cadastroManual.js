var dispositivos = {};
var editando = false;

$(function() {

	ativarModoCadastro();
	
	$("#formCadastro").submit(function(event) {
		$("#status").html("");

		var dados = {};
		if(!editando) {
			dados["operacao"] = "cadastrar";
		} else {
			dados["operacao"] = "editar";
		}
		dados["macaddress"] = $("#macaddress").val();
		dados["localizacao"] = $("#localizacao").val();
		dados["observacao"] = $("#observacao").val();
		var dadosJson = JSON.stringify(dados);

		$.post(path_cadastro, dadosJson, function(data) {
			exibirMensagem(data.mensagem, data.status);
			if(data.status == 1) {
				limpar()
			}
			atualizarGrid();
		}, 'json');
		
		
		event.preventDefault();
	});

	atualizarGrid();
});

function limpar() {
	ativarModoCadastro();
	$("#macaddress").val("");
	$("#localizacao").val("");
	$("#observacao").val("");
}

function remover(id) {
	var dados = {};
	dados["operacao"] = "excluir";
	dados["id"] = id;
	var dadosJson = JSON.stringify(dados);
	console.log(dadosJson);
	$.post(path_cadastro, dadosJson, function(data) {
		exibirMensagem(data.mensagem, data.status);
		atualizarGrid();
	}, 'json');
}

function editar(id) {
	ativarModoEditao();
	
	dispositivos.forEach(function(row) {
		if(row[0] == id) {
			$("#macaddress").val(row[1]);
			$("#localizacao").val(row[2]);
			$("#observacao").val(row[3]);
		}
	});
}

function ativarModoEditao() {
	editando = true;
	$("#macaddress").prop('readonly', true);
	$("#btnCadastrar").html("Gravar alteração");
}

function ativarModoCadastro(){
	editando = false;
	$("#macaddress").prop('readonly', false);
	$("#btnCadastrar").html("Cadastrar");
}

function montarBotaoRemover(id) {
	return "<button class='comando excluir' onClick='remover(\"" + id + "\")'>Remover</button>";
}

function montarBotaoEditar(id) {
	return "<button class='comando editar' onClick='editar(\"" + id + "\")'>Editar</button>";
}

function atualizarGrid() {
	var dados = JSON.stringify({
		operacao : "buscar"
	});
	console.log(dados);
	$.post(path_cadastro, dados, function(data) {
		console.log(data);
		dispositivos = data;

		$(".dispositivo").remove();
		var linhas = "";
		data.forEach(function(row) {
			linhas += "<tr class='dispositivo' id='" + row[0] + "'>";
			linhas += "<td>" + montarBotaoRemover(row[0]) + montarBotaoEditar(row[0]) + "</td>";
			linhas += "<td>" + row[1] + "</td>";
			linhas += "<td>" + row[2] + "</td>";
			linhas += "<td>" + row[3] + "</td>";
			linhas += "</tr>";
		});

		$("#DispositivosCadastrados").append(linhas);
	}, 'json');
}