<?php
	class Conexao {
		public static function Abrir() {
			// definições de host, database, usuário e senha 
			$host = "localhost"; 
			$db = "furb_monitoramento"; 
			$user = "root"; 
			$pass = ""; 
			// conecta ao banco de dados 
			$conn = new mysqli($host, $user, $pass, $db); 
			if ($conn->connect_error) {
				die("Conexão falhou: " . $conn->connect_error);
			} 			
			return $conn;
		}
		
		public static function Executar($query) {
			$conn = Conexao::Abrir ();
			if ($conn->query ( $query ) === TRUE) {
				return true;
			} else {
				return "Erro: " . $query . "<br>" . $conn->error;
			}
		}
	}
?>