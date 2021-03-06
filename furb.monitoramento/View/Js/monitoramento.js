var DISP_PROCURANDO = 'procurando';
var DISP_NAO_ENCONTRATO = 'naoencontrado';

var DISP_CONECTADO = 'conectado';
var DISP_CONECTANDO = 'conectando';
var DISP_NAO_CONECTADO = 'naoconectado';

//Indica o tempo máximo que o sistema pode ficar sem consultar o sensor de movimento.
//Se a última consulta ao sensor de movimento ultrapassar esse tempo, então o cronômetro
//do dispositivo deve ser zerado
var TEMPO_EXPIRACAO_SENSOR = 1000*60*4; //4 minutos
var TEMPO_VERIFICACAO_CONEXAO = 10000
var DELAY_CONSULTAS_SENSOR 	= 5000;
var VERIFICAR_SENSOR = true;

/**
 * Chave: IP
 * Valores: {
 * 		dataUltimaComunicacao [Data de controle para verificar conexao e se não expirou], 
 * 		socket, 
 * 		macAddress [Endereço mac], 
 * 		ultimoMovimento [Data do último movimento identificado pelo sensor], 
 * 		ultimoStatusSensorRecebido [Última data de recebimento do status do sensor],
 * 		dataUltimoDesligamento [Se o comando desligar foi enviado, será armazenada a data em que foi feita. Ao voltar a ter movimento na sala, esse campo é zerado]
 * }
 */
var dispositivos = [];
var webSocketManager;

$(function(){
	webSocketManager = new WebSocketManager(
			configuracoes["Login"], 
			configuracoes["Senha"],
			aoConectar,
			aoReceberMensagem,
			aoDesconectar,
			aoOcorrerErro);
	
	buscarDispositivosCadastrados(buscarDispositivosNaRede);
	iniciarVerificacaoConexao();
	
	if(VERIFICAR_SENSOR) {
		iniciarVerificacaoSensor();
	}
});

function iniciarVerificacaoConexao() {
	setTimeout(function(){
		dataAtual = Date.now();
		for(var ip in dispositivos) {
			if(dataAtual - dispositivos[ip]["dataUltimaComunicacao"] > TEMPO_VERIFICACAO_CONEXAO) {
				dispositivos[ip]["dataUltimaComunicacao"] = dataAtual;
				if(dispositivos[ip]["socket"].readyState == CLOSED) {
					conectar(ip);
				} else if (dispositivos[ip]["socket"].readyState == OPEN) {
					webSocketManager.mensagemVerificacao(dispositivos[ip]["socket"]);	
				}
			}
		}
		iniciarVerificacaoConexao();
	}, 5000);
}

function iniciarVerificacaoSensor() {
	for(var ip in dispositivos) {
		if(dispositivos[ip]["socket"].readyState == OPEN) {
			webSocketManager.consultarSensor(ip);			
		} 
	}
	setTimeout(function(){
		iniciarVerificacaoSensor();
	}, DELAY_CONSULTAS_SENSOR);
}

function buscarDispositivosCadastrados(callback) {
	var dados = JSON.stringify({operacao:"buscar"});
	console.log(dados);
	$.post(path_cadastro, dados, function (data) {

		$(".dispositivo").remove();
		var linhas = "";
		data.forEach(function(row) {	
			var macAdrress = row[1];
			linhas += "<tr class='"+ DISP_NAO_ENCONTRATO +"' id='"+ macAdrress.macToId() +"'>";
			
			//------------ MAC ADDRESS ------------------
			linhas += "<td class='macaddress'	>" + macAdrress + "</td>";
			
			//------------ STATUS ------------------
			linhas += "<td class='status'		> ... </td>";

			//------------ HISTORICO ------------------
			linhas += "<td class='historico'>";
			linhas += "		<span class='historico_ultimo'></span>";
			linhas += "		<button class='btn btn-info btn-sm' data-toggle='modal' data-target='#log" + macAdrress.macToId() + "'>Log</button>";
			linhas += "		<div class='modal fade bd-example-modal-lg' id='log" + macAdrress.macToId() + "' tabindex='-1' role='dialog' aria-labelledby='myLargeModalLabel' aria-hidden='true'>";
			linhas += "  		<div class='modal-dialog modal-lg'>";
			linhas += "    			<div class='modal-content'>";
			linhas += "    				<div class='modal-header'>";
			linhas += "    					<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
			linhas += "    			  			<span aria-hidden='true'>&times;</span>";
			linhas += "    					</button>";
			linhas += "    					<h4 class='modal-title' id='myModalLabel'>Log do disposítivo do local '" + row[2] + "'</h4>";
			linhas += "    			 	</div>";
			linhas += "    				<div class='modal-body'></div>";
			linhas += "    			</div>";
			linhas += "  		</div>";
			linhas += "		</div>";
			linhas += "</td>";
			
			//------------ IP ------------------
			linhas += "<td class='ip'			> ... </td>";
			
			//------------ LOCAL ------------------
			linhas += "<td class='local'		>" + row[2] + "</td>";
			
			//------------ OBSERVAÇÃO ------------------
			linhas += "<td class='observacao'> ";
			if(row[3] != "") {
				linhas += "		<button class='btn btn-info btn-sm' data-toggle='modal' data-target='#obs" + macAdrress.macToId() + "'>Ver obs</button>";
				linhas += "		<div class='modal fade bd-example-modal-lg' id='obs" + macAdrress.macToId() + "' tabindex='-1' role='dialog' aria-labelledby='myLargeModalLabel' aria-hidden='true'>";
				linhas += "  		<div class='modal-dialog modal-lg'>";
				linhas += "    			<div class='modal-content'>";
				linhas += "    				<div class='modal-header'>";
				linhas += "    					<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
				linhas += "    			  			<span aria-hidden='true'>&times;</span>";
				linhas += "    					</button>";
				linhas += "    					<h4 class='modal-title' id='myModalLabel'>Observações do disposítivo do local '" + row[2] + "'</h4>";
				linhas += "    			 	</div>";
				linhas += "    				<div class='modal-body'>" + row[3].replace(/\n/g, "<br>"); + "</div>";
				linhas += "    			</div>";
				linhas += "  		</div>";
				linhas += "		</div>";
			}
			linhas += "</td>";
			
			//------------ COMANDOS ------------------
			linhas += "<td class='comandos'>";
			linhas += "<input type='checkbox' name='relay' value='relay' onchange='toggleRelay(this, \""+ row[1]+"\")'/><span>Relay</span> ";
			linhas += "<button class='btn btn-danger btn-sm' onClick='desligar(\""+ row[1] +"\")'>Desligar</button>";
			linhas += "</td>";
			
			//------------ SENSOR ------------------
			linhas += "<td class='sensor'> ... </td>";
			
			linhas += "</tr>";
		});

		$("#TabelaMonitoriamento").append(linhas);

		callback();
	}, 'json');
}

function buscarDispositivosNaRede() {
 	var dados = {};
 	dados["apenas_cadastrados"] = "1";
 	var dadosJson = JSON.stringify(dados);

 	atualizarStatusByClasse(DISP_NAO_ENCONTRATO, DISP_PROCURANDO, "Procurando...")
 	
	$.post(path_monitoramento, dadosJson, function (data) {
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

function atualizarStatusByMac(macAddress, classe, status, ip, sensor) {
	var linha = getLinhaByMac(macAddress);
	atualizarStatusByLinha(linha, classe, status, ip, sensor) 
}

function atualizarStatusByLinha(linha, classe, status, ip, sensor) {
	if(classe != null) {
		linha.className = classe;
	}
	if(status != null) {
		addHistoricoByLinha(linha, status);
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

function addHistoricoByMac(macAddress, historico, historico_resumido) {
	addHistoricoByLinha(getLinhaByMac(macAddress), historico, historico_resumido);
}

function addHistoricoByIP(IP, historico, historico_resumido) {
	addHistoricoByLinha(getLinhaByIP(IP), historico, historico_resumido);
}

function addHistoricoByLinha(linha, historico, historico_resumido) {
	var elemento = linha.querySelector("td.historico .historico_ultimo");
	if(historico_resumido != null && historico_resumido != "")
		elemento.innerText = historico_resumido;
	else 
		elemento.innerText = historico;
	
	var logContent = linha.querySelector("td.historico .modal-content .modal-body");
	
	var span = document.createElement("span");
	var textNode = document.createTextNode(getHora() + " - " + historico);
	span.appendChild(textNode);
	logContent.insertBefore(document.createElement("br"), logContent.firstChild);
	logContent.insertBefore(span, logContent.firstChild);
}

function toggleRelay(relay, mac) {
	var ip = getIpByMac(mac);
	webSocketManager.setRelay(ip, (relay.checked? 1 : 0 ))
}

function desligar(mac) {
	var ip = getIpByMac(mac);
	webSocketManager.desligar(ip);
	
	dispositivos[socket.ip]["dataUltimoDesligamento"] = Date.now();
	addHistoricoByMac(mac, "Enviado comando para desligar", "Desligado");
}

function conectar(ip) {
	var mac = dispositivos[ip]["macAddress"];
	var socket = webSocketManager.conectar(ip, mac);
	dispositivos[ip]["socket"] = socket;
	atualizarStatusByMac(mac, DISP_CONECTANDO, "Conectando...", socket.ip);
}

/**
 * Verifica se o último status do sensor expirou, 
 * ou seja, retorna true se faz muito tempo desde a última consulta feita ao sensor
 */
function sensorExpirou(ip) {
	return dispositivos[ip]["ultimoStatusSensorRecebido"] != null 
	&& ((Date.now() - dispositivos[ip]["ultimoStatusSensorRecebido"]) > TEMPO_EXPIRACAO_SENSOR);
}

function atualizarSensor(socket, statusSensor) {
	if(statusSensor == 1){
		statusSensor = "Com movimento";
		dispositivos[socket.ip]["ultimoMovimento"] = Date.now();
		
		if(dispositivos[socket.ip]["dataUltimoDesligamento"] != null) {
			addHistoricoByMac(socket.macAddress, "Voltou a detectar movimento.", "Mov. detectado");
			dispositivos[socket.ip]["dataUltimoDesligamento"] = null;
		}
	} else  {
		//Se a última consulta ao sensor foi a muito tempo, então deve-se zerar o tempo
		if(dispositivos[socket.ip]["ultimoMovimento"] == null || sensorExpirou(socket.ip)) {
			dispositivos[socket.ip]["ultimoMovimento"] = Date.now();
		}
		var segundosSemMovimento = (Date.now() - dispositivos[socket.ip]["ultimoMovimento"])/1000; 
		segundosSemMovimento = Math.round(segundosSemMovimento*100)/100;
		statusSensor = segundosSemMovimento + "s sem movimento"
		
		if(dispositivos[socket.ip]["dataUltimoDesligamento"] == null && 
				segundosSemMovimento > configuracoes["TempoDesligamento"]) {
			addHistoricoByMac(socket.macAddress, "Ficou mais de " + segundosSemMovimento + "s sem movimento. Será enviado o comando para desligar os aparelhos", "Será desligado");
			desligar(socket.macAddress);
		}
	}

	atualizarStatusByMac(socket.macAddress, null, null, null, statusSensor);
	
	dispositivos[socket.ip]["ultimoStatusSensorRecebido"] = Date.now();
}

function aoReceberMensagem(event, socket) {	
	console.log(event);
	resultado = JSON.parse(event.data);
	dispositivos[socket.ip]["dataUltimaComunicacao"] = Date.now();
	if(resultado["EventURL"] == "/mod-io2") {
		if(resultado["EventData"]["Data"] != null) {
			atualizarSensor(socket, resultado["EventData"]["Data"]["GPIO3"]);
		} else {
			atualizarStatusByMac(socket.macAddress, null, null, null, "Problema no sensor");
			addHistoricoByMac(socket.macAddress, "Problema no sensor: " + resultado["EventData"]["Error"], "Problema no sensor");
			console.log("Problema no Mod-io2. ");
		}
	}
}

function aoDesconectar(event, socket) {
	atualizarStatusByMac(socket.macAddress, DISP_NAO_CONECTADO, "Desconectado", null);
}

function aoOcorrerErro(event, socket) {
	atualizarStatusByMac(socket.macAddress, DISP_NAO_CONECTADO, "Erro na conexão", null);
}

function aoConectar(sucesso, socket) {
	if(sucesso) {
		dispositivos[socket.ip]["dataUltimaComunicacao"] = Date.now();
		atualizarStatusByMac(socket.macAddress, DISP_CONECTADO, "Conectado", null);
	} else {
		atualizarStatusByMac(socket.macAddress, DISP_NAO_CONECTADO, "Erro ao conectar", null);
		//TODO: Exibir a mensagem do erro no histórico
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
