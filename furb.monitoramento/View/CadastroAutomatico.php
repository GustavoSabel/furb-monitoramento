<?php ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
	$configJson = json_encode($configuracao);
?>

<script>var configuracoes = JSON.parse('<?php echo $configJson; ?>');</script>
<script src="Js/cadastroAutomatico.js"></script>

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
<?php
$pagemaincontent = ob_get_contents ();
ob_end_clean ();
$titulo = "Cadastro automático";
include ("master.php");
?>