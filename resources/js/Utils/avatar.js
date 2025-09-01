// Utils/avatar.js

/**
 * Quita acentos/diacríticos y convierte a ASCII básico.
 */
export function toAscii(str = "") {
  return (str || "")
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")   // diacríticos
    .replace(/[^\x00-\x7F]/g, "");     // no-ASCII
}

/**
 * Iniciales a partir de nombre y apellido (o nombre completo).
 */
export function getInitials(name = "", surname = "") {
  const n = toAscii(String(name).trim()).split(/\s+/)[0] || "";
  const s = toAscii(String(surname).trim()).split(/\s+/)[0] || "";

  const i1 = n.charAt(0).toUpperCase();
  const i2 = s.charAt(0).toUpperCase();

  return (i1 + i2).trim() || (i1 || i2) || "?";
}

/**
 * Mapa de colores deterministas por letra (inspirado en tu ERP).
 * Devuelve un HEX de 6 dígitos a partir de una letra [A-Z].
 */
const letterMap = {
  A: "11EE96", B: "22DD5A", C: "33CC3C", D: "44BB2B", E: "55AA1A",
  F: "669966", G: "EE9900", H: "DD8800", I: "CC7700", J: "BB6600",
  K: "AA5500", L: "994444", M: "FF1111", N: "11FFFF", O: "4499CC",
  P: "55AAEE", Q: "66BBFF", R: "77CCFF", S: "88DDFF", T: "99EEFF",
  U: "996666", V: "AA5599", W: "BB44CC", X: "CC33FF", Y: "DD22EE",
  Z: "EE11DD",
};

/**
 * Color de fondo estable a partir de iniciales.
 * Si no hay letra mapeada, usa un hash sobre el texto para elegir
 * un color de una paleta fallback.
 */
const fallbackPalette = [
  "#6366F1", "#22C55E", "#F59E0B", "#EF4444", "#0EA5E9",
  "#10B981", "#D946EF", "#F97316", "#84CC16", "#14B8A6",
];

export function pickBgColor(initials = "") {
  const first = (initials || "").charAt(0).toUpperCase();
  if (letterMap[first]) return `#${letterMap[first]}`;

  // Fallback determinista por hash
  const txt = initials || "A";
  let h = 0;
  for (let i = 0; i < txt.length; i++) {
    h = (h * 31 + txt.charCodeAt(i)) >>> 0;
  }
  return fallbackPalette[h % fallbackPalette.length];
}

/**
 * Genera el HTML del avatar (para Tabulator formatter).
 * - Si hay `url`, pinta <img> con fallback a iniciales si falla la carga.
 * - Si no hay `url`, pinta círculo con iniciales.
 */
export function renderAvatarHTML({
  name = "", surname = "", url = null, size = 32, bg = null, textColor = "#fff",
} = {}) {
  const initials = getInitials(name, surname);
  const bgColor = bg || pickBgColor(initials);
  const s = Number(size);

  if (url) {
    // Imagen + fallback a iniciales al fallar
    return `
      <span style="position:relative;display:inline-block;width:${s}px;height:${s}px;">
        <img src="${url}"
             style="position:absolute;inset:0;width:${s}px;height:${s}px;object-fit:cover;border-radius:9999px;"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" />
        <span style="display:none;align-items:center;justify-content:center;width:${s}px;height:${s}px;border-radius:9999px;background:${bgColor};color:${textColor};font-weight:700;font-size:${Math.round(s*0.45)}px;">
          ${initials}
        </span>
      </span>
    `;
  }

  // Sólo iniciales
  return `
    <span style="display:inline-flex;align-items:center;justify-content:center;width:${s}px;height:${s}px;border-radius:9999px;background:${bgColor};color:${textColor};font-weight:700;font-size:${Math.round(s*0.45)}px;">
      ${initials}
    </span>
  `;
}
