<?php
header('Content-Type: application/json; charset=utf-8');
$SOLR='http://solr:8983/solr/es_search';
function solr($path,$params=[],$method='GET',$body=null){
 global $SOLR; $url=$SOLR.$path;
 if($params) $url.='?'.http_build_query($params);
 $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]);
 if($method==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/json']);curl_setopt($ch,CURLOPT_POSTFIELDS,$body);}
 $out=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
 if($out===false || $code>=400) throw new Exception("Solr HTTP $code: $err $out");
 return json_decode($out,true);
}
function respond($data){echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
try{
 $action=$_GET['action']??'';
 if($action==='crawl'){
  $url=filter_var($_POST['url']??'',FILTER_VALIDATE_URL); if(!$url) throw new Exception('URL inválida');
  if(!in_array(parse_url($url,PHP_URL_SCHEME),['http','https'])) throw new Exception('Solo HTTP/HTTPS');
  $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_TIMEOUT=>15,CURLOPT_USERAGENT=>'SolrSpanishSearch/1.0']);
  $html=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);$ctype=curl_getinfo($ch,CURLINFO_CONTENT_TYPE);curl_close($ch);
  if(!$html || $code>=400 || stripos((string)$ctype,'text/html')===false) throw new Exception('No se pudo obtener HTML (HTTP '.$code.')');
  libxml_use_internal_errors(true);$dom=new DOMDocument();$dom->loadHTML('<?xml encoding="utf-8">'.$html);$xp=new DOMXPath($dom);
  foreach($xp->query('//script|//style|//noscript|//nav|//footer') as $n)$n->parentNode->removeChild($n);
  $title=trim($xp->evaluate('string(//title)'));if(!$title)$title=$url;
  $text=preg_replace('/\s+/u',' ',trim($dom->textContent));$text=mb_substr($text,0,100000);
  $id=hash('sha256',$url);$category=trim($_POST['category']??'General')?:'General';
  $doc=['id'=>$id,'url'=>$url,'title'=>$title,'content'=>$text,'suggest'=>$title.' '.$text,'category'=>$category,'timestamp'=>gmdate('Y-m-d\TH:i:s\Z')];
  solr('/update/json/docs',['commit'=>'true'],'POST',json_encode($doc,JSON_UNESCAPED_UNICODE));
  respond(['ok'=>true,'title'=>$title,'chars'=>mb_strlen($text)]);
 }
 if($action==='search'){
  $q=trim($_GET['q']??'');$category=trim($_GET['category']??'');
  if(!$q) respond(['response'=>['numFound'=>0,'docs'=>[]],'facets'=>[],'suggestions'=>[]]);
  $params=['q'=>$q,'defType'=>'lucene','df'=>'content','q.op'=>'AND','fl'=>'id,title,url,category,content,score','rows'=>20,'hl'=>'true','hl.fl'=>'content','hl.simple.pre'=>'<b>','hl.simple.post'=>'</b>','hl.fragsize'=>180,'facet'=>'true','facet.field'=>'category','facet.limit'=>20,'facet.mincount'=>1,'wt'=>'json'];
  if($category)$params['fq']='category:"'.str_replace('"','',$category).'"';
  $data=solr('/select',$params);
  $terms=solr('/terms',['terms.fl'=>'suggest','terms.prefix'=>mb_strtolower($q),'terms.limit'=>8,'wt'=>'json']);
  $suggestions=$terms['terms']['suggest']??[];
  respond(['response'=>$data['response'],'facets'=>$data['facet_counts']['facet_fields']['category']??[],'highlighting'=>$data['highlighting']??[],'suggestions'=>$suggestions]);
 }
 if($action==='terms'){
  $data=solr('/terms',['terms.fl'=>'suggest','terms.limit'=>200,'wt'=>'json']);
  respond($data['terms']['suggest']??[]);
 }
 throw new Exception('Acción no válida');
}catch(Throwable $e){http_response_code(400);respond(['error'=>$e->getMessage()]);}
