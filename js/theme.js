document.addEventListener('DOMContentLoaded', () => {

    // BOTÕES
    const darkBtn = document.getElementById('darkMode');
    const lightBtn = document.getElementById('lightMode');

    // verifica se encontrou
    console.log(darkBtn);
    console.log(lightBtn);

    // TEMA SALVO
    if(localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }

    // DARK MODE
    if(darkBtn){
        darkBtn.addEventListener('click', () => {

            document.body.classList.add('dark-mode');

            localStorage.setItem('theme', 'dark');

        });
    }

    // LIGHT MODE
    if(lightBtn){
        lightBtn.addEventListener('click', () => {

            document.body.classList.remove('dark-mode');

            localStorage.setItem('theme', 'light');

        });
    }

});