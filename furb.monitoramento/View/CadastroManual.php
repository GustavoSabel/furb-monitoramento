<?php ob_start(); ?>

<script src="Resources/cadastroManual.js"></script>

<div>
	<form id="formCadastro" action="" method="post"> 
		Mac Address: <br> 
		<input type="text" name="macaddress" id="macaddress" autocomplete="off"> <br> 
		Localização: <br> 
		<input type="text" name="localizacao" id="localizacao" autocomplete="off"> <br> 
		Observação: <br> 
		<input type="text" name="observacao" id="observacao" autocomplete="off"> <br> 
		<input type="submit" id="btnCadastrar" Value="Cadastrar" />
	</form>
	<button id="btnCancelar" onClick="limpar()">Cancelar</button>
</div>
<br>
<table id="DispositivosCadastrados">
	<tr>
		<th>Comandos</th>
		<th>Mac Addrress</th>
		<th>Localização</th>
		<th>Observacao</th>
	</tr>
</table>
<?php
	$pagemaincontent = ob_get_contents();
	ob_end_clean();
	$titulo = "Cadastro manual";
	include("master.php");
?>
