var path_configuracoes = "../Controller/ConfiguracoesController.php";
var path_cadastro = "../Controller/Cadastro.php";
var path_consulta = "../Controller/ConsultarEsp.php"

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
	var tipo = (status == 1 ? 'success' : 'danger')
	$.notify(mensagem, {
		type: tipo,
		animate: {
			enter: 'animated fadeInRight',
			exit: 'animated fadeOutRight'
		}
	});
}