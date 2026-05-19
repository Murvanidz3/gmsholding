const body = document.body;
const navbar = document.querySelector("#navbar");
const menuToggle = document.querySelector("#menuToggle");
const mobileMenu = document.querySelector("#mobileMenu");
const revealEls = document.querySelectorAll(".reveal");
const counters = document.querySelectorAll("[data-counter]");
const cursorDot = document.querySelector(".cursor-dot");
const cursorRing = document.querySelector(".cursor-ring");
const testimonials = document.querySelectorAll(".testimonial");
const carouselButtons = document.querySelectorAll("[data-carousel]");

let testimonialIndex = 0;
let counterStarted = false;

body.classList.add("loading");

window.addEventListener("load", () => {
  window.setTimeout(() => {
    body.classList.add("loaded");
    body.classList.remove("loading");
  }, 650);
});

const setNavbarState = () => {
  navbar.classList.toggle("nav-scrolled", window.scrollY > 20);
};

setNavbarState();
window.addEventListener("scroll", setNavbarState, { passive: true });

menuToggle.addEventListener("click", () => {
  const isOpen = mobileMenu.classList.toggle("open");
  menuToggle.classList.toggle("active", isOpen);
  menuToggle.setAttribute("aria-expanded", String(isOpen));
  body.classList.toggle("menu-open", isOpen);
});

mobileMenu.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", () => {
    mobileMenu.classList.remove("open");
    menuToggle.classList.remove("active");
    menuToggle.setAttribute("aria-expanded", "false");
    body.classList.remove("menu-open");
  });
});

const animateCounter = (element) => {
  const target = Number(element.dataset.counter);
  const duration = 1500;
  const start = performance.now();

  const tick = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    element.textContent = Math.round(target * eased).toLocaleString("en-US");

    if (progress < 1) {
      requestAnimationFrame(tick);
    }
  };

  requestAnimationFrame(tick);
};

const revealObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;

      entry.target.classList.add("visible");

      if (!counterStarted && entry.target.closest("#about")) {
        counterStarted = true;
        counters.forEach(animateCounter);
      }

      revealObserver.unobserve(entry.target);
    });
  },
  { threshold: 0.16, rootMargin: "0px 0px -8% 0px" }
);

revealEls.forEach((element, index) => {
  element.style.transitionDelay = `${Math.min(index % 6, 5) * 70}ms`;
  revealObserver.observe(element);
});

if (window.matchMedia("(pointer: fine)").matches) {
  let dotX = 0;
  let dotY = 0;
  let ringX = 0;
  let ringY = 0;

  window.addEventListener("mousemove", (event) => {
    dotX = event.clientX;
    dotY = event.clientY;
    cursorDot.style.transform = `translate(${dotX}px, ${dotY}px) translate(-50%, -50%)`;
  });

  const renderCursor = () => {
    ringX += (dotX - ringX) * 0.16;
    ringY += (dotY - ringY) * 0.16;
    cursorRing.style.transform = `translate(${ringX}px, ${ringY}px) translate(-50%, -50%)`;
    requestAnimationFrame(renderCursor);
  };

  renderCursor();

  document.querySelectorAll("a, button, .service-card, .project-card").forEach((element) => {
    element.addEventListener("mouseenter", () => body.classList.add("cursor-active"));
    element.addEventListener("mouseleave", () => body.classList.remove("cursor-active"));
  });
}

document.querySelectorAll(".magnetic").forEach((element) => {
  element.addEventListener("mousemove", (event) => {
    const rect = element.getBoundingClientRect();
    const x = event.clientX - rect.left - rect.width / 2;
    const y = event.clientY - rect.top - rect.height / 2;
    element.style.transform = `translate(${x * 0.08}px, ${y * 0.18}px)`;
  });

  element.addEventListener("mouseleave", () => {
    element.style.transform = "";
  });
});

const showTestimonial = (nextIndex) => {
  testimonials[testimonialIndex].classList.remove("active");
  testimonialIndex = (nextIndex + testimonials.length) % testimonials.length;
  testimonials[testimonialIndex].classList.add("active");
};

carouselButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const direction = button.dataset.carousel === "next" ? 1 : -1;
    showTestimonial(testimonialIndex + direction);
  });
});

window.setInterval(() => showTestimonial(testimonialIndex + 1), 5200);

window.addEventListener(
  "scroll",
  () => {
    const offset = window.scrollY * 0.08;
    document.documentElement.style.setProperty("--scroll-light", `${offset}px`);
  },
  { passive: true }
);
