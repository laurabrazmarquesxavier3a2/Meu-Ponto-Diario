<script src="js/js.js"></script>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Meu Ponto Diário – Gestão de RH</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="css/style-landing.css" />
  </head>
  <body> 
    <!-- NAVBAR -->
    <?php include 'topnav.php'; ?>

    <!-- ─── HERO ─────────────────────────────────── -->
    <section class="hero">
      <div class="hero-text">
        <h1>Gestão de RH rápida e segura.</h1>
        <p>
          Simplifique ponto, jornada e relatórios com segurança e assertividade,
          reduzindo erros trabalhistas.
        </p>
        <a href="login.php" class="btn-primary">Começar agora</a>
      </div>

      <div class="hero-image">
  
      <img
        src="img/save-time.png"
        alt="Save Time"
        class="hero-img-custom"
    >
      </div>
    </section>

    <!-- ─── FEATURE CARDS ─────────────────────────── -->
    <section class="features">
      <div class="feature-card">
        <div class="feature-icon">📄</div>
        <div>
          <h3>Gestão de Documentos</h3>
          <p>Centralize holerites e documentos com segurança.</p>
        </div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">⏱</div>
        <div>
          <h3>Controle de Horários e Horas Extras</h3>
          <p>Acompanhe jornadas e banco de horas em tempo real.</p>
        </div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">📋</div>
        <div>
          <h3>Histórico de ponto</h3>
          <p>Acesse o histórico completo de batidas de ponto.</p>
        </div>
      </div>
    </section>

    <!-- ─── PRICING ───────────────────────────────── -->
    <section class="pricing-section">
      <div class="section-header">
        <div class="tag">💳 Planos e Preços</div>
        <h2>Escolha o modelo ideal para sua empresa</h2>
        <p>
          Flexibilidade total: pague de forma fixa ou escale conforme sua equipe
          cresce.
        </p>
      </div>

      <div class="pricing-grid">
 <!-- CARD 1: Pequenas Empresas -->
<a href="cadastro-empresa.php" class="card-link">
<div class="pricing-card card-light featured">

    <div class="plan-icon">🏢</div>

    <div class="plan-name">
        Plano Pequeno Porte
    </div>

    <div class="plan-desc">
        Ideal para empresas em fase de crescimento que precisam organizar
        funcionários, ponto eletrônico, férias, licenças e banco de horas
        em um único sistema.
    </div>

    <hr class="divider" />

    <ul class="check-list">

        <li class="check-item">
            <span class="check-icon">✓</span>
            De 10 a 49 usuários
        </li>

        <li class="check-item">
            <span class="check-icon">✓</span>
            Gestão de férias e licenças
        </li>

        <li class="check-item">
            <span class="check-icon">✓</span>
            Banco de horas
        </li>

        <li class="check-item">
            <span class="check-icon">✓</span>
            Relatórios automáticos
        </li>

    </ul>

</div>
</a>


<!-- CARD 2: Médias Empresas -->
<a href="cadastro-empresa.php" class="card-link">
<div class="pricing-card card-dark featured">

    <div class="badge">⭐ Recomendado</div>

    <div class="plan-icon">🏬</div>

    <div class="plan-name">
        Plano Médio Porte
    </div>

    <div class="plan-desc">
        Desenvolvido para empresas com maior volume de colaboradores,
        múltiplas lojas e necessidade de gestão centralizada do RH.
    </div>

    <hr class="divider" />

    <ul class="check-list">

        <li class="check-item">
            <span class="check-icon">✓</span>
            De 50 a 100 usuários
        </li>

        <li class="check-item">
            <span class="check-icon">✓</span>
            Gestão de férias e licenças
        </li>

        <li class="check-item">
            <span class="check-icon">✓</span>
            Banco de horas
        </li>


        <li class="check-item">
            <span class="check-icon">✓</span>
            Suporte prioritário
        </li>

    </ul>

</div>
</a>
    </section>

    <div class="footer-band">
      <div>
        <h3>Pronto para transformar seu RH?</h3>
        <p>Experimente grátis por 14 dias. Sem cartão de crédito.</p>
      </div>
      <a href="cadastro-empresa.php" class="btn-primary" style="white-space: nowrap"
        >Criar conta gratuita</a
      >
    </div>

    <footer>© 2026 Meu Ponto Diário · Todos os direitos reservados</footer>
  </body>
</html>
