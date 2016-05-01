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
		if (($result = $conn->query ( $query )) == FALSE) {
			error_log ( $conn->error );
		}
		$linha = $result->fetch_all ();
		return $linha;
	}
	public function Inserir($MacAddress, $Local, $Observacao) {
		$MacAddress = trim ( $MacAddress );
		$query = "insert into dispositivos (macaddress, local, observacao) values ('$MacAddress', '$Local', '$Observacao')";
		return Conexao::Executar ( $query );
	}
	public function EditarPorId($Id, $Local, $Observacao) {
		$query = "update dispositivos set local = '$Local', observacao = '$Observacao' where id = $Id";
		return Conexao::Executar ( $query );
	}
	public function EditarPorMac($MacAddress, $Local, $Observacao) {
		$MacAddress = trim ( $MacAddress );
		$query = "update dispositivos set local = '$Local', observacao = '$Observacao' where macaddress = '$MacAddress'";
		return Conexao::Executar ( $query );
	}
	public function Excluir($id, $mac) {
		$mac = trim ( $mac );
		$query = "delete from dispositivos where 1 = 1";
		if ($id != null)
			$query .= " and id = $id";
		else
			$query .= " and macaddress = '$mac'";
		
		return Conexao::Executar ( $query );
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