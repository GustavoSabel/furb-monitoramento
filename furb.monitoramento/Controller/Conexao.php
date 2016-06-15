<?php
	class Conexao {
		
		/**
		 * Abre e retorna uma conexão aberta
		 */
		public static function Abrir() {
			// definições de host, database, usuário e senha 
			$host = "localhost"; 
			$db = "furb_monitoramento"; 
			$user = "admin"; 
			$pass = ""; 
			// conecta ao banco de dados 
			$conn = new mysqli($host, $user, $pass, $db); 
			if ($conn->connect_error) {
				die("Conexão falhou: " . $conn->connect_error);
			} 			
			return $conn;
		}
		
		/**
		 * Executa um UPDATE, DELETE ou INSERT
		 * @param string $query
		 */
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