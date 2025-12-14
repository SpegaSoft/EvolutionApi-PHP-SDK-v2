<?php
// // Libraries/EvolutionApi/autoload_evolution.php

// // 🛑 IMPORTANTE: Asume que la constante ROOT_DIR fue definida en Config/Config.php
// if (!defined('ROOT_DIR')) {
//     // Si ROOT_DIR no existe, usamos una ruta relativa segura para la carga inicial.
//     // Pero si falla, el error es en el require de index.php.
//     define('ROOT_DIR', realpath(__DIR__ . '/../..') . DIRECTORY_SEPARATOR); 
// }

// spl_autoload_register(function ($className) {
    
//     $namespacePrefix = 'Libraries\\EvolutionApi\\'; 
    
//     if (strpos($className, $namespacePrefix) === 0) {
        
//         // 1. Convertir el namespace a una ruta de archivo
//         // Ej: 'Libraries\EvolutionApi\EvolutionApiClient' -> 'Libraries/EvolutionApi/EvolutionApiClient.php'
//         $fileRelative = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

//         // 2. Construir la ruta final
//         // La ruta generada (Libraries/EvolutionApi/EvolutionApiClient.php) 
//         // es relativa a la carpeta raíz (ROOT_DIR).
//         $fileToLoad = ROOT_DIR . $fileRelative; 
        
//         // 3. Verificar y cargar
//         if (file_exists($fileToLoad)) {
//             require_once($fileToLoad);
//         } else {
//              // 🚨 DEBUGGING
//              // file_put_contents('/tmp/autoload_debug.log', "Failed to load: " . $fileToLoad . "\n", FILE_APPEND);
//         }
//     }
// });

spl_autoload_register(function ($className) {
    
    // Solo manejamos clases que comiencen con el namespace de nuestra librería
    if (strpos($className, 'Libraries\\EvolutionApi\\') === 0) {
        
        // 1. Convertir el namespace a una ruta de archivo
        // Ej: 'Libreries\EvolutionApi\EvolutionApiClient' -> 'Libreries/EvolutionApi/EvolutionApiClient.php'
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

        // 2. Ajustar la ruta para que sea relativa al directorio raíz de tu proyecto
        // NOTA: Asumimos que la carpeta "libreries" está en la raíz de tu proyecto.
        // Si tu carpeta se llama "libreries" (minúsculas) en el disco, usa 'libreries' aquí.
        // Usaremos el nombre que definiste:
        $fileToLoad = $file; 
        
        if (file_exists($fileToLoad)) {
            require_once($fileToLoad);
        }
    }
});