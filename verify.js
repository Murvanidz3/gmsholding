#!/usr/bin/env node
/* GMS project integrity checker — NUL bytes, JS syntax, JSON validity. */
const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

const ROOT = __dirname;
const SKIP = new Set(["node_modules", ".git"]);
const EXTS = [".php", ".js", ".json", ".css"];

const files = [];
(function walk(dir) {
  for (const name of fs.readdirSync(dir)) {
    if (SKIP.has(name)) continue;
    const fp = path.join(dir, name);
    const st = fs.statSync(fp);
    if (st.isDirectory()) walk(fp);
    else if (EXTS.includes(path.extname(name))) files.push(fp);
  }
})(ROOT);

const rel = (f) => path.relative(ROOT, f);
let fails = 0, checks = 0;
const fail = (f, msg) => { fails++; console.log("  [FAIL] " + rel(f) + " - " + msg); };

console.log("GMS verify - scanning " + files.length + " files\n");

console.log("1) NUL-byte check (detects M: truncation/corruption)");
for (const f of files) {
  checks++;
  const idx = fs.readFileSync(f).indexOf(0);
  if (idx !== -1) fail(f, "NUL byte at offset " + idx);
}

console.log("2) JS syntax check");
for (const f of files.filter((f) => f.endsWith(".js"))) {
  checks++;
  try { execSync('node --check "' + f + '"', { stdio: "pipe" }); }
  catch (e) {
    const line = (e.stderr ? e.stderr.toString() : e.message).split("\n").find((l) => l.trim()) || "syntax error";
    fail(f, line.trim());
  }
}

console.log("3) JSON validity check");
for (const f of files.filter((f) => f.endsWith(".json"))) {
  checks++;
  try { JSON.parse(fs.readFileSync(f, "utf8")); }
  catch (e) { fail(f, "invalid JSON: " + e.message); }
}

console.log("");
if (fails === 0) {
  console.log("[OK] ALL CHECKS PASSED  (" + checks + " checks across " + files.length + " files)");
  process.exit(0);
} else {
  console.log("[X] " + fails + " problem(s) found - fix before deploying.");
  process.exit(1);
}
