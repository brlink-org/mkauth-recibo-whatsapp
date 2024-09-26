<?php
// INCLUI FUNÇÕES DE ADDONS -----------------------------------------------------------------------
// Este script inclui o arquivo 'addons.class.php', que contém funções ou classe
// auxiliares utilizadas no projeto. Isso permite o uso de funcionalidades extras.
include('addons.class.php');
?>
<!DOCTYPE html>
<html lang="pt-BR" class="has-navbar-fixed-top"> <!-- Define o idioma como português BR e adiciona classe para layout -->
<head>
    <!-- Define visualização para diferentes dispositivos -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Define o charset como ISO-8859-1 -->
    <meta charset="iso-8859-1">
    <!-- Define o título da página web -->
    <title>MK - AUTH :: <?php echo $Manifest->{'name'}; ?></title> <!-- Insere dinamicamente o nome do manifesto no título da página -->
    
    <!-- Inclui estilo CSS -->
    <link href="../../estilos/mk-auth.css" rel="stylesheet" type="text/css" /> <!-- Estilos do projeto -->
    <link href="../../estilos/font-awesome.css" rel="stylesheet" type="text/css" /> <!-- Estilos do Font Awesome, biblioteca de ícones -->
    
    <!-- Inclui bibliotecas JavaScript -->
    <script src="../../scripts/jquery.js"></script> <!-- Biblioteca jQuery para interações -->
    <script src="../../scripts/mk-auth.js"></script> <!-- Arquivo JavaScript -->
</head>
<body>
    <!-- Inclui componentes PHP -->
    <?php include('../../topo.php'); ?> <!-- Inclui o arquivo 'topo.php', que contém o cabeçalho do site -->
    <?php include('recibo.php'); ?> <!-- Inclui o arquivo 'recibo.php', que gera e exibe o recibo -->
    <?php include('../../baixo.php'); ?> <!-- Inclui o arquivo 'baixo.php', que contém o rodapé do site -->
    
    <!-- Inclui JavaScript adicional -->
    <script src="../../menu.js.hhvm"></script> <!-- Arquivo de script relacionado ao menu, com suporte para HHVM -->
</body>
</html>