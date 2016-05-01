<?php
require_once '../Model/Dispositivos.php';

define("STATUS_SUCESSO", 1);
define("STATUS_FALHA", 0);

$dados = json_decode ( file_get_contents ( 'php://input' ), true );
$operacao = $dados ["operacao"];
if ($operacao == "cadastrar") {
	Cadastrar ( $dados ["macaddress"], $dados ["localizacao"], $dados ["observacao"] );
} else if ($operacao == "editar") {
	Editar ( $dados ["macaddress"], $dados ["localizacao"], $dados ["observacao"] );
} else if ($operacao == "buscar") {
	if (array_key_exists ( "macaddress", $dados )) {
		Buscar ( $dados ["macaddress"] );
	} else {
		Buscar ();
	}
} else if ($operacao == "excluir") {
	if (array_key_exists ( "macaddress", $dados )) {
		Excluir ( null, $dados ["macaddress"] );
	} else {
		Excluir ( $dados ["id"], null );
	}
} else {
	$resultado = array ();
	$resultado ['mensagem'] = "Operação '$operacao' inválida";
	echo json_encode ( $resultado );
}
function Cadastrar($MacAddress, $Local, $observacao) {
	$resultado = array ();
	$resultado ['status'] = STATUS_FALHA;
	if ($MacAddress == null || trim ( $MacAddress ) == "") {
		$resultado ['mensagem'] = "Mac Address não informado";
	} else {
		$dispositivos = new Dispositivos ();
		$resultado = $dispositivos->Buscar ( null, $MacAddress );
		if (count ( $resultado ) > 0) {
			$resultado ['mensagem'] = "Mac Address $MacAddress já cadastrado em $Local";
		} else {
			if (($result = $dispositivos->Inserir ( $MacAddress, $Local, $observacao )) !== true) {
				$resultado ['mensagem'] = $result;
			} else {
				$resultado ['status'] = STATUS_SUCESSO;
				$resultado ['mensagem'] = 'Inserido com sucesso';
			}
		}
	}
	echo json_encode ( $resultado );
}
function Editar($MacAddress, $Local, $observacao) {
	$resultado = array ();
	$resultado ['status'] = STATUS_FALHA;
	if ($MacAddress == null || trim ( $MacAddress ) == "") {
		$resultado ['mensagem'] = "Mac Address não informado";
	} else {
		$dispositivos = new Dispositivos ();
		$resultado = $dispositivos->Buscar ( null, $MacAddress );
		if (count ( $resultado ) == 0) {
			$resultado ['mensagem'] = "Mac Address '$MacAddress' ainda não foi cadastrado";
		} else {
			if (($result = $dispositivos->EditarPorMac( $MacAddress, $Local, $observacao )) !== true) {
				$resultado ['mensagem'] = $result;
			} else {
				$resultado ['status'] = STATUS_SUCESSO;
				$resultado ['mensagem'] = 'Editado com sucesso';
			}
		}
	}
	echo json_encode ( $resultado );
}
function Buscar($mac = null) {
	$dispositivos = new Dispositivos ();
	$resultado = $dispositivos->Buscar (null, $mac);
	
	echo json_encode ( $resultado );
}
function Excluir($Id, $MacAddress = null) {
	$dispositivos = new Dispositivos ();
	$resultado = array ();
	if (($result = $dispositivos->Excluir ( $Id, $MacAddress )) !== true) {
		$resultado ['status'] = STATUS_FALHA;
		$resultado ['mensagem'] = $result;
	} else {
		$resultado ['status'] = STATUS_SUCESSO;
		$resultado ['mensagem'] = 'Excluido com sucesso';
	}
	echo json_encode ( $resultado );
}
?>