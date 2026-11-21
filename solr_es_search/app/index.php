<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Buscador en español - Solr</title>
<style>
body{font-family:system-ui,Arial;background:#eef2f8;color:#172033;margin:0}main{max-width:1000px;margin:30px auto;padding:24px;background:white;border-radius:16px;box-shadow:0 5px 24px #0001}h1{color:#3346a0}input,select,button{padding:11px;border:1px solid #bbc4d5;border-radius:8px;font-size:1rem}button{background:#4255bd;color:white;border:0;cursor:pointer}form{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}.search{flex:1;min-width:260px}#suggestions{display:flex;gap:6px;flex-wrap:wrap}.chip{background:#edf0ff;border:0;color:#3346a0;padding:6px 10px}.result{border-top:1px solid #ddd;padding:16px 0}.result h3{margin:4px 0}.url{color:#13804b;word-break:break-all}.snippet{color:#4b5563}.meta{font-size:.85rem;color:#666}.error{color:#b42318}aside{background:#f6f7fb;padding:12px;border-radius:8px;margin:12px 0}
</style></head><body><main>
<h1>🔎 Sistema de búsqueda en español</h1><p>Crawling HTML, análisis lingüístico, búsqueda booleana, TF-IDF, facetas y sugerencias.</p>
<form id="crawl"><input name="url" class="search" placeholder="URL pública HTML para rastrear" required><input name="category" placeholder="Categoría (opcional)"><button>Rastrear e indexar</button></form><div id="crawlmsg"></div>
<form id="search"><input id="q" class="search" autocomplete="off" placeholder="Buscar: ingeniería AND software" required><select id="cat"><option value="">Todas las categorías</option></select><button>Buscar</button></form>
<div id="suggestions"></div><aside id="correction"></aside><div id="facets"></div><div id="results"></div>
</main><script src="app.js"></script></body></html>
