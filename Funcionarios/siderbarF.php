<?php
// página atual (pra destacar o botão ativo)
$current = basename($_SERVER['PHP_SELF']);
?>
 <link rel="stylesheet" href="../css/style-funcionario.css">
<aside class="sidebar">

  <button class="menu <?php echo ($current == 'pontoF.php') ? 'active' : ''; ?>">
    <i class="bi bi-clock"></i> Registro do Ponto
  </button>

  <button class="menu <?php echo ($current == 'documentos.php') ? 'active' : ''; ?>">
    <i class="bi bi-file-earmark-text"></i> Solicitar Documen.
  </button>

  <button class="menu <?php echo ($current == 'pedidos.php') ? 'active' : ''; ?>">
    <i class="bi bi-folder"></i> Pedidos
  </button>

  <button class="menu <?php echo ($current == 'permissoes.php') ? 'active' : ''; ?>">
    <i class="bi bi-calendar-check"></i> Permissões
  </button>

  <button class="menu <?php echo ($current == 'seguranca.php') ? 'active' : ''; ?>">
    <i class="bi bi-shield-lock"></i> Segurança
  </button>

  <button class="menu <?php echo ($current == 'perfil.php') ? 'active' : ''; ?>">
    <i class="bi bi-person"></i> Perfil
  </button>

</aside>