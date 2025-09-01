import React from "react";
import { getInitials, pickBgColor, toAscii } from "@/Utils/avatar";

export default function Avatar({
  name = "", surname = "", url = null, size = 32, bg, textColor = "#fff", className = "",
}) {
  const initials = getInitials(name, surname);
  const bgColor  = bg || pickBgColor(initials);
  const s = Number(size);

  return (
    <span className={className} style={{ position:'relative', display:'inline-block', width:s, height:s }}>
      {url ? (
        <>
          <img
            src={url}
            alt={toAscii(`${name} ${surname}`) || "avatar"}
            style={{ position:'absolute', inset:0, width:s, height:s, objectFit:'cover', borderRadius:9999 }}
            onError={(e) => { e.currentTarget.style.display = 'none'; e.currentTarget.nextElementSibling.style.display='flex'; }}
          />
          <span
            style={{
              display:'none', alignItems:'center', justifyContent:'center',
              width:s, height:s, borderRadius:9999, background:bgColor, color:textColor,
              fontWeight:700, fontSize: Math.round(s*0.45)
            }}
          >
            {initials}
          </span>
        </>
      ) : (
        <span
          style={{
            display:'inline-flex', alignItems:'center', justifyContent:'center',
            width:s, height:s, borderRadius:9999, background:bgColor, color:textColor,
            fontWeight:700, fontSize: Math.round(s*0.45)
          }}
        >
          {initials}
        </span>
      )}
    </span>
  );
}
