<?php ob_start(); ?>

<script src="Resources/monitoramento.js"></script>
<link rel="stylesheet" type="text/css"
	href="Resources/monitoramento.css">

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
