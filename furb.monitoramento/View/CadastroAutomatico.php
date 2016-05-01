<?php ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
	$configJson = json_encode($configuracao);
?>

<script src="Resources/cadastroAutomatico.js"></script>

<div class="status"></div>
<table class='tabela' style="width: 100%" id="DispositivosEncontratos">
	<tr>
		<th>Comando</th>
		<th>Mac Address</th>
		<th>IP</th>
		<th>Local</th>
		<th>Observação</th>
	</tr>
</table>
<br>
<div id="EspStatus"></div>

<?php
$pagemaincontent = ob_get_contents ();
ob_end_clean ();
$titulo = "Cadastro automático";
include ("master.php");
?>