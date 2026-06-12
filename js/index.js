/*
========================================
AOS
========================================
*/

AOS.init({

  duration: 800,
  once: true

});

/*
========================================
PARALLAX HERO IMAGE
========================================
*/

const heroImage =
document.querySelector('.hero-image');

document.addEventListener('mousemove', (e) => {

  if(!heroImage) return;

  const x =
  (window.innerWidth / 2 - e.pageX) / 45;

  const y =
  (window.innerHeight / 2 - e.pageY) / 45;

  heroImage.style.transform =
  `translate(${x}px, ${y}px)`;

});

/*
========================================
SCROLL NAV SHADOW
========================================
*/

window.addEventListener('scroll', () => {

  const nav =
  document.querySelector('nav');

  if(window.scrollY > 40){

    nav.style.boxShadow =
    '0 10px 30px rgba(0,0,0,.08)';

  }else{

    nav.style.boxShadow =
    'none';

  }

});

/*
========================================
HOVER 3D CARDS
========================================
*/

const cards =
document.querySelectorAll(
'.feature-card, .pricing-card'
);

cards.forEach(card => {

  card.addEventListener('mousemove', (e) => {

    const rect =
    card.getBoundingClientRect();

    const x =
    e.clientX - rect.left;

    const y =
    e.clientY - rect.top;

    const rotateY =
    ((x / rect.width) - 0.5) * 10;

    const rotateX =
    ((y / rect.height) - 0.5) * -10;

    card.style.transform =
    `
    perspective(1000px)
    rotateX(${rotateX}deg)
    rotateY(${rotateY}deg)
    translateY(-8px)
    `;

  });

  card.addEventListener('mouseleave', () => {

    card.style.transform =
    'perspective(1000px) rotateX(0) rotateY(0)';

  });

});

/*
========================================
FLOAT HERO IMAGE
========================================
*/

let floatY = 0;
let direction = 1;

setInterval(() => {

  if(!heroImage) return;

  floatY += 0.3 * direction;

  if(floatY > 10) direction = -1;
  if(floatY < -10) direction = 1;

  heroImage.style.marginTop =
  `${floatY}px`;

}, 16);

/*
========================================
SMOOTH SCROLL LINKS
========================================
*/

document.querySelectorAll('a[href^="#"]')
.forEach(link => {

  link.addEventListener('click', (e) => {

    e.preventDefault();

    const id =
    link.getAttribute('href');

    const target =
    document.querySelector(id);

    if(target){

      target.scrollIntoView({

        behavior:'smooth'

      });

    }

  });

});
