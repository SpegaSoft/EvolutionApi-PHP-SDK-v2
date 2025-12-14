<?php
// libreries/EvolutionApi/EvolutionApiHttpClient.php

namespace Libraries\EvolutionApi; 

use Libraries\EvolutionApi\EvolutionApiConfig; // Importamos la configuración

class EvolutionApiHttpClient {

    protected EvolutionApiConfig $config;

    public function __construct(EvolutionApiConfig $config) {
        $this->config = $config;
    }

    /**
     * Ejecuta una petición POST a la Evolution API.
     * @param string $url La URL completa del endpoint.
     * @param array $payloadData Los datos a enviar en el cuerpo de la petición.
     * @return array Resultado de la petición (estado, código HTTP, respuesta).
     */
    public function post(string $url, array $payloadData): array {
        
        $payload = json_encode($payloadData);
        
        // --- Ejecución de cURL ---
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                // Usamos el método Getter del objeto de configuración inyectado
                "apikey: " . $this->config->getApiKey() 
            ],
            CURLOPT_POSTFIELDS => $payload, 
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_errno($ch) ? curl_error($ch) : null;

        curl_close($ch);

        $isSuccessful = ($httpCode === 200 || $httpCode === 201);

        return [
            'ok' => $isSuccessful,
            'status_code' => $httpCode,
            'curl_error' => $error,
            'response' => json_decode($response, true) ?? $response
        ];
    }

    /**
     * Ejecuta una petición GET a la Evolution API.
     * @param string $url La URL completa del endpoint.
     * @return array Resultado de la petición (estado, código HTTP, respuesta).
     */
    public function get(string $url): array {
        
        // --- Ejecución de cURL ---
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                // Usamos el método Getter del objeto de configuración inyectado
                "apikey: " . $this->config->getApiKey() 
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_errno($ch) ? curl_error($ch) : null;

        curl_close($ch);

        // Considerar 200 (OK) como éxito para GET
        $isSuccessful = ($httpCode === 200);

        return [
            'ok' => $isSuccessful,
            'status_code' => $httpCode,
            'curl_error' => $error,
            'response' => json_decode($response, true) ?? $response
        ];
    }

    /**
     * Ejecuta una petición DELETE a la Evolution API.
     * @param string $url La URL completa del endpoint.
     * @return array Resultado de la petición (estado, código HTTP, respuesta).
     */
    public function delete(string $url): array {
        
        // --- Ejecución de cURL ---
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CUSTOMREQUEST => "DELETE", 
            CURLOPT_HTTPHEADER => [
                // Usamos el método Getter del objeto de configuración inyectado
                "apikey: " . $this->config->getApiKey() 
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_errno($ch) ? curl_error($ch) : null;

        curl_close($ch);

        $isSuccessful = ($httpCode === 200);

        return [
            'ok' => $isSuccessful,
            'status_code' => $httpCode,
            'curl_error' => $error,
            'response' => json_decode($response, true) ?? $response
        ];
    }

    /**
     * Ejecuta una petición PUT a la Evolution API.
     * @param string $url La URL completa del endpoint.
     * @return array Resultado de la petición (estado, código HTTP, respuesta).
     */
    public function put(string $url): array {
        
        // --- Ejecución de cURL ---
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            // 🛑 Método CUSTOMREQUEST configurado como PUT
            CURLOPT_CUSTOMREQUEST => "PUT", 
            CURLOPT_HTTPHEADER => [
                "apikey: " . $this->config->getApiKey() 
            ],
            // En las peticiones PUT para restart no suele haber cuerpo de petición (payload)
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_errno($ch) ? curl_error($ch) : null;

        curl_close($ch);

        // Considerar 200 (OK) como éxito para PUT
        $isSuccessful = ($httpCode === 200);

        return [
            'ok' => $isSuccessful,
            'status_code' => $httpCode,
            'curl_error' => $error,
            'response' => json_decode($response, true) ?? $response
        ];
    }
}