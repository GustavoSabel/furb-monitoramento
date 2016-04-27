<?php
require_once '../Model/Configuracao.php';

define ( "STATUS_SUCESSO", 1 );
define ( "STATUS_FALHA", 0 );

$dados = json_decode ( file_get_contents ( 'php://input' ), true );
$operacao = $dados ["operacao"];

if ($operacao == "atualizar") {
	Atualizar ( $dados ["login"], $dados ["senha"], $dados ["tempo"] );
} else if ($operacao == "buscar") {
	echo json_encode ( Buscar ());
}
function Atualizar($login, $senha, $tempo) {
	$resultado = array ();
	$resultado ['status'] = STATUS_FALHA;
	$configuracao = new Configuracao ();
	if (($result = $configuracao->Alterar( $login, $senha, $tempo)) !== true) {
		$resultado ['mensagem'] = $result;
	} else {
		$resultado ['status'] = STATUS_SUCESSO;
		$resultado ['mensagem'] = 'Alterado com sucesso';
	}
	echo json_encode ( $resultado );
}
function Buscar() {
	$configuracoes = new Configuracao ();
	$resultado = $configuracoes->Buscar ();
	
	return $resultado;
}
?>