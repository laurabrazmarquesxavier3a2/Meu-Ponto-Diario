// =========================
// ALERTA INSANO
// =========================

const form = document.getElementById('reportForm');
const alertBox = document.getElementById('alertBox');

form.addEventListener('submit', function(e){

    e.preventDefault();

    alertBox.classList.add('show');

    // SOM
    const audio = new Audio(
        'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3'
    );

    audio.volume = 0.25;
    audio.play();

    // VIBRAÇÃO
    if(navigator.vibrate){
        navigator.vibrate([120,60,120]);
    }

    // CONFETE

    for(let i = 0; i < 120; i++){

        const confetti = document.createElement('div');

        confetti.style.position = 'fixed';
        confetti.style.width = '10px';
        confetti.style.height = '10px';
        confetti.style.borderRadius = '50%';

        confetti.style.left = Math.random() * window.innerWidth + 'px';

        confetti.style.top = '-20px';

        confetti.style.background = `
            hsl(${Math.random() * 360}, 100%, 50%)
        `;

        confetti.style.zIndex = '999999';

        confetti.style.pointerEvents = 'none';

        document.body.appendChild(confetti);

        const duration = Math.random() * 3000 + 2000;

        confetti.animate([

            {
                transform:'translateY(0) rotate(0deg)',
                opacity:1
            },

            {
                transform:`
                translateY(${window.innerHeight + 200}px)
                translateX(${Math.random() * 300 - 150}px)
                rotate(${Math.random() * 720}deg)
                `,
                opacity:0
            }

        ],{

            duration:duration,
            easing:'cubic-bezier(.2,.8,.2,1)'

        });

        setTimeout(() => {

            confetti.remove();

        }, duration);

    }

    setTimeout(() => {

        alertBox.classList.remove('show');

    }, 3500);

    form.reset();

});

// =========================
// PARALLAX INSANO
// =========================

document.addEventListener('mousemove', (e) => {

    const x = e.clientX / window.innerWidth;
    const y = e.clientY / window.innerHeight;

    document.querySelector('.light1').style.transform =
    `translate(${x * 40}px, ${y * 40}px)`;

    document.querySelector('.light2').style.transform =
    `translate(-${x * 35}px, -${y * 35}px)`;

});

// =========================
// INPUT FX
// =========================

const inputs = document.querySelectorAll(
    '.form-control, .form-select'
);

inputs.forEach(input => {

    input.addEventListener('focus', () => {

        input.animate([

            {
                transform:'scale(1)'
            },

            {
                transform:'scale(1.02)'
            },

            {
                transform:'scale(1)'
            }

        ],{

            duration:250

        });

    });

});

// =========================
// CARD ENTRANCE
// =========================

const cards = document.querySelectorAll('.report-item');

cards.forEach((card, index) => {

    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';

    setTimeout(() => {

        card.style.transition =
        'all .7s cubic-bezier(.22,1,.36,1)';

        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';

    }, 400 + (index * 180));

});

// =========================
// MAGNETIC BUTTON
// =========================

const button = document.querySelector('.btn-report');

button.addEventListener('mousemove', (e) => {

    const rect = button.getBoundingClientRect();

    const x = e.clientX - rect.left - rect.width / 2;
    const y = e.clientY - rect.top - rect.height / 2;

    button.style.transform =
    `translate(${x * 0.12}px, ${y * 0.25}px)`;

});

button.addEventListener('mouseleave', () => {

    button.style.transform = 'translate(0,0)';

});