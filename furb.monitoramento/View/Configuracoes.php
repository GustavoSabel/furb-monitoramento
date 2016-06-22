<?php 
	ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
?>

<script src="Js/configuracoes.js"></script>

<form id="configuracoes" action="" method="post" role="form"> 
	<div class="form-group col-sm-4">
		<label for="login">Usuário:</label>
		<input 
			class="form-control" 
			placeholder="ex: olimex" 
			type="text" 
			name="login" 
			id="login" 
			autocomplete="on" 
			required 
			title="Será utilizado para se conectar ao dispositivo que está na sala"
			value='<?php echo $configuracao->Login ?>'>
	</div>
	<div class="form-group col-sm-4">
		<label for="senha">Senha:</label>
		<input 
			class="form-control" 
			type="password" 
			name="senha" 
			id="senha" 
			autocomplete="off" 
			required
			title="Será utilizado para se conectar ao dispositivo que está na sala"
			value='<?php echo $configuracao->Senha ?>'>
	</div>
	<div class="form-group col-sm-4">
		<label for="tempo">Tempo para desligar automaticamente (Em min.): </label>
		<input 
			class="form-control" 
			placeholder="ex: Para 30 min, colocar 1800" 
			type="text" 
			name="tempo" 
			id="tempo" 
			autocomplete="off" 
			required
			title="Tempo limite em minutos que a sala pode ficar sem movimento. Ao ultrapassar esse valor, a sala será considerada vazia e os aparelhos eletrônicos serão desligados automaticamente"
			value='<?php echo $configuracao->TempoDesligamento ?>'> 
	</div>

	<div class="form-group col-sm-12">
		<button type="submit" class="btn btn-success">Gravar</button>
	</div>
</form>

<?php
	$pagemaincontent = ob_get_contents();
	ob_end_clean();
	$titulo = "Configurações";
	include("master.php");
?>