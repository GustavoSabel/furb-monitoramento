<?php
require_once '../Model/Dispositivos.php';

define ( "INFO_APENAS_CADASTRADOS", 'apenas_cadastrados' );

$dados = json_decode ( file_get_contents ( 'php://input' ), true );

if (array_key_exists ( INFO_APENAS_CADASTRADOS, $dados )) {
	echo json_encode ( BuscarEnderecos ( $dados [INFO_APENAS_CADASTRADOS] ) );
} else {
	echo json_encode ( BuscarEnderecos ( false ) );
}

/**
 * Faz uma busca pela rede e busca todos dispositivos da rede com seu IP e Mac Address
 * @param boolean $apenasCadastrados: Se true ou 1, retorna apenas os dispositivos encontratos que estão cadastrados no sistema
 * @return Retorna um array com os IPs e Mac Address. A chave é o IP e o valor é o endereço mac
 */
function BuscarEnderecos($apenasCadastrados) {
	$os = $_SERVER ['HTTP_USER_AGENT'];
	
	// No windows não existe o software arp-scan, então apenas traz um resultado fixo para testes
	//if (preg_match ( "/windows/i", $os ) == 1) {
	//	$output = SimularArpScan ();
	//} else {
		$cmd = "sudo arp-scan --localnet --timeout=1000";
		exec ( $cmd, $output );
	//}
	
	$dispositivos = array ();
	
	$dispositivosCadastrados = array ();
	if ($apenasCadastrados) {
		$disp = new Dispositivos ();
		$dispositivosCadastrados = $disp->Buscar ();
	}
	
	$expressao = "/^(\d(\d?){2}.?){4}\s([a-f0-9]{2}:?){6}/i";
	foreach ( $output as $linha ) {
		if (preg_match ( $expressao, $linha ) == 1) {
			$linhaQuebrada = array ();
			$linhaQuebrada = preg_split ( "/\s/", $linha );
			if (! $apenasCadastrados or ContemDispositivo ( $dispositivosCadastrados, $linhaQuebrada [1] )) {
				$dispositivos [$linhaQuebrada [0]] = $linhaQuebrada [1];
			}
		}
	}
	
	return $dispositivos;
}

/**
 * Verifica se no array $dispositivos contém o $mac
 * @param array $dispositivos
 * @param string $mac
 * @return boolean
 */
function ContemDispositivo($dispositivos, $mac) {
	foreach ( $dispositivos as $dispo ) {
		if ($dispo [1] == $mac) {
			return true;
		}
	}
	return false;
}

/**
 * Simula o arp-scan para windows, ou seja, traz apenas um resultado fake para testes.
 * Retorna o mesmo que o que o comando arp-scan retornaria no sistema linux.
 */
function SimularArpScan() {
	/*for($i = 1; $i < 250; $i ++) {
		$output [] = "192.168.1.$i 18:fe:34:a1:f5:a7 (Unknown)";
	}
	return $output;*/
	
	  return [
	  "Interface: enp0s3, datalink type: EN10MB (Ethernet)",
	  "Starting arp-scan 1.8.1 with 256 hosts (http://www.nta-monitor.com/tools/arp-scan/)",
	  "192.168.1.1 e8:de:27:66:2f:4a (Unknown)",
	  "192.168.1.100 c0:65:99:b8:9e:50 (Unknown)",
	  "192.168.1.101 14:1a:a3:1c:62:c2 (Unknown)",
	  "192.168.1.102 f4:f1:e1:13:db:6b (Unknown)",
	  "192.168.1.103 0c:84:dc:ff:35:a9 (Unknown)",
	  "192.168.1.104 f4:f1:e1:13:db:6b (Unknown)",
	  "192.168.1.106 18:fe:34:a1:f5:a7 (Unknown)",
	  "192.168.1.105 18:fe:34:f5:d6:d3 (Unknown)",
	  "",
	  "6 packets received by filter, 0 packets dropped by kernel",
	  "Ending arp-scan 1.8.1: 256 hosts scanned in 3.372 seconds (75.92 hosts/sec). 6 responded"
	  ];
	 
}
?>

<?php
/*
 * // Must be run as root
 * $arp_scan = shell_exec('arp-scan --localnet');
 *
 * echo $arp_scan;
 *
 * $arp_scan = explode("\n", $arp_scan);
 *
 * $matches;
 *
 * foreach($arp_scan as $scan) {
 *
 * $matches = array();
 *
 * if(preg_match('/^([0-9\.]+)[[:space:]]+([0-9a-f:]+)[[:space:]]+(.+)$/', $scan, $matches) !== 1) {
 * continue;
 * }
 *
 * $ip = $matches[1];
 * $mac = $matches[2];
 * $desc = $matches[3];
 *
 * echo "Found device with mac address $mac ($desc) and ip $ip\n";
 * }
 */
?>
