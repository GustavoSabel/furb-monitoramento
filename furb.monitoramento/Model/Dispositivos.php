<?php
require_once ("../Controller/Conexao.php");
class Dispositivos {
	public function Buscar($Id = null, $MacAddress = null) {
		$MacAddress = trim ( $MacAddress );
		$conn = Conexao::Abrir ();
		$query = "select id, macaddress, local, observacao from dispositivos where 1 = 1";
		if ($Id != null) {
			$query .= " and id = " + $Id;
		}
		if ($MacAddress != null) {
			$query .= " and macaddress = '$MacAddress'";
		}
		if(($result = $conn->query ( $query )) == FALSE) {
			error_log($conn->error);
		}
		$linha = $result->fetch_all ();
		return $linha;
	}
	public function Alterar($Id, $MacAddress, $Local, $Observacao) {
		$MacAddress = trim ( $MacAddress );
		$con = Conexao::Abrir ();
		$query = "update dispositivos set macaddress = '$MacAddress', local = '$Local', observacao = '$Observacao' where id = $Id";
		$conn->query ( $query );
	}
	public function Inserir($MacAddress, $Local, $Observacao) {
		$MacAddress = trim ( $MacAddress );
		$conn = Conexao::Abrir ();
		$query = "insert into dispositivos (macaddress, local, observacao) values ('$MacAddress', '$Local', '$Observacao')";
		
		if ($conn->query ( $query ) === TRUE) {
			return true;
		} else {
			return "Erro: " . $query . "<br>" . $conn->error;
		}
	}
	public function Excluir($id, $mac) {
		$mac = trim ( $mac );
		$conn = Conexao::Abrir ();
		$query = "delete from dispositivos where 1 = 1";
		if ($id != null)
			$query .= " and id = $id";
		else
			$query .= " and macaddress = '$mac'";
		
		if ($conn->query ( $query ) === TRUE) {
			return true;
		} else {
			return "Erro: " . $query . "<br>" . $conn->error;
		}
	}
	public function Existe($MacAddress) {
		$MacAddress = trim ( $MacAddress );
		$conn = Conexao::Abrir ();
		$query = "select id from dispositivos where macaddress = '$MacAddress'";
		$conn->query ( $query );
		return $result->num_rows () > 0;
	}
}
?>