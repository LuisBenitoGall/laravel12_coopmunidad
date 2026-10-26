// resources/js/data/lineAwesomeIcons.js
// Genera la lista completa de iconos a partir del CSS de Line Awesome (Vite ?raw).
// Requiere que tengas instalada la librería y su CSS accesible.
// importado en tu app (o al menos en node_modules).
//
// Uso:
//   import LA_ICONS from "@/data/lineAwesomeIcons";
//   // LA_ICONS === ["la la-500px", "la la-home", ... ]

import cssText from "line-awesome/dist/line-awesome/css/line-awesome.min.css?raw";

const ICON_RE = /\.la(?:\s*)\.la-([a-z0-9-]+):before/g; // captura .la.la-ICON:before
const names = new Set();
let match;

while ((match = ICON_RE.exec(cssText)) !== null) {
  const name = match[1];
  if (!name) continue;
  names.add(`la la-${name}`);
}

// Exporta lista estable y ordenada alfabéticamente
const icons = Array.from(names).sort();

export default icons;
