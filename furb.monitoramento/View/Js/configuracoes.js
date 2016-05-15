$(function() {
	$("#configuracoes").submit(function(event) {
		$("#status").html("");

		var dados = {};
		dados["operacao"] = "atualizar";
		dados["login"] = $("#login").val();
		dados["senha"] = $("#senha").val();
		dados["tempo"] = $("#tempo").val();
		var dadosJson = JSON.stringify(dados);

		$.post(path_configuracoes, dadosJson, function(data) {
			exibirMensagem(data.mensagem, data.status);
			//atualizarGrid();
		}, 'json');
		event.preventDefault();
	});
});