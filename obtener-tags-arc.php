<?php

define("DOP", "desarrollo");
define("PATH_BASE","~/proyectos/migracionPEP/listado-tags-autores/");
define("PATH_FICHERO_JSON_TAGS", PATH_BASE."tags/");
define("PATH_FICHERO_JSON_DESTINOS", PATH_BASE."destinos/");
define("PATH_FICHERO_TXT_CDB_TAGS", PATH_BASE."tags/cdb/busqueda.txt");
define("PATH_FICHERO_TXT_CDB_DESTINOS", PATH_BASE."destinos/cdb/busqueda.txt");

define("PATH", "/mnt/filer/html/".DOP."/lib/php/elpais/path.html");
define("LIB_GESTION_FICHEROS", "/mnt/filer/html/".DOP."/lib/php/portal_unico/librerias/lib_gestion_ficheros.php");
//define("PATH_ABSOLUTO_DIRECTORIO_LIBRERIAS","/mnt/filer/html/".DOP."/lib/php/portal_unico/librerias");
define("PATH_ABSOLUTO_DIRECTORIO_LIBRERIAS_PORTAL_UNICO","/mnt/filer/html/".DOP."/lib/php/portal_unico/librerias");
define("USER_PASSWORD_API_ARC","2018-10:FSB3uOXhFni4QAlehAFiuyOIkWNCmMYqeJ6Q0Vx52KbGtcLtl7p7ZR2wf6U3Bhx_7V-D5uk32cvmlVn642nPEtrdqQcIKlRP");
define('PATH_ABSOLUTO_LIBRERIA_EXCEPCIONES',  PATH_ABSOLUTO_DIRECTORIO_LIBRERIAS . '/' . "c_excepciones.php");
define('PATH_ABSOLUTO_LIBRERIA_MENSAJES_ERROR', PATH_ABSOLUTO_DIRECTORIO_LIBRERIAS . '/' . "c_mensajes.php");

define("URL_BASE_SEARCH_TAGS","tags/v2/search/?website=el-pais&prefix=");
define("URL_BASE_CONTENT_PUBLISHED_ARTICLES","content/v4/search/published?website=el-pais&size=1&q=type:story+AND+");
define("SUB_QUERY_CONTENT_PUBLISHED_ARTICLES_TAG","taxonomy.tags.slug:");
define("SUB_QUERY_CONTENT_PUBLISHED_ARTICLES_AUTHOR","credits.by.slug:");

define("URL_BASE_SEARCH_AUTORES","author/v2/author-service/?website=el-pais&limit=25");

//https://api.prisa.arcpublishing.com/content/v4/search/published?website=el-pais&size=1&q=type:story+AND+credits.by.slug:guillermo-abril-a

define('HOST_API_PRODUCCION',"https://api.prisa.arcpublishing.com/");
define('HOST_API_SANDBOX',"https://api.sandbox.prisa.arcpublishing.com/");
//define('HOST_API_SANDBOX',"https://api.prisa.arcpublishing.com/");


define('SECCION_ELVIAJERO','internacional');
define ('NUMERO_RESULTADOS_QUERY_TAGS', 100);
define ('SEPARADOR_AUTORES',',');

//require_once PATH;
require_once LIB_GESTION_FICHEROS;


function existenNoticiasEnTagSeccion ($tag,$seccion,$hostAPI,$typeTag) {

    //return(true);
    // Crea un nuevo recurso cURL
    $curlObtenerNoticiasTag = curl_init();

    // Establece la URL y otras opciones apropiadas
    curl_setopt($curlObtenerNoticiasTag, CURLOPT_HEADER, 0);
    curl_setopt($curlObtenerNoticiasTag, CURLOPT_USERPWD, USER_PASSWORD_API_ARC);
    curl_setopt($curlObtenerNoticiasTag, CURLOPT_RETURNTRANSFER, 1);
    
    $subQueryTagOrAuthor = $typeTag == "author" ? SUB_QUERY_CONTENT_PUBLISHED_ARTICLES_AUTHOR : SUB_QUERY_CONTENT_PUBLISHED_ARTICLES_TAG;

    $subQuerySeccion = $seccion == "" ? "" : "+AND+taxonomy.sites._id:%22/".$seccion."%22";

    $urlPeticion = $hostAPI . URL_BASE_CONTENT_PUBLISHED_ARTICLES . $subQueryTagOrAuthor . $tag . $subQuerySeccion;
    echo $urlPeticion . "\n";
    curl_setopt($curlObtenerNoticiasTag, CURLOPT_URL, $urlPeticion);
    $result = curl_exec($curlObtenerNoticiasTag);        
    $response = json_decode($result, true);
    if($response["count"] > 0){
        return (true);
    }
    curl_close($curlObtenerNoticiasTag);
}


function array_sort_by(&$arrIni, $col, $order = SORT_ASC)
{
    $arrAux = array();
    foreach ($arrIni as $key=> $row)
    {
        $arrAux[$key] = is_object($row) ? $arrAux[$key] = $row->$col : $row[$col];
        $arrAux[$key] = strtolower($arrAux[$key]);
    }
    array_multisort($arrAux, $order, $arrIni);
    return $arrIni;
}



$hostAPI = (DOP == 'desarrollo') ? HOST_API_SANDBOX : HOST_API_PRODUCCION;

//Iniciación de recursos para obtener los tags
$curlObtenerTags = curl_init();

// Establece la URL y otras opciones apropiadas
curl_setopt($curlObtenerTags, CURLOPT_HEADER, 0);
curl_setopt($curlObtenerTags, CURLOPT_USERPWD, USER_PASSWORD_API_ARC);
curl_setopt($curlObtenerTags, CURLOPT_RETURNTRANSFER, 1);

$urlSearchTags = $hostAPI . URL_BASE_SEARCH_TAGS;

//$arrayTags = ("");
$tags4Search = "";
$idTag = 1;


//Iniciación de recursos para obtener los autores
$curlObtenerAutores = curl_init();

//$arrayAutores = ["a" => ""];
//$arrayAutores = [];
//$arrayTagsJson = [];
//$arrayDestinosJson = [];

$arrayAutores = ("");
$arrayTagsJson = ("");
$arrayDestinosJson = ("");

foreach( range('a','z') as $inicial) {
    $arrayAutores[$inicial] = array();    
}

//$arrayAutores['a']['slug']="slug";
//$arrayAutores["a"] = array("slug"=>"alberto_mendoza", "text"=>"Alberto Mendoza");
//$array_push($arrayAutores["a"],array("slug"=>"alberto_bueno", "text"=>"Alberto Bueno")); 

$idContAutores = 0;

//$array_push($arrayAutores['a'],"hola"); 

//var_dump($arrayAutores["a"]);

//array_push($pila, "manzana", "arándano");

//die();


curl_setopt($curlObtenerAutores, CURLOPT_HEADER, 0);
curl_setopt($curlObtenerAutores, CURLOPT_USERPWD, USER_PASSWORD_API_ARC);
curl_setopt($curlObtenerAutores, CURLOPT_RETURNTRANSFER, 1);
$urlSearchAutores = $hostAPI . URL_BASE_SEARCH_AUTORES;
$idNextBlock = "";
$idContAutores = 1;

$numeroMaximoIteracionesAutores = 1;
$numeroIteracionesAutores = 0;

//********INICIO OBTENER AUTORES*****************************
do {        
    $urlPeticion = $urlSearchAutores."&last=".$idNextBlock;
    curl_setopt($curlObtenerAutores, CURLOPT_URL, $urlPeticion);
    $result = curl_exec($curlObtenerAutores);        
    $response = json_decode($result, true);
    $arrayItems = $response["authors"];

    foreach ($arrayItems as $valor) {
        if (isset($valor["slug"]) AND isset($valor["byline"])){
            if (($valor["slug"] != "") AND (ctype_alnum(substr($valor["slug"],0,1))) AND ($valor["byline"] != "")) {
                if (existenNoticiasEnTagSeccion($valor["slug"],"",$hostAPI,"author")){
                    echo "existe alguna noticia para el autor " . $valor["slug"]."\n";
                    array_push($arrayAutores[strtolower(substr($valor["slug"],0,1))],array("slug" => $valor["slug"], "text" => $valor["byline"], "type" => "author"));
                    $tags4Search .= $valor["byline"].' '.'| {"id":"'.$idTag.'", "name":"'.$valor["byline"].'","slug":"'.$valor["slug"].'","description":"Autores"}'."\n";
                    $idTag++; 
                    $idContAutores++;
                } else {
                    echo "NO existen noticias para el autor " . $valor["slug"]."\n";
                }
            }
        }
    }
    if (isset($response["last"])){
        $idNextBlock = $response["last"];        
    }
    $numeroIteracionesAutores++;
} while (isset($response["last"]) && $numeroIteracionesAutores < $numeroMaximoIteracionesAutores);



$idNextBlock = "";
curl_close($curlObtenerAutores);



//echo "total autores: " . $idContAutores . "\n";
//echo "total iteraciones " . $numeroIteracionesAutores . "\n";

//$pathFicheroSalidaAutores = PATH_FICHERO_JSON_TAGS . "autores.json";
//f_crea_fichero_en_disco($pathFicheroSalidaAutores, json_encode($arrayAutores));

//********FIN OBTENER AUTORES*****************************


//******** INICIO OBTENER TAGS *****************************
//Recorremos todas las iniciales posibles para realizar peticiones al API usando esa inicial en la query
foreach( range('a','a') as $claveBusqueda)
{   
    //$arrayTagsJson = ("");
    //$arrayDestinosJson = ("");
    $arrayTagsJson = array();    
    $arrayDestinosJson = array();
    $contadorTagsEnLetra = 0;
    $contadorDestinos = 0;
    $idNextBlock = "";
    //Realizaremos peticiones al API de búsqueda de tags hasta que obtengamos todos los tags de una letra concreta (se devuelven en bloques de 100)
    do {        
        $urlPeticion = $urlSearchTags.$claveBusqueda."bu&size=".NUMERO_RESULTADOS_QUERY_TAGS."&from=".$idNextBlock;
        curl_setopt($curlObtenerTags, CURLOPT_URL, $urlPeticion);
        $result = curl_exec($curlObtenerTags);        
        $response = json_decode($result, true);
        $arrayItems = $response["Payload"]["items"];

        //Recorremos todos los tags existentes en un bloque devuelto por el API de búsqueda
        foreach ($arrayItems as $valor) {
            if (existenNoticiasEnTagSeccion($valor["slug"],"",$hostAPI,"tag")){

                echo "existe alguna noticia para el tag " . $valor["slug"]."\n";                

                $arrayTagsJson[$contadorTagsEnLetra]["slug"] = $valor["slug"];
                $arrayTagsJson[$contadorTagsEnLetra]["text"] = $valor["text"];
                $arrayTagsJson[$contadorTagsEnLetra]["type"] = "tag";
                $tags4Search .= $valor["text"].' '.'| {"id":"'.$idTag.'", "name":"'.$valor["text"].'","slug":"'.$valor["slug"].'","description":"'.$valor["description"].'"}'."\n";
                $contadorTagsEnLetra++;
                $idTag++;             
                if ($valor["description"] == "Lugares") {
                    if (existenNoticiasEnTagSeccion($valor["slug"],SECCION_ELVIAJERO,$hostAPI,"tag")){
                        echo "existen noticias en la sección " . SECCION_ELVIAJERO . " con el tag " . $valor["slug"] . "\n";
                        $arrayDestinosJson[$contadorDestinos]["slug"] = $valor["slug"];
                        $arrayDestinosJson[$contadorDestinos]["text"] = $valor["text"];                        
                        $contadorDestinos++;
                    }
                }
            } else {
                echo "NO existen noticias para el tag " . $valor["slug"]."\n";
            }
        }
        if (isset($response["Payload"]["next"])){
            $idNextBlock = $response["Payload"]["next"];        
        }
    } while (isset($response["Payload"]["next"]));

    //Unimos los tags obtenidos para la inicial en proceso a los autores de dicha inicial    
    foreach ($arrayAutores[$claveBusqueda] as $autor) {
        array_push($arrayTagsJson,$autor);
    }

    //Ordenamos el array de tags+autores por el slug
    array_sort_by($arrayTagsJson, 'slug', $order = SORT_ASC);    

    $pathFicheroSalidaTags = PATH_FICHERO_JSON_TAGS . $claveBusqueda .".json";
    $pathFicheroSalidaDestinos = PATH_FICHERO_JSON_DESTINOS . $claveBusqueda .".json";

    f_crea_fichero_en_disco($pathFicheroSalidaTags, json_encode($arrayTagsJson));
    f_crea_fichero_en_disco($pathFicheroSalidaDestinos, json_encode($arrayDestinosJson));
    f_crea_fichero_en_disco(PATH_FICHERO_TXT_CDB_TAGS, $tags4Search);    
}

curl_close($curlObtenerTags);

//******** FIN OBTENER TAGS *****************************
?>

