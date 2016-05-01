<?php 
	ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
?>

<script src="Js/configuracoes.js"></script>

<div>
	<form id="configuracoes" action="Teste.php" method="post">
		Login: <br> 
		<input type="text" name="login" id="login" autocomplete="on" value='<?php echo $configuracao->Login ?>'> <br> 
		Senha: <br> 
		<input type="password" name="senha" id="senha" autocomplete="off" value='<?php echo $configuracao->Senha ?>'> <br> 
		Tempo para desligar automaticamente: 

		<span class="tooltip">
			<img src="Resources/Imagens/help.png" alt="help" />
		  	<span class="tooltiptext">
		  		Tempo (em minutos) em que a sala está sem movimento detectado. 
		  		Após esse tempo, os aparelhos serã desligados automaticamente.
		  	</span>
		</span>
		
		 <br> 
		<input type="text" name="tempo" id="tempo" autocomplete="off" value='<?php echo $configuracao->TempoDesligamento ?>'> <br> 
		<input type="submit" Value="Cadastrar" />
	</form>
</div>

<?php
	$pagemaincontent = ob_get_contents();
	ob_end_clean();
	$titulo = "Configurações";
	include("master.php");
?>