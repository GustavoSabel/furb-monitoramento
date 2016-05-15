<?php ob_start(); ?>

<script src="Js/cadastroManual.js"></script>

<form id="formCadastro" action="" method="post" role="form"> 
	<div class="form-group col-sm-12">
		<label for="macaddress">Mac Address:</label>
		<input class="form-control" type="text" name="macaddress" id="macaddress" autocomplete="off">
	</div>
	<div class="form-group col-sm-12">
		<label for="localizacao">Localização:</label>
		<input class="form-control" type="text" name="localizacao" id="localizacao" autocomplete="off">
	</div>
	<div class="form-group col-sm-12">
		<label for="observacao">Observação:</label>
		<input class="form-control" type="text" name="observacao" id="observacao" autocomplete="off"> 
	</div>

	<div class="form-group col-sm-12">
		<button type="submit" id="btnCadastrar" class="btn btn-success">Cadastrar</button>
		<button id="btnCancelar" onClick="limpar()" class="btn btn-danger">Cancelar</button>
	</div>
</form>


<table class="table col-sm-12" id="DispositivosCadastrados">
	<thead>
	  	<tr>
			<th>Comandos</th>
			<th>Mac Addrress</th>
			<th>Localização</th>
			<th>Observacao</th>
		</tr>
	</thead>
	<tbody>	</tbody>
</table>
<?php
	$pagemaincontent = ob_get_contents();
	ob_end_clean();
	$titulo = "Cadastro manual";
	include("master.php");
?>
