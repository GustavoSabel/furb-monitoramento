<?php ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
	$configJson = json_encode($configuracao);
?>

<script>var configuracoes = JSON.parse('<?php echo $configJson; ?>');</script>
<script src="Js/cadastroAutomatico.js"></script>

<table class="table col-sm-12" id="DispositivosEncontratos">
	<thead>
	  	<tr>
			<th>Comando</th>
			<th>Mac Address</th>
			<th>IP</th>
			<th>Local</th>
			<th>Observação</th>
		</tr>
	</thead>
	<tbody></tbody>
</table>

<?php
$pagemaincontent = ob_get_contents ();
ob_end_clean ();
$titulo = "Cadastro automático";
include ("master.php");
?>