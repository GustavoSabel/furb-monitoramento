<html>
<head>
<script src="Js/webSocket.js"></script>
<script src="Js/commons.js"></script>
<script src="Js/jquery-2.2.2.min.js"></script>
<link rel="stylesheet" type="text/css" href="Resources/estilo.css">
<title><?php echo $titulo;?></title>
</head>
<body>
	<h1 id="titulo_pagina"><?php echo $titulo;?></h1>
		<div id="menus">
			<a href="Index.php">Início</a>
			<a href="CadastroManual.php">Cadastrar manualmente</a>
			<a href="CadastroAutomatico.php">Cadastrar automaticamente</a>
			<a href="Configuracoes.php">Configurações</a>
		</div>
		<div id="mensagensSistema"></div>
		<div id="conteudo">
          <?php
            echo $pagemaincontent;
          ?>
        </div>
        <br>
        <div id="EspStatus"></div>
</body>
</html>
