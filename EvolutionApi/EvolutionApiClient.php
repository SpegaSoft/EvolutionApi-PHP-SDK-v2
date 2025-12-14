<?php
// libreries/EvolutionApi/EvolutionApiClient.php

namespace Libraries\EvolutionApi; 

use Libraries\EvolutionApi\EvolutionApiConfig;
use Libraries\EvolutionApi\EvolutionApiHttpClient;

class EvolutionApiClient {
    
    protected EvolutionApiConfig $config;
    protected EvolutionApiHttpClient $httpClient;

    public function __construct(EvolutionApiConfig $config) {
        $this->config = $config;
        $this->httpClient = new EvolutionApiHttpClient($config); 
    }
    
    // Se mantiene y se puede refinar para limpiar campos nulos o vacíos en general
    private function normalizePayload(array $payloadData, array $opcionesAvanzadas): array {
        
        // Define todos los campos que pueden ser opcionales o avanzados para evitar que se pasen si no tienen valor.
        $defaults = [
            'delay' => null, 
            'linkPreview' => null, 
            'mentionsEveryOne' => null, 
            'mentioned' => [], 
            'quoted' => null,
            // Campos específicos de Status
            'caption' => null,
            'backgroundColor' => null,
            'font' => null,
            'allContacts' => null,
            'statusJidList' => [],
        ];

        $finalPayloadData = array_merge($defaults, $opcionesAvanzadas, $payloadData);

        // Limpia los valores null, arrays vacíos, y strings vacíos del payload final
        return array_filter($finalPayloadData, function($value) {
            return $value !== null && !(is_array($value) && empty($value)) && !(is_string($value) && trim($value) === '');
        });
    }

    // --- Métodos de Mensajería ---
    
    /**
     * Envía un mensaje de texto plano a un número o grupo específico.
     * Endpoint: POST /message/sendText/{instanceKey}
     * @param string $instanceKey La llave de la instancia de WhatsApp.
     * @param string $numero Número de destino (ej: 553198296801@s.whatsapp.net o 55319xxxxxxx-xxxxxxx@g.us).
     * @param string $texto El mensaje de texto a enviar.
     * @param array $opcionesAvanzadas Opciones opcionales como delay, linkPreview, mentioned, etc.
     * @return array Resultado de la petición.
     */
    public function sendText(string $instanceKey, string $numero, string $texto, array $opcionesAvanzadas = []): array {
        
        $payloadData = ["number" => $numero, "text" => $texto,];
        $finalPayload = $this->normalizePayload($payloadData, $opcionesAvanzadas);
        
        // Construimos la URL con la instanceKey proporcionada
        $apiUrl = $this->config->getApiUrl();
        $url = $apiUrl . "/message/sendText/" . $instanceKey; 

        // Usar el método POST del cliente HTTP
        return $this->httpClient->post($url, $finalPayload);
    }

    /**
     * Envía un estado (status) de WhatsApp (texto, imagen o audio).
     * Endpoint: POST /message/sendStatus/{instanceKey}
     * @param string $instanceKey La llave de la instancia de WhatsApp.
     * @param string $type Tipo de estado ('text', 'image', 'audio').
     * @param string $content El contenido del estado (texto o URL del archivo).
     * @param array $opcionesAvanzadas Opciones opcionales como caption, font, allContacts, statusJidList, etc.
     * @return array Resultado de la petición.
     */
    public function sendStatus(string $instanceKey, string $type, string $content, array $opcionesAvanzadas = []): array {
        
        $payloadData = [
            "type" => $type, 
            "content" => $content,
        ];
        
        $finalPayload = $this->normalizePayload($payloadData, $opcionesAvanzadas);
        
        // Construimos la URL con la instanceKey proporcionada
        $apiUrl = $this->config->getApiUrl();
        $url = $apiUrl . "/message/sendStatus/" . $instanceKey; 

        // Usar el método POST del cliente HTTP
        return $this->httpClient->post($url, $finalPayload);
    }
  
   
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
        $url = $this->config->getBaseUrl("/message/sendMedia");

        return $this->httpClient->post($url, $finalPayload);
    }
    
    // --- Métodos de Instancia y Utilidades ---
    
    public function checkIsWhatsApp(array $numbers): array {
        
        $url = $this->config->getBaseUrl("/chat/whatsappNumbers"); 
        $payload = ['numbers' => $numbers];

        return $this->httpClient->post($url, $payload);
    }
    
    public function connectionState(): array {
        
        $url = $this->config->getBaseUrl("/instance/connectionState"); 
        
        return $this->httpClient->get($url);
    }
    
    public function createInstance(array $instanceConfig): array {
        
        $url = $this->config->getApiUrl() . "/instance/create";
        
        return $this->httpClient->post($url, $instanceConfig);
    }

    /**
     * Obtiene una lista de todas las instancias registradas en el servidor Evolution API.
     * Endpoint: GET /instance/fetchInstances
     * @return array Resultado de la petición (lista de instancias).
     */
    public function fetchInstances(): array {
        
        // 🛑 Importante: Este endpoint no lleva instanceKey en la URL.
        $url = $this->config->getApiUrl() . "/instance/fetchInstances"; 
        
        return $this->httpClient->get($url);
    }

    /**
     * Inicia el proceso de conexión (QR o Pairing Code) para una instancia existente.
     * Endpoint: GET /instance/connect/{instanceKey}
     * @param string $instanceKey La llave de la instancia a conectar.
     * @return array Resultado de la petición (código QR o Pairing Code).
     */
    public function instanceConnect(string $instanceKey): array {
        
        $apiUrl = $this->config->getApiUrl();
        $url = $apiUrl . "/instance/connect/" . $instanceKey;
        
        return $this->httpClient->get($url);
    }
    
    /**
     * Cierra la sesión de WhatsApp de una instancia activa.
     * Endpoint: DELETE /instance/logout/{instanceKey}
     * @param string $instanceKey La llave de la instancia a desconectar.
     * @return array Resultado de la petición de cierre de sesión.
     */
    public function instanceLogout(string $instanceKey): array {
        
        // 🛑 Importante: Construimos la URL sin usar getBaseUrl() para sobreescribir la instanceKey predeterminada
        $apiUrl = $this->config->getApiUrl();
        $url = $apiUrl . "/instance/logout/" . $instanceKey;
        
        // Usar el método DELETE del cliente HTTP
        return $this->httpClient->delete($url);
    }

    /**
     * Elimina una instancia registrada en el servidor Evolution API.
     * Endpoint: DELETE /instance/delete/{instanceKey}
     * @param string $instanceKey La llave de la instancia a eliminar.
     * @return array Resultado de la petición de eliminación.
     */
    public function instanceDelete(string $instanceKey): array {
        
        // Construimos la URL sin usar getBaseUrl() para sobreescribir la instanceKey predeterminada
        $apiUrl = $this->config->getApiUrl();
        $url = $apiUrl . "/instance/delete/" . $instanceKey;
        
        // Usar el método DELETE del cliente HTTP
        return $this->httpClient->delete($url);
    }

    /**
     * Establece el estado de presencia (available, composing, paused, recording) para la instancia.
     * Endpoint: POST /instance/setPresence/{instanceKey}
     * @param string $instanceKey La llave de la instancia.
     * @param string $presence El estado de presencia (e.g., 'available', 'composing').
     * @return array Resultado de la petición de establecimiento de presencia.
     */
    public function instanceSetPresence(string $instanceKey, string $presence): array {
        
        // Construimos la URL sin usar getBaseUrl() para sobreescribir la instanceKey predeterminada
        $apiUrl = $this->config->getApiUrl();
        $url = $apiUrl . "/instance/setPresence/" . $instanceKey;
        
        $payload = ['presence' => $presence];
        
        // Usar el método POST del cliente HTTP
        // Aunque el método es POST, este endpoint en Evolution API no retorna 201/200 OK con payload de éxito
        return $this->httpClient->post($url, $payload);
    }

    /**
     * Reinicia una instancia activa.
     * Endpoint: PUT /instance/restart/{instanceKey}
     * @param string $instanceKey La llave de la instancia a reiniciar.
     * @return array Resultado de la petición de reinicio.
     */
    public function instanceRestart(string $instanceKey): array {
        
        // Construimos la URL sin usar getBaseUrl() para sobreescribir la instanceKey predeterminada
        $apiUrl = $this->config->getApiUrl();
        $url = $apiUrl . "/instance/restart/" . $instanceKey;
        
        // Usar el método PUT del cliente HTTP
        return $this->httpClient->put($url);
    }
}