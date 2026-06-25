/* =========================================================================
   GMS Admin — Polygon Mapper (click-to-plot, no external library)
   Usage in any admin form:
     <div class="gms-mapper" data-image="URL" data-target="#poly_input"></div>
     <input type="hidden" name="polygon" id="poly_input" value="[[x,y],...]">
   Captures percentage coordinates (0-100, 1 decimal) into the hidden input
   as JSON. Buttons: Undo, Clear. Polygon auto-closes visually.
   ========================================================================= */
(function () {
  "use strict";
  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".gms-mapper").forEach(setup);
  });

  function setup(box) {
    const imgUrl = box.dataset.image || "";
    const target = document.querySelector(box.dataset.target);
    if (!target) return;

    let points = [];
    try { points = JSON.parse(target.value || "[]"); } catch (e) { points = []; }
    if (!Array.isArray(points)) points = [];

    box.innerHTML = `
      <div class="flex items-center justify-between mb-2">
        <span class="font-heading uppercase text-xs tracking-wider2 text-muted">Click on the image to plot the shape</span>
        <span class="flex gap-2">
          <button type="button" data-act="undo" class="border border-line text-muted hover:text-primary hover:border-primary px-3 py-1.5 text-xs uppercase tracking-wider2">Undo</button>
          <button type="button" data-act="clear" class="border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white px-3 py-1.5 text-xs uppercase tracking-wider2">Clear</button>
        </span>
      </div>
      <div class="relative border border-line bg-dark-700 select-none" data-stage>
        ${imgUrl ? `<img src="${imgUrl}" alt="" class="block w-full h-auto pointer-events-none" draggable="false">`
                 : `<div class="aspect-video grid place-items-center text-muted text-sm">Add an image URL above, then save to plot the map.</div>`}
        <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 w-full h-full cursor-crosshair" data-svg></svg>
      </div>
      <p class="text-muted text-xs mt-2"><span data-count>0</span> point(s) · coordinates are stored as percentages (responsive).</p>`;

    const svg = box.querySelector("[data-svg]");
    const stage = box.querySelector("[data-stage]");
    const countEl = box.querySelector("[data-count]");

    const SVGNS = "http://www.w3.org/2000/svg";
    function draw() {
      svg.innerHTML = "";
      if (points.length >= 2) {
        const poly = document.createElementNS(SVGNS, points.length >= 3 ? "polygon" : "polyline");
        poly.setAttribute("points", points.map((p) => p.join(",")).join(" "));
        poly.setAttribute("style", "fill:rgba(255,196,0,0.30);stroke:#FFC400;stroke-width:0.6");
        svg.appendChild(poly);
      }
      points.forEach((p, i) => {
        const dot = document.createElementNS(SVGNS, "circle");
        dot.setAttribute("cx", p[0]); dot.setAttribute("cy", p[1]);
        dot.setAttribute("r", "1.1");
        dot.setAttribute("style", "fill:#fff;stroke:#FFC400;stroke-width:0.5");
        svg.appendChild(dot);
      });
      countEl.textContent = points.length;
      target.value = JSON.stringify(points);
    }

    if (imgUrl) {
      svg.addEventListener("click", (ev) => {
        const r = stage.getBoundingClientRect();
        const x = +(((ev.clientX - r.left) / r.width) * 100).toFixed(1);
        const y = +(((ev.clientY - r.top) / r.height) * 100).toFixed(1);
        points.push([Math.max(0, Math.min(100, x)), Math.max(0, Math.min(100, y))]);
        draw();
      });
    }
    box.querySelector('[data-act="undo"]').addEventListener("click", () => { points.pop(); draw(); });
    box.querySelector('[data-act="clear"]').addEventListener("click", () => { points = []; draw(); });

    draw();
  }
})();
