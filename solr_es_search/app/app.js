const q=document.querySelector('#q'),
results=document.querySelector('#results'),
suggestions=document.querySelector('#suggestions'),
facets=document.querySelector('#facets'),
correction=document.querySelector('#correction'),
cat=document.querySelector('#cat');

let vocab=[];
async function api(params) {
    const r=await fetch('api.php?'+new URLSearchParams(params));
    return r.json()
}

async function loadVocab() {
    const d=await api({action:'terms'});
    vocab=Array.isArray(d)?d.filter((_,i)=>i%2===0):[];
}

loadVocab();
loadCategories();

async function loadCategories() {
    try {
        const categories = await api({ action: 'categories' });

        cat.innerHTML = '<option value="">Todas las categorías</option>';

        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            cat.appendChild(option);
        });

    } catch (error) {
        console.error('Error al cargar categorías:', error);
    }
}
cat.addEventListener('change', () => {
    search();
});

function levenshtein(a,b) {
    let dp=Array.from({length:b.length+1},(_,i)=>[i]);
    
    for(let j=0;j<=a.length;j++)
        dp[0][j]=j;
    
    for(let i=1;i<=b.length;i++)
        for(let j=1;j<=a.length;j++)
            dp[i][j]=Math.min(
                dp[i-1][j]+1,
                dp[i][j-1]+1,
                dp[i-1][j-1]+(a[j-1]===b[i-1]? 0:1)
            );
    return dp[b.length][a.length]
}

q.addEventListener('input',async()=>{
    const term=q.value.trim().toLowerCase();
    suggestions.innerHTML='';
    
    if(term.length<2) return;
    
    const data=await api({action:'search',q:term});
    
    (data.suggestions||[]).forEach(word=>{
        const b=document.createElement('button');
        b.className='chip';
        b.textContent=word;
        
        b.onclick=()=>{
            q.value=word;
            search()
        };
        
        suggestions.append(b)
    });
});

document.querySelector('#search').onsubmit=e=>{
    e.preventDefault();
    search()
};

async function search(){
    const data=await api({
        action:'search',
        q:q.value,
        category:cat.value
    });
    
    if(data.error){
        results.innerHTML='<p class="error">'+data.error+'</p>';
        return
    }
results.innerHTML='';
correction.innerHTML='';

const docs=data.response?.docs||[];
if(!docs.length){
    const words=q.value.toLowerCase().split(/\s+/);
    let best=null,dist=99;
    
    for(const w of words){
        for(const item of vocab){
            const d=levenshtein(w,item);
            if(d<dist&&d>0){
                best=item;
                dist=d
            }
        }
    }
    if(best&&dist<=2)
        correction.innerHTML=`¿Quisiste decir <button class="chip" onclick="q.value='${best}';search()">${best}</button>?`;
    
    results.innerHTML='<p>No se encontraron documentos.</p>';
    return
}

docs.forEach(d=>{
    const el=document.createElement('article');
    el.className='result';
    
    const snippet=(
        data.highlighting?.[d.id]?.content||[d.content||'']
    ).join(' … ').slice(0,500);
    
    el.innerHTML=`
        <h3>${escapeHtml(d.title||'Sin título')}</h3>
        <div class="url">${escapeHtml(d.url||'')}</div>
        <div class="meta">
        Score TF-IDF: ${Number(d.score).toFixed(4)} · 
        Categoría: ${escapeHtml(d.category||'')}
        </div>
        <p class="snippet">${snippet}</p>
        `;
        
        results.append(el)
    });

facets.innerHTML='';
const f=data.facets||[];

for(let i=0;i<f.length;i+=2){
    const name=f[i],count=f[i+1];
    const b=document.createElement('button');
    
    b.className='chip';
    b.textContent=`${name} (${count})`;
    
    b.onclick=()=>{
        cat.value=name;
        search()
    };
        facets.append(b)
    }
}

function escapeHtml(s){
    return String(s).replace(/[&<>"']/g,c=>({
        '&':'&amp;',
        '<':'&lt;',
        '>':'&gt;',
        '"':'&quot;',
        "'":'&#39;'
    }[c]))
}

document.querySelector('#crawl').onsubmit=async e=>{
    e.preventDefault();
    
    const fd=new FormData(e.target);
    const r=await fetch('api.php?action=crawl',{
        method:'POST',
        body:fd
    });
    
    const d=await r.json();
    document.querySelector('#crawlmsg').textContent=
    d.error||`Indexado: ${d.title} (${d.chars} caracteres)`;
    
    if(d.ok)e.target.reset()
};
