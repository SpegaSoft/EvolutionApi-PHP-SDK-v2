<?php
// libreries/EvolutionApi/EvolutionApiConfig.php

namespace Libraries\EvolutionApi; // Usamos el namespace Libreries\

class EvolutionApiConfig {
    
    // Configuración de la API
    const API_URL = "http://31.97.175.37:8080";
    const API_TOKEN = "D2CF2860B8CA-4424-A804-E912FD0D63D4";
    const INSTANCE_KEY = "envios_wa_master";

    /**
     * Construye la URL completa del endpoint de la API.
     */
    public static function getBaseUrl(string $endpoint): string {
        // Ejemplo: "http://31.97.175.37:8080" + "/message/sendText" + "/envios_wa_master"
        return self::API_URL . $endpoint . "/" . self::INSTANCE_KEY;
    }
}