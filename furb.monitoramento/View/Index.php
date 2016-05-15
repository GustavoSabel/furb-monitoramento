<?php ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
	$configJson = json_encode($configuracao);
?>

<script src="Js/monitoramento.js"></script>
<link rel="stylesheet" type="text/css" href="Resources/monitoramento.css">

<script>
	var configuracoes = JSON.parse('<?php echo $configJson; ?>');
</script>

<button class="btn btn-primary" data-toggle="tooltip" title="
			Caso alguns dispositivos não tenham sido encontrados, ao clicar nesse comando será feita uma nova busca na rede para encontrá-los. Os dispositivos já encontrados não sofrerãoimpacto." 
			onClick="buscarDispositivos()">
	Buscar novamente </button>

<table class="table col-sm-12" id="TabelaMonitoriamento">
	<thead>
	  <tr>
		<th class='colMac'>Mac Addrress</th>
		<th class='colStatus'>Status</th>
		<th class='colHistorio'>Histórico</th>
		<th class='colIP'>IP</th>
		<th class='colLocal'>Localização</th>
		<th class='colObs'>Obs</th>
		<th class='colComandos'>Comandos</th>
		<th class='colSensor'>Sensor</th>
	</tr>
	</thead>
	<tbody></tbody>
</table>

<?php
$pagemaincontent = ob_get_contents ();
ob_end_clean ();
$titulo = "Monitoramento";
include ("master.php");
?>
