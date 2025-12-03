<?php
// libreries/EvolutionApi/EvolutionApiClient.php

namespace Libraries\EvolutionApi; // Usamos el namespace Libreries\

// Importamos las clases compañeras del SDK
use Libraries\EvolutionApi\EvolutionApiConfig;
use Libraries\EvolutionApi\EvolutionApiHttpClient;

class EvolutionApiClient {

    /**
     * Normaliza y filtra el payload, aplicando opciones avanzadas y eliminando nulos/vacíos.
     */
    private function normalizePayload(array $payloadData, array $opcionesAvanzadas): array {
        // Valores predeterminados para las opciones avanzadas (según tu código original)
        $defaults = [
            'delay' => null, 
            'linkPreview' => null,
            'mentionsEveryOne' => null,
            'mentioned' => [], 
            'quoted' => null,
        ];

        // 1. Combinar opciones: defaults <- avanzadas <- datos principales
        $finalPayloadData = array_merge($defaults, $opcionesAvanzadas, $payloadData);

        // 2. Filtrado CRÍTICO: Eliminar nulos y arrays vacíos para evitar errores de API
        return array_filter($finalPayloadData, function($value) {
            return $value !== null && !(is_array($value) && empty($value));
        });
    }

    /**
     * Envía un mensaje de texto simple. (Sustituye a enviar_texto.php)
     */
    public function sendText(string $numero, string $texto, array $opcionesAvanzadas = []): array {
        
        $payloadData = [
            "number" => $numero, 
            "text" => $texto,
        ];
        
        // 1. Normalizar y filtrar el payload
        $finalPayload = $this->normalizePayload($payloadData, $opcionesAvanzadas);

        // 2. Obtener la URL y llamar al cliente HTTP
        $url = EvolutionApiConfig::getBaseUrl("/message/sendText");
        
        return EvolutionApiHttpClient::post($url, $finalPayload);
    }
    
    /**
     * Envía una imagen, documento, audio o video. (Sustituye a enviar_pdf.php y similar)
     */
    public function sendMedia(
        string $numero, 
        string $mediaUrl, 
        string $fileName, 
        string $mediatype, 
        string $mimetype,  
        string $caption = "", // <-- Ya es opcional
        array $opcionesAvanzadas = []
    ): array {

        $payloadData = [
            "number" => $numero, 
            "mediatype" => $mediatype, 
            "mimetype" => $mimetype, 
            "caption" => $caption, // <-- Usamos la variable aquí
            "media" => $mediaUrl, 
            "fileName" => $fileName,
        ];

        $finalPayload = $this->normalizePayload($payloadData, $opcionesAvanzadas);
        
        $url = EvolutionApiConfig::getBaseUrl("/message/sendMedia");

        return EvolutionApiHttpClient::post($url, $finalPayload);
    }
    
    /**
     * Envía una actualización de estado (mensaje de tipo "status"). (Sustituye a enviar_estado.php)
     * NOTA: El endpoint puede variar. He puesto un placeholder común para Evolution.
     */
    public function sendStatus(string $texto, array $opcionesAvanzadas = []): array {
        
        // En Evolution API, el endpoint de estado es a menudo /message/sendText con el número como 'status@broadcast' o similar.
        // Si tu API usa un endpoint dedicado, ajusta la URL y el payload aquí.
        
        $payloadData = [
            "text" => $texto,
            // Aquí puedes agregar un campo como "statusId" si tu API lo requiere
        ];
        
        $finalPayload = $this->normalizePayload($payloadData, $opcionesAvanzadas);
        
        // Endpoint placeholder. Revisa la documentación de tu Evolution API para el endpoint exacto de "status".
        $url = EvolutionApiConfig::getBaseUrl("/message/sendStatus"); 

        return EvolutionApiHttpClient::post($url, $finalPayload);
    }
}