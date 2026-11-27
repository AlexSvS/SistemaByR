<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Buscador en español - Solr</title>
        <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main>
        <h1>🔎 Sistema de búsqueda en español</h1>
        <p>Crawling HTML, análisis lingüístico, búsqueda booleana, TF-IDF, facetas y sugerencias.</p>
        <form id="crawl">
            <input name="url" class="search" placeholder="URL pública HTML para rastrear" required>
            <input name="category" placeholder="Categoría (opcional)">
            <button>Rastrear e indexar</button>
        </form>
        <div id="crawlmsg"></div>
        <form id="search">
            <input id="q" class="search" autocomplete="off" placeholder="Buscar: ingeniería AND software" required>
            <select id="cat"><option value="">Todas las categorías</option></select>
            <button>Buscar</button>
        </form>
        <div id="suggestions"></div>
        <aside id="correction"></aside>
        <div id="facets"></div>
        <div id="results"></div>
    </main>
    <script src="app.js"></script>
</body>
</html>
