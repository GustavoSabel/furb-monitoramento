var DISP_PROCURANDO = 'procurando';
var DISP_NAO_ENCONTRATO = 'naoencontrado';

var DISP_CONECTADO = 'conectado';
var DISP_CONECTANDO = 'conectando';
var DISP_NAO_CONECTADO = 'naoconectado';

//Indica o tempo máximo que o sistema pode ficar sem consultar o sensor de movimento
//se a última consulta ao sensor de movimento ultrapassar esse tempo, então o cronômetro
//do dispositivo deve ser zerado
var TEMPO_EXPIRACAO_SENSOR = 1000*60*4; //4 minutos
var TEMPO_EXPIRACAO = 30000 //30 segundos
var DELAY_CONSULTAS_SENSOR = 3000;

/**
 * Chave: IP
 * Valores: {
 * 		dataUltimaComunicacao [Data de controle para verificar conexao e se não expirou], 
 * 		socket, 
 * 		macAddress [Endereço mac], 
 * 		ultimoMovimento [Data do último movimento identificado pelo sensor], 
 * 		ultimaStatusSensorRecebido [Última data de recebimento do status do sensor], 
 * 		ultimaStatusSensorRequisitado [Data da última requisição de status para sensor]
 * }
 */
var dispositivos = [];
var webSocketManager;

$(function(){
	webSocketManager = new WebSocketManager(aoConectar, aoReceberMensagem, aoDesconectar, aOcorrerErro);
	exibirCadastrados(buscarDispositivos);
	verificarExpiracao();
});

function verificarExpiracao() {
	setTimeout(function(){
		dataAtual = Date.now();
		for(var ip in dispositivos) {
			if(dataAtual - dispositivos[ip]["dataUltimaComunicacao"] > TEMPO_EXPIRACAO) {
				dispositivos[ip]["dataUltimaComunicacao"] = dataAtual;
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
			linhas += "<td class='status'		> ... </td>";
			linhas += "<td class='ip'			> ... </td>";
			linhas += "<td class='local'		>" + row[2] + "</td>";
			linhas += "<td class='observacao'	>" + row[3] + "</td>";
			linhas += "<td class='comandos'>";
			linhas += "<input type='checkbox' name='relay' value='relay' onchange='toggleRelay(this, \""+ row[1]+"\")'/><span>Relay</span>";
			linhas += "<button onClick='desligar(\""+ row[1] +"\")'>Desligar</button>";
			linhas += "</td>";
			linhas += "<td class='sensor'> ... </td>";
			linhas += "</tr>";
		});

		$("#TabelaMonitoriamento").append(linhas);

		callback();
	}, 'json');
}

function buscarDispositivos() {
 	var dados = {};
 	dados["apenas_cadastrados"] = "1";
 	var dadosJson = JSON.stringify(dados);

 	atualizarStatusByClasse(DISP_NAO_ENCONTRATO, DISP_PROCURANDO, "Procurando...")
 	
	$.post(path_consulta, dadosJson, function (data) {
		for(var ip in data) {
			dispositivos[ip] = { data : Date.now(), macAddress : data[ip] }
			var linha = getLinhaByIP(ip);
			if(linha != null && linha.className == DISP_PROCURANDO ) {
				conectar(ip)
			}
		}

		atualizarStatusByClasse(DISP_PROCURANDO, DISP_NAO_ENCONTRATO, "Não encontrato")
	}, "json");
}

function atualizarStatusByClasse(classeOriginal, novaClasse, statusDescricao) {
	var linhas = document.querySelectorAll("tr." + classeOriginal);
	for(var i = 0; i< linhas.length; i++) {
		atualizarStatusByLinha(linhas[i], novaClasse, statusDescricao, null)
	}
}

function atualizarStatusByMac(MacAddress, classe, status, ip, sensor) {
	var linha = document.querySelector("#" + MacAddress.macToId());
	atualizarStatusByLinha(linha, classe, status, ip, sensor) 
}

function atualizarStatusByLinha(linha, classe, status, ip, sensor) {
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

function toggleRelay(relay, mac) {
	var ip = getIpByMac(mac);
	webSocketManager.setRelay(ip, (relay.checked? 1 : 0 ))
}

function desligar(mac) {
	var ip = getIpByMac(mac);
	webSocketManager.desligar(ip);
}

/*
function ConectarNovamente() {
	var naoConectados = document.querySelectorAll("tr." + DISP_NAO_CONECTADO);
	for(var i = 0; i < naoConectados.length; i++) {
		var celulaIp = naoConectados[i].querySelector("td.ip");
		var ip = celulaStatus.firstChild.textContent;
		conectar(ip);
	}
}*/

function conectar(ip) {
	var mac = dispositivos[ip]["macAddress"];
	var socket = webSocketManager.conectar(ip, mac);
	dispositivos[ip]["socket"] = socket;
	atualizarStatusByMac(mac, DISP_CONECTANDO, "Conectando...", socket.ip);
}

function atualizarSensor(socket, statusSensor) {
	if(statusSensor == 1){
		statusSensor = "Com movimento";
		dispositivos[socket.ip]["ultimoMovimento"] = Date.now();
	} else  {
		//Se a última consulta ao sensor foi a muito tempo, então deve-se zerar o tempo
		if(dispositivos[socket.ip]["ultimaStatusSensorRecebido"] != null && 
				((Date.now() - dispositivos[socket.ip]["ultimaStatusSensorRecebido"]) > TEMPO_EXPIRACAO_SENSOR))
		{
			dispositivos[socket.ip]["ultimoMovimento"] = Date.now();
		}
		var segundosSemMovimento = (Date.now() - dispositivos[socket.ip]["ultimoMovimento"])/1000; 
		segundosSemMovimento = Math.round(segundosSemMovimento*100)/100;
		statusSensor = segundosSemMovimento + "s sem movimento"
	}
	dispositivos[socket.ip]["ultimaStatusSensorRecebido"] = Date.now();

	atualizarStatusByMac(socket.macAddress, null, null, null, statusSensor);
	
	var diferencaTempo = 0;
	if(dispositivos[socket.ip]["ultimaStatusSensorRequisitado"] != null) {
		diferencaTempo = Date.now() - dispositivos[socket.ip]["ultimaStatusSensorRequisitado"];
	}
	setTimeout(function(){
			dispositivos[socket.ip]["ultimaStatusSensorRequisitado"] = Date.now();
			webSocketManager.consultarSensor(socket.ip);
		}, DELAY_CONSULTAS_SENSOR - diferencaTempo);
	
}

function aoReceberMensagem(event, socket) {	
	console.log(event);
	resultado = JSON.parse(event.data);
	dispositivos[socket.ip]["dataUltimaComunicacao"] = Date.now();
	if(resultado["EventURL"] == "/mod-io2") {
		if(resultado["EventData"]["Data"] != null) {
			atualizarSensor(socket, resultado["EventData"]["Data"]["GPIO3"]);
		}
		else{
			atualizarStatusByMac(socket.macAddress, null, null, null, "Problema no sensor");
			console.log("Problema no Mod-io2. ");
		}
	}
}

function aoDesconectar(event, socket) {
	atualizarStatusByMac(socket.macAddress, DISP_NAO_CONECTADO, "Desconectado", null);
}

function aOcorrerErro(event, socket) {
	atualizarStatusByMac(socket.macAddress, DISP_NAO_CONECTADO, "Erro na conexão", null);
}

function aoConectar(sucesso, socket) {
	if(sucesso) {
		dispositivos[socket.ip]["dataUltimaComunicacao"] = Date.now();
		if(dispositivos[socket.ip]["ultimoMovimento"] == null)
			dispositivos[socket.ip]["ultimoMovimento"] = Date.now();
		atualizarStatusByMac(socket.macAddress, DISP_CONECTADO, "Conectado", null);
		webSocketManager.consultarSensor(socket.ip);
	} else {
		atualizarStatusByMac(socket.macAddress, DISP_NAO_CONECTADO, "Erro ao conectar", null);
	}
}

function getIpByMac(mac) {
	var linha = getLinhaByMac(mac);
	var celulaIp = linha.querySelector("td.ip");
	return celulaIp.firstChild.textContent;
}

function getLinhaByIP(ip) {
	return document.querySelector("#" + dispositivos[ip]["macAddress"].macToId());
}

function getLinhaByMac(mac) {
	return document.querySelector("#" + mac.macToId());
}
