// assets/js/scroll-animation.js

document.addEventListener("DOMContentLoaded", () => {
  // Elementos que terão animação
  const elements = document.querySelectorAll(
    ".hero-text, .feature-card, .pricing-card, .section-header, .footer-band"
  );

  // Estado inicial
  elements.forEach((el) => {
    el.style.opacity = "0";
    el.style.transform = "translateY(50px)";
    el.style.transition =
      "opacity 0.8s ease-out, transform 0.8s ease-out";
  });

  // Revela os elementos ao scroll
  const revealOnScroll = () => {
    const windowHeight = window.innerHeight;

    elements.forEach((el) => {
      const elementTop = el.getBoundingClientRect().top;

      if (elementTop < windowHeight - 100) {
        el.style.opacity = "1";
        el.style.transform = "translateY(0)";
      }
    });
  };

  // Evento de scroll
  window.addEventListener("scroll", revealOnScroll);

  // Executa ao carregar
  revealOnScroll();
});