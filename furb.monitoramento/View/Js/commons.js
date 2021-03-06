var path_configuracoes = "../Controller/ConfiguracoesController.php";
var path_cadastro = "../Controller/CadastroController.php";
var path_monitoramento = "../Controller/MonitoramentoController.php"

String.prototype.remover = function(caracter) {
	var target = this;
	return target.replace(new RegExp(caracter, 'g'), "");
};

// Transforma o macAddress em um ID
String.prototype.macToId = function() {
	var target = this;
	return "mac-" + target.replace(new RegExp(":", 'g'), "");
};

function getHora() {
	return new Date().toLocaleTimeString();
}

function getData() {
	return new Date().toLocaleDateString();
}

function exibirMensagem(mensagem, status) {
	var tipo = ''
	if(status == 1) {
		tipo = 'success'
	} else if (status == 0) {
		tipo = 'danger'
	} else {
		tipo = 'warning'
	}
	$.notify(mensagem, {
		type: tipo,
		animate: {
			enter: 'animated fadeInRight',
			exit: 'animated fadeOutRight'
		}
	});
}