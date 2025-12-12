<?php

// libreries/EvolutionApi/EvolutionApiClient.php

namespace Libraries\EvolutionApi; 

use Libraries\EvolutionApi\EvolutionApiConfig;
use Libraries\EvolutionApi\EvolutionApiHttpClient;

class EvolutionApiClient {
    
    // Propiedades para almacenar la configuración y el cliente HTTP
    protected EvolutionApiConfig $config;
    protected EvolutionApiHttpClient $httpClient;

    /**
     * Constructor que requiere la inyección del objeto de configuración.
     */
    public function __construct(EvolutionApiConfig $config) {
        $this->config = $config;
        // Inicializa el cliente HTTP inyectándole la misma configuración
        $this->httpClient = new EvolutionApiHttpClient($config); 
    }
    
    // ... (Método normalizePayload se mantiene igual)

    private function normalizePayload(array $payloadData, array $opcionesAvanzadas): array {
        // ... (Tu código actual de normalizePayload)
        $defaults = [
            'delay' => null, 
            'linkPreview' => null,
            'mentionsEveryOne' => null,
            'mentioned' => [], 
            'quoted' => null,
        ];

        $finalPayloadData = array_merge($defaults, $opcionesAvanzadas, $payloadData);

        return array_filter($finalPayloadData, function($value) {
            return $value !== null && !(is_array($value) && empty($value));
        });
    }

    /**
     * Envía un mensaje de texto simple.
     */
    public function sendText(string $numero, string $texto, array $opcionesAvanzadas = []): array {
        
        $payloadData = [
            "number" => $numero, 
            "text" => $texto,
        ];
        
        $finalPayload = $this->normalizePayload($payloadData, $opcionesAvanzadas);

        // Obtenemos la URL del objeto de configuración inyectado
        $url = $this->config->getBaseUrl("/message/sendText");
        
        // Usamos el cliente HTTP inyectado
        return $this->httpClient->post($url, $finalPayload);
    }
    
    /**
     * Envía una imagen, documento, audio o video.
     */
    public function sendMedia(
        string $numero, 
        string $mediaUrl, 
        string $fileName, 
        string $mediatype, 
        string $mimetype,  
        string $caption = "", 
        array $opcionesAvanzadas = []
    ): array {

        $payloadData = [
            "number" => $numero, 
            "mediatype" => $mediatype, 
            "mimetype" => $mimetype, 
            "caption" => $caption, 
            "media" => $mediaUrl, 
            "fileName" => $fileName,
        ];

        $finalPayload = $this->normalizePayload($payloadData, $opcionesAvanzadas);
        
        // Obtenemos la URL del objeto de configuración inyectado
        $url = $this->config->getBaseUrl("/message/sendMedia");

        // Usamos el cliente HTTP inyectado
        return $this->httpClient->post($url, $finalPayload);
    }
    
    /**
     * Envía una actualización de estado (mensaje de tipo "status").
     */
    public function sendStatus(string $texto, array $opcionesAvanzadas = []): array {
        
        $payloadData = [
            "text" => $texto,
        ];
        
        $finalPayload = $this->normalizePayload($payloadData, $opcionesAvanzadas);
        
        $url = $this->config->getBaseUrl("/message/sendStatus"); 

        return $this->httpClient->post($url, $finalPayload);
    }

    /**
     * Verifica si una lista de números de teléfono están registrados en WhatsApp.
     * @param string[] $numbers Array de números de teléfono (ej: ['553198296801', '12025550101']).
     * @return array Resultado de la petición (estado, código HTTP, respuesta con el array de resultados).
     */
    public function checkIsWhatsApp(array $numbers): array {
        
        // El endpoint es /chat/whatsappNumbers/{instance}
        $url = $this->config->getBaseUrl("/chat/whatsappNumbers"); 
        
        // El payload requiere los números dentro de un array con la clave 'numbers'
        $payload = [
            'numbers' => $numbers
        ];

        // Usamos el cliente HTTP inyectado para la petición POST
        return $this->httpClient->post($url, $payload);
    }
}