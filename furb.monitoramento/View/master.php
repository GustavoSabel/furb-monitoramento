<html>
<head>
<script src="Js/webSocket.js"></script>
<script src="Js/commons.js"></script>
<script src="Js/jquery-2.2.2.min.js"></script>

<link rel="stylesheet" type="text/css" href="lib/bootstrap-3.3.6.min.css">
<script type="text/javascript" src="lib/bootstrap-3.3.6.min.js"></script>

<script src="Js/monitoramento.js"></script>
<link rel="stylesheet" type="text/css" href="Resources/monitoramento.css">
<link rel="stylesheet" type="text/css" href="Resources/main.css">

<!--<link rel="stylesheet" type="text/css" href="Resources/estilo.css">-->
<title><?php echo $titulo;?></title>
</head>
<body>
	<div class="titulo_pagina">
		<h1 id="titulo_pagina"><?php echo $titulo;?></h1>
	</div>

    <nav class="navbar navbar-light">
        <a class="navbar-brand" href="Index.php">Início</a>
        <a class="navbar-brand" href="CadastroManual.php">Cadastrar manualmente</a>
        <a class="navbar-brand" href="CadastroAutomatico.php">Cadastrar automaticamente</a>
        <a class="navbar-brand" href="Configuracoes.php">Configurações</a>
	</nav>

	<div id="mensagensSistema"></div>

	<div id="conteudo">
      <?php
        echo $pagemaincontent;
      ?>
    </div>

  	<div class="espStatus" id="EspStatus"></div>
</body>
</html>

