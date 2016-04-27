<?php ob_start(); ?>

<script src="Resources/monitoramento.js"></script>
<link rel="stylesheet" type="text/css" href="Resources/monitoramento.css">

<button onClick="buscarDispositivos()">Buscar novamente</button>
<!--  <button onClick="ConectarNovamente()">Tentar conectar novamente</button> -->
<table id="TabelaMonitoriamento">
	<tr>
		<th>Mac Addrress</th>
		<th>Status</th>
		<th>IP</th>
		<th>Localização</th>
		<th>Observação</th>
		<th>Comandos</th>
		<th>Sensor</th>
	</tr>
</table>
<br>
<br>
<div id="EspStatus"></div>

<?php
$pagemaincontent = ob_get_contents ();
ob_end_clean ();
$titulo = "Monitoramento";
include ("master.php");
?>
