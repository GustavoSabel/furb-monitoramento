<?php ob_start(); ?>

<script>
$(function(){
	$("#cadastro").submit(function( event ) {
		$("#status").html("");

		var dados = {};
		dados["operacao"] = "cadastrar";
		dados["macaddress"] = $("#macaddress").val();
		dados["localizacao"] = $("#localizacao").val();
		dados["observacao"] = $("#observacao").val();
		var dadosJson = JSON.stringify(dados);
		
		$.post(path_cadastro, dadosJson, function (data){
			showStatus(data);
			atualizarGrid();
		}, 'json');
		event.preventDefault();
	});

	atualizarGrid();
});

function showStatus(data) {
	$("#status").html(data.mensagem);
}

function remover(id) {
	var dados = {};
	dados["operacao"] = "excluir";
	dados["id"] = id;
	var dadosJson = JSON.stringify(dados);
	console.log(dadosJson);
	$.post(path_cadastro, dadosJson, function (data){
		showStatus(data);
		atualizarGrid();
	}, 'json');
}

function montarBotaoRemover(id) {
	return "<button class='comando excluir' onClick='remover(\"" + id + "\")'>Remover</button>";
}

function atualizarGrid() {
	var dados = JSON.stringify({operacao:"buscar"});
	console.log(dados);
	$.post(path_cadastro, dados, function (data) {
		console.log(data);

		$(".dispositivo").remove();
		var linhas = "";
		data.forEach(function(row) {
			linhas += "<tr class='dispositivo' id='"+ row[0] +"'>";
			linhas += "<td>" + montarBotaoRemover(row[0]) + "</td>";
			linhas += "<td>" + row[1] + "</td>";
			linhas += "<td>" + row[2] + "</td>";
			linhas += "<td>" + row[3] + "</td>";
			linhas += "</tr>";
		});

		$("#DispositivosCadastrados").append(linhas);
	}, 'json');
}
</script>

<div>
	<form id="cadastro" action="Teste.php" method="post">
		Mac Address: <br> <input type="text" name="macaddress"
			id="macaddress" autocomplete="on"> <br> Localização: <br> <input
			type="text" name="localizacao" id="localizacao" autocomplete="off">
		<br> Observação: <br> <input type="text" name="observacao"
			id="observacao" autocomplete="off"> <br> <input type="submit"
			Value="Cadastrar" />
	</form>
	<div id="status"></div>
</div>
<br>
<table id="DispositivosCadastrados">
	<tr>
		<th>Comandos</th>
		<th>Mac Addrress</th>
		<th>Localização</th>
		<th>Observacao</th>
	</tr>
</table>
<?php
	$pagemaincontent = ob_get_contents();
	ob_end_clean();
	$titulo = "Cadastro manual";
	include("master.php");
?>
