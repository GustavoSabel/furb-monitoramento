<?php ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
	$configJson = json_encode($configuracao);
?>

<script>
var configuracoes = JSON.parse('<?php echo $configJson; ?>');

var contador = 0;
var total = 0;

$(function(){
	var webSocketManager = new WebSocketManager(
			configuracoes["Login"], 
			configuracoes["Senha"], 
			aoConectar);
	
	$.post(path_consulta, "{}", function (data){
		console.log(data);
		total = Object.keys(data).length;
		if(total > 0) {
			exibirMensagem("Procurando... Verificados 0 de " + total);
			for(var ip in data) {
				webSocketManager.conectar(ip, data[ip]);
			}
		} else {
			exibirMensagem("Nenhum dispositivo encontrado");
		}
	}, "json");
});

function aoConectar (sucesso, socket) {
	if(sucesso) {
		addAoGrid(socket.macAddress, socket.ip);
	}
	contador++;
	exibirMensagem("Procurando... Verificados " + contador + " de " + total);
	if(contador == total){
		exibirMensagem("Busca finalizada");
	}
}

function cadastrar(mac) {
	var dados = {};
	dados["operacao"] = "cadastrar";
	dados["macaddress"] = mac;
	dados["localizacao"] = "";
	dados["observacao"] = "";
	
	$.post(path_cadastro, JSON.stringify(dados), function (data){
		var botao = document.querySelector("#" + mac.macToId() + " button");
		var celula = botao.parentElement;
		if(data.status == 1) {
			celula.innerHTML = montarBotaoRemover(mac);
		} else {
			celula.innerHTML = data.mensagem;
		}
	}, 'json');
}

function remover(mac) {
	var dados = {};
	dados["operacao"] = "excluir";
	dados["macaddress"] = mac;
	
	$.post(path_cadastro, JSON.stringify(dados), function (data){
		var botao = document.querySelector("#" + mac.macToId() + " button");
		var celula = botao.parentElement;
		if(data.status == 1) {
			celula.innerHTML = montarBotaoCadastrar(mac);
		} else {
			celula.innerHTML = data.mensagem;
		}
	}, 'json');
}

function montarBotaoCadastrar(mac) {
	return "<button class='comando cadastrar' onClick='cadastrar(\"" + mac + "\")'>Cadastrar</button>";
}

function montarBotaoRemover(mac) {
	return "<button class='comando excluir' onClick='remover(\"" + mac + "\")'>Remover</button>";
}

function addAoGrid(mac, ip) {
	var dados = {};
	dados["operacao"] = "buscar";
	dados["macaddress"] = mac;
	
	$.post(path_cadastro, JSON.stringify(dados), function (data){
			var linhas = "";
			linhas += "<tr class='dispositivo' id='" + mac.macToId() + "'>";
			if(Object.keys(data).length == 0) {
				linhas += "<td>" + montarBotaoCadastrar(mac) + "</td>"
			} else {
				linhas += "<td>" + montarBotaoRemover(mac) + "</td>"
			}
			linhas += "<td>" + mac + "</td>";
			linhas += "<td>" + ip + "</td>";
			linhas += "</tr>";
			$("#DispositivosEncontratos").append(linhas);
	}, 'json');
}

</script>

<div class="status"></div>
<table class='tabela' style="width: 100%" id="DispositivosEncontratos">
	<tr>
		<th>Comando</th>
		<th>Mac Address</th>
		<th>ip</th>
	</tr>
</table>
<br>
<div id="EspStatus"></div>

<?php
$pagemaincontent = ob_get_contents ();
ob_end_clean ();
$titulo = "Cadastro automático";
include ("master.php");
?>