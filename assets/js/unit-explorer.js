/* =========================================================================
   GMS — Interactive Unit Explorer (multi-instance, side-by-side)
   Each .ue-instance is an independent Project → Floor → Apartment explorer.
   Coordinates are percentages rendered into an SVG overlay with
   viewBox="0 0 100 100" preserveAspectRatio="none" => fully responsive.
   ========================================================================= */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", () => {
    const statusEl = document.getElementById("ue-statuses");
    let statuses = {};
    try { statuses = JSON.parse((statusEl && statusEl.textContent) || "{}"); } catch (e) {}

    document.querySelectorAll(".ue-instance").forEach((inst) => {
      const dataEl = inst.querySelector(".ue-data");
      const stage = inst.querySelector(".ue-stage");
      if (!dataEl || !stage) return;
      let project;
      try { project = JSON.parse(dataEl.textContent || "{}"); } catch (e) { return; }
      if (!project || !project.floors) return;
      initExplorer(stage, project, statuses);
    });
  });

  const PRIMARY = "#FFC400";

  function initExplorer(stage, project, statuses) {
    const state = { floorId: null };
    const byId = (arr, id) => (arr || []).find((x) => x.id === id);
    const pts = (poly) => (poly || []).map((p) => p.join(",")).join(" ");
    const sColor = (k) => (statuses[k] && statuses[k].color) || PRIMARY;
    const sLabel = (k) => (statuses[k] && statuses[k].label) || k;

    function buildingView() {
      const polys = project.floors
        .map((f) => `<polygon points="${pts(f.polygon)}" data-floor="${f.id}"
            style="fill:rgba(255,196,0,0);stroke:rgba(255,196,0,0);stroke-width:0.6;cursor:pointer;transition:fill .25s,stroke .25s" />`)
        .join("");
      stage.innerHTML = `
        <div class="relative select-none h-full">
          <img src="${project.building_image || ""}" alt="${project.name}" class="block w-full h-auto" draggable="false">
          <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 w-full h-full">${polys}</svg>
          <div class="ue-tip pointer-events-none absolute z-20 hidden bg-primary text-dark font-heading uppercase text-xs tracking-wider2 px-3 py-1.5 -translate-x-1/2 -translate-y-full"></div>
        </div>`;
      const tip = stage.querySelector(".ue-tip");
      stage.querySelectorAll("polygon").forEach((poly) => {
        const f = byId(project.floors, poly.dataset.floor);
        poly.addEventListener("mouseenter", () => {
          poly.style.fill = "rgba(255,196,0,0.35)"; poly.style.stroke = PRIMARY;
          tip.textContent = f.name; tip.classList.remove("hidden");
        });
        poly.addEventListener("mousemove", (ev) => {
          const r = stage.getBoundingClientRect();
          tip.style.left = ev.clientX - r.left + "px";
          tip.style.top = ev.clientY - r.top - 8 + "px";
        });
        poly.addEventListener("mouseleave", () => {
          poly.style.fill = "rgba(255,196,0,0)"; poly.style.stroke = "rgba(255,196,0,0)";
          tip.classList.add("hidden");
        });
        poly.addEventListener("click", () => { state.floorId = f.id; render(); });
      });
    }

    function floorView(floor) {
      const polys = floor.apartments
        .map((a) => { const c = sColor(a.status);
          return `<polygon points="${pts(a.polygon)}" data-apt="${a.id}"
            style="fill:${c}33;stroke:${c};stroke-width:0.8;cursor:pointer;transition:fill .25s" />`; })
        .join("");
      stage.innerHTML = `
        <div>
          <div class="flex items-center justify-between gap-3 p-3 border-b border-line">
            <button class="ue-back inline-flex items-center gap-2 font-heading uppercase text-xs tracking-wider2 text-muted hover:text-primary transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 18l-6-6 6-6"/></svg>Building
            </button>
            <span class="font-heading text-white uppercase text-xs truncate">${floor.name}</span>
          </div>
          <div class="relative select-none">
            <img src="${floor.plan_image || ""}" alt="${floor.name}" class="block w-full h-auto" draggable="false">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 w-full h-full">${polys}</svg>
          </div>
        </div>`;
      stage.querySelector(".ue-back").addEventListener("click", () => { state.floorId = null; render(); });
      stage.querySelectorAll("polygon").forEach((poly) => {
        const a = byId(floor.apartments, poly.dataset.apt); const c = sColor(a.status);
        poly.addEventListener("mouseenter", () => (poly.style.fill = c + "66"));
        poly.addEventListener("mouseleave", () => (poly.style.fill = c + "33"));
        poly.addEventListener("click", () => openModal(a, sColor, sLabel));
      });
    }

    function render() {
      if (state.floorId) {
        const f = byId(project.floors, state.floorId);
        if (f) return floorView(f);
      }
      buildingView();
    }
    render();
  }

  /* ---------------- Shared modal ---------------- */
  function openModal(a, sColor, sLabel) {
    const c = sColor(a.status);
    let modal = document.getElementById("ue-modal");
    if (!modal) {
      modal = document.createElement("div");
      modal.id = "ue-modal";
      modal.className = "fixed inset-0 z-[90] hidden items-center justify-center p-5";
      document.body.appendChild(modal);
    }
    modal.innerHTML = `
      <div class="absolute inset-0 bg-black/70" data-close></div>
      <div class="relative w-full max-w-3xl bg-dark-800 border border-line shadow-card grid sm:grid-cols-2">
        <div class="relative aspect-[4/3] sm:aspect-auto bg-dark-700">
          <img src="${a.render || ""}" alt="${a.code}" class="absolute inset-0 w-full h-full object-cover">
        </div>
        <div class="p-7 lg:p-8">
          <div class="flex items-center justify-between mb-5">
            <span class="font-heading text-2xl text-white uppercase">${a.code}</span>
            <span class="font-heading uppercase text-xs tracking-wider2 px-3 py-1.5 text-dark" style="background:${c}">${sLabel(a.status)}</span>
          </div>
          <div class="grid grid-cols-2 gap-px bg-line border border-line mb-6">
            <div class="bg-dark-800 p-4"><span class="block text-muted text-xs uppercase tracking-wider2 mb-1">Rooms</span><span class="font-heading text-white text-xl">${a.rooms ?? "-"}</span></div>
            <div class="bg-dark-800 p-4"><span class="block text-muted text-xs uppercase tracking-wider2 mb-1">Area</span><span class="font-heading text-white text-xl">${a.area ?? "-"} m²</span></div>
          </div>
          <span class="block text-muted text-xs uppercase tracking-wider2 mb-1">Price</span>
          <span class="block font-heading text-primary text-2xl mb-7">${a.price || "On request"}</span>
          <div class="flex gap-3">
            <a href="#contact" class="btn-primary !py-3" data-close>Enquire</a>
            <button class="btn-outline !py-3" data-close>Close</button>
          </div>
        </div>
        <button class="absolute top-3 right-3 grid place-items-center w-9 h-9 bg-dark/70 text-white hover:bg-primary hover:text-dark transition-colors" data-close aria-label="Close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
      </div>`;
    modal.classList.remove("hidden"); modal.classList.add("flex");
    document.body.style.overflow = "hidden";
    modal.querySelectorAll("[data-close]").forEach((el) =>
      el.addEventListener("click", () => {
        modal.classList.add("hidden"); modal.classList.remove("flex");
        document.body.style.overflow = "";
      })
    );
  }
})();
