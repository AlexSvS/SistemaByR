# Sistema de búsqueda y recuperación en español con Apache Solr

## Requisitos
- Docker Desktop instalado y ejecutándose.
- Internet para descargar imágenes y rastrear páginas públicas.

## Ejecutar (Windows PowerShell, Linux o macOS)
Desde la carpeta descomprimida:
```bash
docker compose up -d --build
```
Crear el core usando el configset del proyecto:
```bash
docker compose exec solr solr create -c es_search -d /opt/solr/server/solr/configsets/es_search
```
Si el comando indica que ya existe, no repetirlo. Abrir:
- Interfaz: http://localhost:8080
- Solr Admin: http://localhost:8983/solr

## Funciones
1. Crawling: pega una URL HTML pública y presiona **Rastrear e indexar**.
2. Búsqueda: texto normal o booleana `software AND ingeniería`, `software OR programación`, `software NOT hardware`.
3. Preprocesamiento español: tokenización, minúsculas, stopwords, sinónimos y stemming Snowball Spanish.
4. Relevancia: ClassicSimilarity de Solr (TF-IDF).
5. Autocompletado: vocabulario de términos indexados.
6. Corrección: distancia Levenshtein para sugerir términos cercanos.
7. Facetas: categoría; se pueden usar como filtro.
8. Snippets: título, score y fragmentos destacados.
9. Sinónimos: editar `solr/conf/synonyms.txt`; recrear el core para que los cambios sean cargados.
10. Índice invertido: Solr almacena documentos, términos y postings en su índice.

## Notas
- El crawler procesa HTML estático y limita texto a 100,000 caracteres. No ejecuta JavaScript.
- Rastrea sitios que permitan el acceso automatizado y evita páginas privadas.
- Si quieres borrar índice y reiniciar:
```bash
docker compose down -v
docker compose up -d --build
docker compose exec solr solr create -c es_search -d /opt/solr/server/solr/configsets/es_search
```
