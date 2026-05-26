<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ponto do Funcionário</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/sidebarfunc.css">
    <link rel="stylesheet" href="../css/pontof.css">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- GOOGLE FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebarfunc.php'; ?>

    <!-- CONTEÚDO -->
    <div class="content">

        <!-- TÍTULO -->
        <div class="title">

            <h2>Ponto do Funcionário</h2>

            <p>
                Acompanhe seus horários, registros e desempenho diário
            </p>

        </div>

        <div class="top-content">

            <!-- ESQUERDA -->
            <div class="left-side">

                <!-- CARDS -->
                <div class="cards">

                    <div class="card-box">

                        <div>

                            <span>Entrada</span>

                            <strong>09:01:30</strong>

                        </div>

                        <i class="fa-solid fa-right-to-bracket"></i>

                    </div>

                    <div class="card-box">

                        <div>

                            <span>Início Intervalo</span>

                            <strong>12:00:00</strong>

                        </div>

                        <i class="fa-solid fa-mug-hot"></i>

                    </div>

                    <div class="card-box">

                        <div>

                            <span>Fim Intervalo</span>

                            <strong>13:00:00</strong>

                        </div>

                        <i class="fa-solid fa-briefcase"></i>

                    </div>

                    <div class="card-box">

                        <div>

                            <span>Saída</span>

                            <strong>18:00:00</strong>

                        </div>

                        <i class="fa-solid fa-right-from-bracket"></i>

                    </div>

                </div>

                <!-- MENSAGEM -->
                <div class="message-box">

                    <div class="message-title">

                        <i class="fa-solid fa-star"></i>

                        Mensagem do Dia

                    </div>

                    <div id="messageText" class="message-text"></div>

                </div>

                <!-- CALENDÁRIOS -->
                <div class="calendar-section">

                    <!-- SEMANA -->
                    <div class="calendar-box">

                        <div class="calendar-title">

                            <i class="fa-regular fa-calendar"></i>

                            Semana Atual

                        </div>

                        <div class="calendar-item">
                            <strong>Segunda</strong>
                            <span>08:55 às 18:02</span>
                        </div>

                        <div class="calendar-item">
                            <strong>Terça</strong>
                            <span>09:01 às 18:00</span>
                        </div>

                        <div class="calendar-item">
                            <strong>Quarta</strong>
                            <span>08:48 às 18:03</span>
                        </div>

                        <div class="calendar-item">
                            <strong>Quinta</strong>
                            <span>09:00 às 18:00</span>
                        </div>

                        <div class="calendar-item">
                            <strong>Sexta</strong>
                            <span>08:59 às 17:58</span>
                        </div>

                    </div>

                    <!-- MÊS -->
                    <div class="calendar-box">

                        <div class="calendar-title">

                            <i class="fa-regular fa-calendar-days"></i>

                            Registros do Mês

                        </div>

                        <div class="calendar-item">
                            <strong>01/05</strong>
                            <span>08:59 às 18:00</span>
                        </div>

                        <div class="calendar-item">
                            <strong>02/05</strong>
                            <span>09:02 às 18:10</span>
                        </div>

                        <div class="calendar-item">
                            <strong>03/05</strong>
                            <span>08:50 às 17:58</span>
                        </div>

                        <div class="calendar-item">
                            <strong>04/05</strong>
                            <span>09:00 às 18:00</span>
                        </div>

                        <div class="calendar-item">
                            <strong>05/05</strong>
                            <span>08:47 às 18:15</span>
                        </div>

                    </div>

                </div>

            </div>

            <!-- RELÓGIO -->
            <div class="clock-box">

                <i class="fa-regular fa-clock"></i>

                <div id="clock" class="clock"></div>

                <div id="date" class="date"></div>

            </div>

        </div>

    </div>

<script>

function updateClock(){

    const now = new Date();

    const time = now.toLocaleTimeString('pt-BR');

    const date = now.toLocaleDateString('pt-BR',{

        weekday:'long',
        day:'numeric',
        month:'long',
        year:'numeric'

    });

    document.getElementById('clock').innerText = time;
    document.getElementById('date').innerText = date;

}

setInterval(updateClock,1000);

updateClock();

/* MENSAGENS */

const messages = [

    "Você está indo muito bem hoje 🚀",
    "Seu esforço faz diferença 💙",
    "Mais um dia produtivo pela frente ✨",
    "Pequenos avanços geram grandes resultados 📈",
    "Continue firme, você consegue 🔥",
    "Seu trabalho importa muito 👏",
    "Organização é o caminho do sucesso 📅"

];

let currentMessage = 0;

function changeMessage(){

    document.getElementById("messageText").innerText =
    messages[currentMessage];

    currentMessage++;

    if(currentMessage >= messages.length){

        currentMessage = 0;

    }

}

changeMessage();

setInterval(changeMessage, 4000);

</script>

</body>
</html>