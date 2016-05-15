var contador = 0;
var total = 0;
var qtdEncontrados = 0;
var notify;

$(function(){
	var webSocketManager = new WebSocketManager(
			configuracoes["Login"], 
			configuracoes["Senha"], 
			aoConectar);
	
	qtdEncontrados = 0;
	
	$.post(path_consulta, "{}", function (data){
		console.log(data);
		total = Object.keys(data).length;
		if(total > 0) {
			notify = $.notify('<strong>Procurando...</strong> Verificados 0 de ' + total, {
				allow_dismiss: false,
				delay: 3600000
			});
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
		qtdEncontrados++;
	}
	contador++;
	notify.update({
		'message': '<strong>Procurando...</strong> Verificados ' + contador + ' de ' + total});
	if(contador == total){
		var msgEncontrados;
		if(qtdEncontrados == 0)
			msgEncontrados = "Nenhum encontrato"
		else
			msgEncontrados = "Foram encontratos " + qtdEncontrados + " dispositivos";
			
		notify.update({
			'message': '<strong>Procura finalizada</strong> ' + msgEncontrados,
			allow_dismiss: true,
			type:'success'});
	}
}

function cadastrar(mac) {

	linha = getLinhaByMac(mac);	
	var celulaLocal = linha.querySelector("input.local");
	var local = celulaLocal.value;
	var celulaObservacao = linha.querySelector("input.observacao");
	var observacao = celulaObservacao.value;

	var dados = {};
	dados["operacao"] = "cadastrar";
	dados["macaddress"] = mac;
	dados["localizacao"] = local;
	dados["observacao"] = observacao;

	$.post(path_cadastro, JSON.stringify(dados), function (data){
		if(data.status == 1) {
			atualizarStatus(mac, true);
		} else {
			exibirMensagem(data.mensagem, data.status)
		}
	}, 'json');
}

function remover(mac) {
	var dados = {};
	dados["operacao"] = "excluir";
	dados["macaddress"] = mac;
	
	$.post(path_cadastro, JSON.stringify(dados), function (data){
		if(data.status == 1) {
			atualizarStatus(mac, false);
		} else {
			exibirMensagem(data.mensagem, data.status)
		}
	}, 'json');
}

function atualizarStatus(mac, estaCadastrado) {
	var linha = getLinhaByMac(mac);
	var botao = linha.querySelector("#" + mac.macToId() + " button");
	var celula = botao.parentElement;

	var local = linha.querySelector("input.local");
	var observacao = linha.querySelector("input.observacao");
	local.readOnly = estaCadastrado;//.prop('readonly', true);
	observacao.readOnly = estaCadastrado;
	if(estaCadastrado) {
		celula.innerHTML = montarBotaoRemover(mac);
	} else {
		celula.innerHTML = montarBotaoCadastrar(mac);
	}
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
			var comando = "";
			var local = "";
			var observacao = "";
			if(Object.keys(data).length == 0) {
				comando = montarBotaoCadastrar(mac);
			} else {
				comando = montarBotaoRemover(mac);
				local = data[0][2];
				observacao = data[0][3];
			}
			linhas += "<td>" + comando + "</td>"
			linhas += "<td>" + mac + "</td>";
			linhas += "<td>" + ip + "</td>";
			linhas += "<td><input type='text' value='" + local + "' class='local' autocomplete='off'></td>";
			linhas += "<td><input type='text' value='" + observacao + "' class='observacao' autocomplete='off'></td>";

			linhas += "</tr>";
			$("#DispositivosEncontratos").append(linhas);
	}, 'json');
}

function getLinhaByMac(mac) {
	return document.querySelector("#" + mac.macToId());
}
