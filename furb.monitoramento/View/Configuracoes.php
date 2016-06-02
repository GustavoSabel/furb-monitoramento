<?php 
	ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
?>

<script src="Js/configuracoes.js"></script>

<form id="configuracoes" action="" method="post" role="form"> 
	<div class="form-group col-sm-4">
		<label for="login">Login:</label>
		<input class="form-control" type="text" name="login" id="login" autocomplete="on" value='<?php echo $configuracao->Login ?>'> 
	</div>
	<div class="form-group col-sm-4">
		<label for="senha">Senha:</label>
		<input class="form-control" type="password" name="senha" id="senha" autocomplete="off" value='<?php echo $configuracao->Senha ?>'>
	</div>
	<div class="form-group col-sm-4">
		<label for="tempo">Tempo para desligar automaticamente: </label>

		<span class="tooltip">
			<img src="Resources/Imagens/help.png" alt="help" />
		  	<span class="tooltiptext">
		  		Tempo (em minutos) em que a sala está sem movimento detectado. 
		  		Após esse tempo, os aparelhos serã desligados automaticamente.
		  	</span>
		</span>
		
		<input class="form-control" type="text" name="tempo" id="tempo" autocomplete="off" 
		value='<?php echo $configuracao->TempoDesligamento ?>'> 
	</div>

	<div class="form-group col-sm-12">
		<button type="submit" class="btn btn-success">Cadastrar</button>
	</div>
</form>

<?php
	$pagemaincontent = ob_get_contents();
	ob_end_clean();
	$titulo = "Configurações";
	include("master.php");
?>