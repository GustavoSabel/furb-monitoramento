<?php
require_once ("../Controller/Conexao.php");
class Configuracao {
	public $Login;
	public $Senha;
	public $TempoDesligamento;
	public function Buscar() {
		$conn = Conexao::Abrir ();
		$query = "select login, senha, tempodesligamento from configuracoes";
		if (($result = $conn->query ( $query )) == FALSE) {
			error_log ( $conn->error );
		}
		if ($result->num_rows == 0) {
			$this->Inserir ( 'olimex', 'olimex', 60 );
			return Buscar ();
		}
		$config = new Configuracao ();
		$linha = $result->fetch_assoc ();
		$config->Login = $linha ["login"];
		$config->Senha = $linha ["senha"];
		$config->TempoDesligamento = $linha ["tempodesligamento"];
		return $config;
	}
	public function Alterar($Nome, $Senha, $TempoDesligamento) {
		$conn = Conexao::Abrir ();
		$query = "update configuracoes set login = '$Nome', senha = '$Senha', tempodesligamento = $TempoDesligamento";
		if ($conn->query ( $query ) === TRUE) {
			return true;
		} else {
			return "Erro: " . $query . "<br>" . $conn->error;
		}
	}
	public function Inserir($Nome, $Senha, $TempoDesligamento) {
		$conn = Conexao::Abrir ();
		$query = "insert into configuracoes (login, senha, tempodesligamento) values ('$Nome', '$Senha', $TempoDesligamento)";
		if ($conn->query ( $query ) === TRUE) {
			return true;
		} else {
			return "Erro: " . $query . "<br>" . $conn->error;
		}
	}
}
