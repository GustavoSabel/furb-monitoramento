<?php ob_start(); ?>

<script src="Js/cadastroManual.js"></script>

<form id="formCadastro" action="" method="post" role="form"> 
	<div class="form-group col-sm-6">
		<label for="macaddress">Mac Address:</label>
		<input class="form-control" placeholder="Ex: 18:fe:34:a1:f5:a7" type="text" name="macaddress" id="macaddress" autocomplete="off" required>
	</div>
	<div class="form-group col-sm-6">
		<label for="localizacao">Localização:</label>
		<input class="form-control" placeholder="Ex: Campus I - S427" type="text" name="localizacao" id="localizacao" autocomplete="off" required>
	</div>
	<div class="form-group col-sm-12">
		<label for="observacao">Observação:</label>
		<textarea class="form-control" name="observacao" id="observacao" rows="4"></textarea> 
	</div>

	<div class="form-group col-sm-12">
		<button type="submit" id="btnCadastrar" class="btn btn-success">Cadastrar</button>
		<button type="button" id="btnCancelar" onClick="limpar()" class="btn btn-danger">Cancelar</button>
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
