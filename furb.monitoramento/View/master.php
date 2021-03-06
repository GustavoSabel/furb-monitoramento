<html>
<head>
<script src="Js/webSocket.js"></script>
<script src="Js/commons.js"></script>

<script src="lib/jquery-2.2.3.min.js"></script>
<link rel="stylesheet" type="text/css" href="lib/bootstrap-3.3.6.min.css">
<script type="text/javascript" src="lib/bootstrap-3.3.6.min.js"></script>
<script type="text/javascript" src="lib/bootstrap-notify.min.js"></script>

<link rel="stylesheet" type="text/css" href="Resources/main.css">
<link rel="stylesheet" type="text/css" href="lib/animate.css">

<!--<link rel="stylesheet" type="text/css" href="Resources/estilo.css">-->
<title><?php echo $titulo;?> </title>
</head>
<body>
	<div class="titulo_pagina">
		<h1 id="titulo_pagina"><?php echo $titulo;?> </h1>
		<img src="Resources/Imagens/logo-furb.gif" alt="FURB" class="logo_furb" /> 
		<div class="clear"></div>
	</div>

    <nav class="navbar navbar-light">
        <a class="navbar-brand" href="Index.php">Monitoramento</a>
        <a class="navbar-brand" href="CadastroManual.php">Cadastrar manualmente</a>
        <a class="navbar-brand" href="CadastroAutomatico.php">Cadastrar automaticamente</a>
        <a class="navbar-brand" href="Configuracoes.php">Configurações</a>
		<a class="navbar-brand" href="../OlimexApp/index.html">Aplicação Olimex</a>
	</nav>
	
	<div id="conteudo">
      <?php
        echo $pagemaincontent;
      ?>
    </div>
</body>
</html>

