/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./includes/**/*.php",
    "./sections/**/*.php",
    "./admin/**/*.php",
    "./assets/js/**/*.js",
  ],
  theme: {
    container: {
      center: true,
      padding: { DEFAULT: "1.25rem", lg: "2rem" },
      screens: { sm: "640px", md: "768px", lg: "1024px", xl: "1280px", "2xl": "1320px" },
    },
    extend: {
      colors: {
        // Premium construction palette: Yellow + Dark
        primary: {
          DEFAULT: "#FFC400", // brand yellow
          50: "#FFF8E1",
          100: "#FFECB3",
          400: "#FFD23F",
          500: "#FFC400",
          600: "#E0A800",
          700: "#B98900",
        },
        dark: {
          DEFAULT: "#16181D", // near-black base
          800: "#1C1F26",
          700: "#23272F",
          600: "#2C313B",
          500: "#3A404C",
        },
        muted: "#8A9099",
        line: "rgba(255,255,255,0.08)",
      },
      fontFamily: {
        // Heading = condensed/strong, Body = clean sans
        heading: ['"Oswald"', "system-ui", "sans-serif"],
        body: ['"Inter"', "system-ui", "sans-serif"],
      },
      fontSize: {
        "display": ["clamp(2.75rem, 6vw, 5.5rem)", { lineHeight: "1.02", letterSpacing: "-0.01em" }],
        "section": ["clamp(2rem, 4vw, 3rem)", { lineHeight: "1.08" }],
      },
      letterSpacing: { tightest: "-0.02em", wider2: "0.18em" },
      maxWidth: { "8xl": "1320px" },
      boxShadow: {
        card: "0 20px 50px -20px rgba(0,0,0,0.45)",
        glow: "0 10px 40px -10px rgba(255,196,0,0.45)",
      },
      transitionTimingFunction: {
        "out-expo": "cubic-bezier(0.16, 1, 0.3, 1)",
      },
      keyframes: {
        "fade-up": {
          "0%": { opacity: "0", transform: "translateY(30px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        "ken-burns": {
          "0%": { transform: "scale(1)" },
          "100%": { transform: "scale(1.12)" },
        },
        "marquee": {
          "0%": { transform: "translateX(0)" },
          "100%": { transform: "translateX(-50%)" },
        },
      },
      animation: {
        "fade-up": "fade-up 0.8s var(--tw-ease, cubic-bezier(0.16,1,0.3,1)) both",
        "ken-burns": "ken-burns 7s ease-out both",
        "marquee": "marquee 28s linear infinite",
      },
    },
  },
  plugins: [],
};
