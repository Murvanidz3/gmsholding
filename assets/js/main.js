/* =========================================================================
   GMS — Core interactions (Vanilla JS, no dependencies except Swiper CDN)
   ========================================================================= */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", () => {
    initHeroSlider();
    initTestimonialsSlider();
    initStickyHeader();
    initMobileMenu();
    initScrollReveal();
    initBackToTop();
    initCounters();
    initParallax();
  });

  /* ----------------------------------------------------------------------
     1. HERO SLIDER — smooth momentum + autoplay
  ---------------------------------------------------------------------- */
  function initHeroSlider() {
    const el = document.querySelector(".hero-swiper");
    if (!el || typeof Swiper === "undefined") return;

    new Swiper(el, {
      loop: true,
      speed: 1100,
      grabCursor: true,
      effect: "slide",
      parallax: true,
      autoplay: { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
      keyboard: { enabled: true },
      pagination: { el: ".hero-swiper .swiper-pagination", clickable: true },
      navigation: {
        nextEl: ".hero-swiper .swiper-button-next",
        prevEl: ".hero-swiper .swiper-button-prev",
      },
      on: {
        init() {
          document.addEventListener("visibilitychange", () => {
            if (!this.autoplay) return;
            document.hidden ? this.autoplay.stop() : this.autoplay.start();
          });
        },
      },
    });
  }

  /* ----------------------------------------------------------------------
     1b. TESTIMONIALS SLIDER — responsive, auto-height
  ---------------------------------------------------------------------- */
  function initTestimonialsSlider() {
    const el = document.querySelector(".testimonials-swiper");
    if (!el || typeof Swiper === "undefined") return;

    new Swiper(el, {
      loop: true,
      speed: 800,
      grabCursor: true,
      autoHeight: false,
      spaceBetween: 24,
      slidesPerView: 1,
      autoplay: { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true },
      pagination: { el: ".testimonials-pagination", clickable: true },
      breakpoints: { 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } },
    });
  }

  /* ----------------------------------------------------------------------
     2. STICKY HEADER — solid + shrink after scroll
  ---------------------------------------------------------------------- */
  function initStickyHeader() {
    const header = document.getElementById("site-header");
    if (!header) return;

    const stuck = ["fixed", "top-0", "bg-dark/95", "backdrop-blur", "shadow-card", "border-b", "border-line"];
    const floating = ["absolute", "top-11"];

    const apply = () => {
      if (window.scrollY > 80) {
        header.classList.remove(...floating);
        header.classList.add(...stuck, "animate-fade-up");
      } else {
        header.classList.add(...floating);
        header.classList.remove(...stuck, "animate-fade-up");
      }
    };
    apply();
    window.addEventListener("scroll", apply, { passive: true });
  }

  /* ----------------------------------------------------------------------
     3. MOBILE MENU — slide-in drawer
  ---------------------------------------------------------------------- */
  function initMobileMenu() {
    const menu = document.getElementById("mobile-menu");
    const panel = document.getElementById("mobile-panel");
    const toggle = document.getElementById("nav-toggle");
    if (!menu || !panel || !toggle) return;

    const open = () => {
      menu.classList.remove("invisible", "opacity-0");
      panel.classList.remove("translate-x-full");
      document.body.style.overflow = "hidden";
    };
    const close = () => {
      menu.classList.add("opacity-0");
      panel.classList.add("translate-x-full");
      document.body.style.overflow = "";
      setTimeout(() => menu.classList.add("invisible"), 300);
    };

    toggle.addEventListener("click", open);
    menu.querySelectorAll("[data-close]").forEach((b) => b.addEventListener("click", close));
    document.addEventListener("keydown", (e) => e.key === "Escape" && close());
  }

  /* ----------------------------------------------------------------------
     4. SCROLL REVEAL — IntersectionObserver, staggered
  ---------------------------------------------------------------------- */
  function initScrollReveal() {
    const items = document.querySelectorAll(".reveal");
    if (!items.length || !("IntersectionObserver" in window)) {
      items.forEach((el) => el.classList.add("is-visible"));
      return;
    }
    const io = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry, i) => {
          if (entry.isIntersecting) {
            const el = entry.target;
            setTimeout(() => el.classList.add("is-visible"), i * 120);
            obs.unobserve(el);
          }
        });
      },
      { threshold: 0.15, rootMargin: "0px 0px -10% 0px" }
    );
    items.forEach((el) => io.observe(el));
  }

  /* ----------------------------------------------------------------------
     5. BACK TO TOP
  ---------------------------------------------------------------------- */
  function initBackToTop() {
    const btn = document.getElementById("to-top");
    if (!btn) return;
    const show = ["opacity-100", "translate-y-0", "pointer-events-auto"];
    const hide = ["opacity-0", "translate-y-4", "pointer-events-none"];
    window.addEventListener(
      "scroll",
      () => {
        const on = window.scrollY > 600;
        on
          ? (btn.classList.add(...show), btn.classList.remove(...hide))
          : (btn.classList.add(...hide), btn.classList.remove(...show));
      },
      { passive: true }
    );
  }

  /* ----------------------------------------------------------------------
     6. COUNT-UP — animates [.counter][data-target] when in view
  ---------------------------------------------------------------------- */
  function initCounters() {
    const counters = document.querySelectorAll(".counter");
    if (!counters.length) return;

    const ease = (t) => 1 - Math.pow(1 - t, 3);
    const DURATION = 2000;

    const run = (el) => {
      const target = parseFloat(el.dataset.target) || 0;
      const decimals = (String(el.dataset.target).split(".")[1] || "").length;
      let start = null;

      const step = (ts) => {
        if (!start) start = ts;
        const p = Math.min((ts - start) / DURATION, 1);
        const val = target * ease(p);
        el.textContent = val.toLocaleString(undefined, {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals,
        });
        if (p < 1) requestAnimationFrame(step);
        else el.textContent = target.toLocaleString();
      };
      requestAnimationFrame(step);
    };

    if (!("IntersectionObserver" in window)) {
      counters.forEach(run);
      return;
    }
    const io = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            run(e.target);
            obs.unobserve(e.target);
          }
        });
      },
      { threshold: 0.4 }
    );
    counters.forEach((el) => io.observe(el));
  }

  /* ----------------------------------------------------------------------
     7. PARALLAX — translateY on [data-parallax], rAF-throttled
  ---------------------------------------------------------------------- */
  function initParallax() {
    const layers = document.querySelectorAll("[data-parallax]");
    if (!layers.length) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    let ticking = false;

    const update = () => {
      const vh = window.innerHeight;
      layers.forEach((el) => {
        const rect = el.getBoundingClientRect();
        if (rect.bottom < 0 || rect.top > vh) return;
        const speed = parseFloat(el.dataset.parallaxSpeed) || 0.2;
        const offset = (rect.top + rect.height / 2 - vh / 2) * speed;
        el.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
      });
      ticking = false;
    };

    const onScroll = () => {
      if (!ticking) {
        requestAnimationFrame(update);
        ticking = true;
      }
    };

    update();
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll, { passive: true });
  }
})();
