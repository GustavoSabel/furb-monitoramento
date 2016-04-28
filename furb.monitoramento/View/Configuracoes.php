<?php 
	ob_start(); 
	include_once '../Controller/ConfiguracoesController.php';
	$configuracao = Buscar();
?>

<script>
$(function(){
	$("#configuracoes").submit(function( event ) {
		$("#status").html("");

		var dados = {};
		dados["operacao"] = "atualizar";
		dados["login"] = $("#login").val();
		dados["senha"] = $("#senha").val();
		dados["tempo"] = $("#tempo").val();
		var dadosJson = JSON.stringify(dados);
		
		$.post(path_configuracoes, dadosJson, function (data){
			showStatus(data);
			atualizarGrid();
		}, 'json');
		event.preventDefault();
	});
});

function showStatus(data) {
	$("#status").html(data.mensagem);
}
</script>

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
	<div id="status"></div>
</div>

<?php
	$pagemaincontent = ob_get_contents();
	ob_end_clean();
	$titulo = "Configurações";
	include("master.php");
?>