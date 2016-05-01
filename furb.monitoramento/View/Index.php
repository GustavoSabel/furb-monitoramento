<?php ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
	$configJson = json_encode($configuracao);
?>

<script>
	var configuracoes = JSON.parse('<?php echo $configJson; ?>');
</script>

<script src="Js/monitoramento.js"></script>
<link rel="stylesheet" type="text/css" href="Resources/monitoramento.css">

<button onClick="buscarDispositivos()">
	Buscar novamente <span class="tooltip"> <img
		src="Resources/Imagens/help.png" alt="help" /> <span
		class="tooltiptext"> Caso alguns dispositivos não tenham sido
			encontrados, ao clicar nesse comando será feita uma nova busca na
			rede para encontrá-los. Os dispositivos já encontrados não sofrerão
			impacto. </span>
	</span>
</button>
<!--  <button onClick="ConectarNovamente()">Tentar conectar novamente</button> -->
<table id="TabelaMonitoriamento">
	<tr>
		<th>Mac Addrress</th>
		<th>Status</th>
		<th>Histórico</th>
		<th>IP</th>
		<th>Localização</th>
		<th>Obs</th>
		<th>Comandos</th>
		<th>Sensor</th>
	</tr>
</table>

<?php
$pagemaincontent = ob_get_contents ();
ob_end_clean ();
$titulo = "Monitoramento";
include ("master.php");
?>
