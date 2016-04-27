<?php ob_start(); ?>
<script>

var DISP_PROCURANDO = 'procurando';
var DISP_NAO_ENCONTRATO = 'naoencontrado';

var DISP_CONECTADO = 'conectado';
var DISP_CONECTANDO = 'conectando';
var DISP_NAO_CONECTADO = 'naoconectado';
var TEMPO_EXPIRACAO = 30000 //30 segundos
var DELAY_CONSULTAS_SENSOR = 3000;

//A chave é o IP e o valor é o endereço mac
var dispositivos = [];
var webSocketManager;
$(function(){
	webSocketManager = new WebSocketManager(aoConectar, aoReceberMensagem, aoDesconectar, aOcorrerErro);
	exibirCadastrados(buscarIps);
	verificarExpiracao();
});

function verificarExpiracao() {
	setTimeout(function(){
		dataAtual = Date.now();
		for(var ip in dispositivos) {
			if(dataAtual - dispositivos[ip]["data"] > TEMPO_EXPIRACAO) {
				dispositivos[ip]["data"] = dataAtual;
				if(dispositivos[ip]["socket"].readyState == OPEN) {
					webSocketManager.consultarSensor(ip);
				} else if(dispositivos[ip]["socket"].readyState == CLOSED) {
					conectar(ip);
				}
			}
		}
		verificarExpiracao();
	}, 5000);
}

function exibirCadastrados(callback) {
	var dados = JSON.stringify({operacao:"buscar"});
	console.log(dados);
	$.post(path_cadastro, dados, function (data) {
		console.log(data);

		$(".dispositivo").remove();
		var linhas = "";
		data.forEach(function(row) {
			linhas += "<tr class='"+ DISP_NAO_ENCONTRATO +"' id='"+ row[1].macToId() +"'>";
			linhas += "<td class='macaddress'	>" + row[1] + "</td>";
			linhas += "<td class='status'		> # </td>";
			linhas += "<td class='ip'			> # </td>";
			linhas += "<td class='local'		>" + row[2] + "</td>";
			linhas += "<td class='observacao'	>" + row[3] + "</td>";
			linhas += "<td class='comandos'>";
			linhas += "<input type='checkbox' name='relay' value='relay' onchange='toggleRelay(this, \""+ row[1]+"\")'>Relay</input>";
			linhas += "<button onClick='Desligar(\""+ row[1] +"\")'>Desligar</button>";
			linhas += "</td>";
			linhas += "<td class='sensor'>...</td>";
			linhas += "</tr>";
		});

		$("#TabelaMonitoriamento").append(linhas);

		callback();
	}, 'json');
}

function toggleRelay(relay, mac) {
	var ip = pegarIpGrid(mac);
	webSocketManager.setRelay(ip, (relay.checked? 1 : 0 ))
}

function buscarIps() {
 	var dados = {};
 	dados["apenas_cadastrados"] = "1";
 	var dadosJson = JSON.stringify(dados);

 	AjustarStatusLinhas(DISP_NAO_ENCONTRATO, DISP_NAO_ENCONTRATO, "Procurando...")
 	
	$.post(path_consulta, dadosJson, function (data) {
		for(var ip in data) {
			dispositivos[ip] = { data : Date.now(), macAddress : data[ip] }
			var linha = document.querySelector("#" + data[ip].macToId());
			if(linha != null && linha.className == DISP_NAO_ENCONTRATO ) {
				conectar(ip)
			}
		}

		AjustarStatusLinhas(DISP_NAO_ENCONTRATO, DISP_NAO_ENCONTRATO, "Não encontrato")
	}, "json");
}

function AjustarStatusLinhas(classeOriginal, novaClasse, statusDescricao) {
	var linhas = document.querySelectorAll("tr." + classeOriginal);
	for(var i = 0; i< linhas.length; i++) {
		AjustarLinha(linhas[i], novaClasse, statusDescricao, null)
	}
}

function AtualizarStatus(MacAddress, classe, status, ip, sensor) {
	var linha = document.querySelector("#" + MacAddress.macToId());
	AjustarLinha(linha, classe, status, ip, sensor) 
}

function AjustarLinha(linha, classe, status, ip, sensor) {
	if(classe != null) {
		linha.className = classe;
	}
	if(status != null) {
		var celulaStatus = linha.querySelector("td.status");
		celulaStatus.firstChild.textContent = status;
	}
	if(ip != null) {
		var celulaIp = linha.querySelector("td.ip");
		celulaIp.firstChild.textContent = ip;
	}
	if(sensor != null) {
		var celula = linha.querySelector("td.sensor");
		celula.firstChild.textContent = sensor;
	}
}

function aoReceberMensagem(event, socket) {	
	console.log(event);
	resultado = JSON.parse(event.data);
	if(resultado["EventURL"] == "/mod-io2") {
		var statusSensor = resultado["EventData"]["Data"]["GPIO3"];
		if(statusSensor == 1){
			statusSensor = "Com movimento";
			dispositivos[socket.ip]["ultimoMovimento"] = Date.now();
		} else  {
			var segundosSemMovimento = (Date.now() - dispositivos[socket.ip]["ultimoMovimento"])/1000; 
			statusSensor = segundosSemMovimento + "s sem movimento"
		}
		AtualizarStatus(socket.macAddress, null, null, null, statusSensor);

		
		/*var linha = document.querySelector("#" + socket.macAddress.macToId());
		var celula = linha.querySelector("td.sensor");
		celula.firstChild.textContent = ;*/

		diferencaTempo = Date.now() - dispositivos[socket.ip]["data"];
		setTimeout(function(){
				dispositivos[socket.ip]["data"] = Date.now();
				webSocketManager.consultarSensor(socket.ip);
			}, DELAY_CONSULTAS_SENSOR - diferencaTempo);
	}
}

function aoDesconectar(event, socket) {
	AtualizarStatus(socket.macAddress, DISP_NAO_CONECTADO, "Desconectou...", null);
}

function aOcorrerErro(event, socket) {
	AtualizarStatus(socket.macAddress, DISP_NAO_CONECTADO, "Erro na conexão", null);
}

function aoConectar(sucesso, socket) {
	if(sucesso) {
		dispositivos[socket.ip]["data"] = Date.now();
		webSocketManager.consultarSensor(socket.ip);
		AtualizarStatus(socket.macAddress, DISP_CONECTADO, "Conectado", null);
	} else {
		AtualizarStatus(socket.macAddress, DISP_NAO_CONECTADO, "Erro conexão", null);
	}
}

function Desligar(mac) {
	var ip = pegarIpGrid(mac);
	desligar(ip);
}

function BuscarNovamente() {
	buscarIps();
}

function ConectarNovamente() {
	var naoConectados = document.querySelectorAll("tr." + DISP_NAO_CONECTADO);
	for(var i = 0; i < naoConectados.length; i++) {
		var celulaIp = naoConectados[i].querySelector("td.ip");
		var ip = celulaStatus.firstChild.textContent;
		conectar(ip);
	}
}

function pegarIpGrid(mac) {
	var linha = document.querySelector("#" + mac.macToId());
	var celulaIp = linha.querySelector("td.ip");
	return celulaIp.firstChild.textContent;
}

function getLinhaByIP(ip) {
	return document.querySelector("#" + dispositivos[ip]["macAddress"].macToId());
}

function getLinhaByMac(mac) {
	return document.querySelector("#" + mac.macToId());
}

function conectar(ip) {
	var linha = getLinhaByIP(ip);
	AjustarLinha(linha, DISP_CONECTANDO, "Conectando...", null);
	var socket = webSocketManager.conectar(ip, dispositivos[ip]["macAddress"]);
	dispositivos[ip]["socket"] = socket;
}

</script>

<button onClick="BuscarNovamente()">Buscar novamente</button>
<button onClick="ConectarNovamente()">Tentar conectar novamente</button>
<table id="TabelaMonitoriamento">
	<tr>
		<th>Mac Addrress</th>
		<th>Status</th>
		<th>IP</th>
		<th>Localização</th>
		<th>Observação</th>
		<th>Comandos</th>
		<th>Sensor</th>
	</tr>
</table>
<br>
<br>
<div id="EspStatus"></div>

<?php
$pagemaincontent = ob_get_contents ();
ob_end_clean ();
$titulo = "Monitoramento";
include ("master.php");
?>
