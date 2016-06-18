<?php
require_once '../Model/Configuracao.php';

define ( "STATUS_SUCESSO", 1 );
define ( "STATUS_FALHA", 0 );

$dados = json_decode ( file_get_contents ( 'php://input' ), true );
$operacao = $dados ["operacao"];

if ($operacao == "atualizar") {
	$result = Atualizar ( $dados ["login"], $dados ["senha"], $dados ["tempo"] );
	echo json_encode($result);
} else if ($operacao == "buscar") {
	echo json_encode ( Buscar ());
}
function Atualizar($login, $senha, $tempo) {
	$resultado = array ();
	$resultado ['status'] = STATUS_FALHA;
	$login = trim($login);
	if($login == "") {
		$resultado['mensagem'] = 'O login é um campo obrigatório';
		return $resultado;
	}
	if($senha == "") {
		$resultado['mensagem'] = 'A senha é um campo obrigatório';
		return $resultado;
	}
	$tempo = trim($tempo);
	if($tempo == "") {
		$resultado['mensagem'] = 'O tempo é um campo obrigatório';
		return $resultado;
	}
	
	$configuracao = new Configuracao ();
	if (($result = $configuracao->Alterar( $login, $senha, $tempo)) !== true) {
		$resultado ['mensagem'] = $result;
	} else {
		$resultado ['status'] = STATUS_SUCESSO;
		$resultado ['mensagem'] = 'Alterado com sucesso';
	}
	return $resultado;
}
function Buscar() {
	$configuracoes = new Configuracao ();
	$resultado = $configuracoes->Buscar ();
	
	return $resultado;
}
?>