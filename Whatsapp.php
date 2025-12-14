<?php

use Libraries\EvolutionApi\EvolutionApiClient; 
use Libraries\EvolutionApi\EvolutionApiConfig;

class Whatsapp extends Controllers {
    
    // --- FUNCIÓN 1: ENVIAR DOCUMENTOS CON VERIFICACIÓN PREVIA ---
    public function enviarUnDocumento($params): void {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["ok" => false, "error" => "Método no permitido. Use POST."]);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $numero = $data['numero'] ?? null;
        $documento = $data['documento'] ?? null;
        $nombre_documento = $data['nombre_documento'] ?? null;

        if (!$numero || !$documento || !$nombre_documento) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Faltan parámetros requeridos."]);
            return;
        }

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        EvolutionApiConfig::resetInstance(); 
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Llamada a la API para enviar el medio (documento)
        $resultado = $apiClient->sendMedia(
            $numero, 
            $documento, 
            $nombre_documento, 
            "document", 
            "application/pdf"
        );
        
        // Devolver Respuesta JSON
        header('Content-Type: application/json');

        if ($resultado['ok']) {
            http_response_code(200);
            echo json_encode(["ok" => true, "mensaje" => "PDF enviado con éxito!", "data" => $resultado['response']]);
        } else {
            http_response_code($resultado['status_code'] ?? 500);
            echo json_encode([
                "ok" => false, 
                "error" => "Error al enviar WA: ", 
                "details" => $resultado['curl_error'], 
                "api_response" => $resultado['response'],
                "http_code" => $resultado['status_code']
            ]);
        }
    }

    // --- FUNCIÓN 2: VERIFICAR NÚMERO DE WHATSAPP ---
    public function verificarNumero(): void {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["ok" => false, "error" => "Método no permitido. Use POST."]);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $numeros = $data['numeros'] ?? null;

        if (!is_array($numeros) || empty($numeros)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Faltan parámetros requeridos: 'numeros' debe ser un array no vacío."]);
            return;
        }

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        EvolutionApiConfig::resetInstance(); 
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);

        // Llamada a la API
        $resultado = $apiClient->checkIsWhatsApp($numeros);

        // Devolver Respuesta JSON
        header('Content-Type: application/json');

        if ($resultado['ok'] && isset($resultado['response'][0])) {
            http_response_code(200);
            
            $numeroResultado = $resultado['response']; 
            
            echo json_encode([
                "ok" => true, 
                "mensaje" => "Verificación exitosa.", 
                "data" => $numeroResultado
            ]);
            
        } else {
            http_response_code($resultado['status_code'] ?? 500);
            
            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al verificar WA: " . $apiResponse;

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['message'])) {
                    $specificErrorMessage = "API Message: " . $apiResponse['message'];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }
    
    // --- FUNCIÓN 3: OBTENER ESTADO DE CONEXIÓN DE INSTANCIA (Usando InstanceKey de Config) ---
    public function connectionState(): void { 
        
        header('Content-Type: application/json');

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance(); 
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Llamar a la función Connection State (GET)
        $resultado = $apiClient->connectionState();
        
        // Manejar y devolver la respuesta
        if ($resultado['ok']) {
            // ÉXITO (código 200 OK)
            http_response_code(200);
            
            $state = $resultado['response']['state'] ?? 'unknown';
            $mensaje = "Estado de la instancia: {$state}.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (404 Not Found, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al obtener el estado de conexión.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['message'])) {
                    $specificErrorMessage = "API Message: " . $apiResponse['message'];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }
    
    // --- FUNCIÓN 4: CREAR INSTANCIA ---
    public function crearInstancia(): void {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["ok" => false, "error" => "Método no permitido. Use POST."]);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar los parámetros mínimos necesarios para crear instancia
        $instanceName = $data['instanceName'] ?? null;
        $instanceKey = $data['instanceKey'] ?? null;
        
        if (!$instanceName || !$instanceKey) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Faltan parámetros requeridos: instanceName y instanceKey."]);
            return;
        }

        // Configuración: Notar que la InstanceKey aquí no es la que usaremos
        // en el getBaseUrl (sólo usamos apiUrl y apiKey/token)
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance(); 
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Payload de la API de Evolution
        $instanceConfig = [
            'instanceName' => $instanceName,
            'instanceKey' => $instanceKey,
        ];
        
        // Llamar a la función Create Instance (POST)
        $resultado = $apiClient->createInstance($instanceConfig);
        
        // Manejar y devolver la respuesta
        header('Content-Type: application/json');

        if ($resultado['ok']) {
            // ÉXITO (código 200 OK o 201 Created)
            http_response_code($resultado['status_code'] ?? 201);
            
            $instanceKeyCreada = $resultado['response']['instance']['instanceKey'] ?? $instanceKey;
            $mensaje = "Instancia '{$instanceKeyCreada}' creada con éxito.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (409 Conflict, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al crear la instancia.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['response']['message'][0])) {
                    $specificErrorMessage = "API Error: " . $apiResponse['response']['message'][0];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }
    
    // --- FUNCIÓN 5: CONECTAR INSTANCIA (Obtener QR o Pairing Code) ---
    public function conectarInstancia(string $instanceKey): void { 
        
        header('Content-Type: application/json');

        if (empty($instanceKey)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Debe proporcionar la llave (instanceKey) de la instancia en la URL."]);
            return;
        }

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, // Se ignora en este caso, se usa el de la URL
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance(); 
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Llamar a la función Instance Connect (GET)
        $resultado = $apiClient->instanceConnect($instanceKey);
        
        // Manejar y devolver la respuesta
        if ($resultado['ok']) {
            // ÉXITO (código 200 OK)
            http_response_code(200);
            
            $mensaje = $resultado['response']['message'] ?? "Conexión iniciada. Revise QR o Pairing Code.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (404 Not Found, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al iniciar la conexión de la instancia.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['response']['message'][0])) {
                    // Manejo del error 404 de instancia no encontrada
                    $specificErrorMessage = "API Error: " . $apiResponse['response']['message'][0];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }
    
    // --- FUNCIÓN 6: CERRAR SESIÓN DE INSTANCIA ---
    public function cerrarSesionInstancia(string $instanceKey): void { 
        
        header('Content-Type: application/json');

        if (empty($instanceKey)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Debe proporcionar la llave (instanceKey) de la instancia en la URL."]);
            return;
        }

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance(); 
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Llamar a la función Instance Logout (DELETE)
        $resultado = $apiClient->instanceLogout($instanceKey);
        
        // Manejar y devolver la respuesta
        if ($resultado['ok']) {
            // ÉXITO (código 200 OK)
            http_response_code(200);
            
            $mensaje = $resultado['response']['response']['message'] ?? "Sesión de la instancia cerrada con éxito.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (404 Not Found, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al cerrar la sesión de la instancia.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['response']['message'][0])) {
                    // Manejo del error 404 de instancia no encontrada
                    $specificErrorMessage = "API Error: " . $apiResponse['response']['message'][0];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }
    
    // --- FUNCIÓN 7: REINICIAR INSTANCIA (NUEVO) ---
    /**
     * Reinicia una instancia de WhatsApp.
     * Accessible via URL: /Whatsapp/reiniciarInstancia/{instanceKey}
     * @param string $instanceKey La llave de la instancia a reiniciar (tomada del parámetro de la URL).
     */
    public function reiniciarInstancia(string $instanceKey): void { 
        
        header('Content-Type: application/json');

        if (empty($instanceKey)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Debe proporcionar la llave (instanceKey) de la instancia en la URL."]);
            return;
        }

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance(); 
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Llamar a la función Instance Restart (PUT)
        $resultado = $apiClient->instanceRestart($instanceKey);
        
        // Manejar y devolver la respuesta
        if ($resultado['ok']) {
            // ÉXITO (código 200 OK)
            http_response_code(200);
            
            $instanceName = $resultado['response']['instance']['instanceName'] ?? $instanceKey;
            $instanceState = $resultado['response']['instance']['state'] ?? 'unknown';
            $mensaje = "Instancia '{$instanceName}' reiniciada con éxito. Estado: {$instanceState}.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (404 Not Found, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al reiniciar la instancia.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['response']['message'][0])) {
                    // Manejo del error 404 de instancia no encontrada
                    $specificErrorMessage = "API Error: " . $apiResponse['response']['message'][0];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }

    // --- FUNCIÓN 8: OBTENER TODAS LAS INSTANCIAS (Fetch Instances) ---
    /**
     * Obtiene una lista de todas las instancias registradas en el servidor.
     * Accessible via URL: /Whatsapp/fetchInstances
     */
    public function fetchInstances(): void {
        
        header('Content-Type: application/json');

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance(); 
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Llamar a la función Fetch Instances (GET)
        $resultado = $apiClient->fetchInstances();
        
        // Manejar y devolver la respuesta
        if ($resultado['ok']) {
            // ÉXITO (código 200 OK)
            http_response_code(200);
            
            $count = is_array($resultado['response']) ? count($resultado['response']) : 0;
            $mensaje = "Se han obtenido {$count} instancias.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (404 Not Found, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al obtener la lista de instancias.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['message'])) {
                    $specificErrorMessage = "API Message: " . $apiResponse['message'];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }

    // --- FUNCIÓN 9: ELIMINAR INSTANCIA (NUEVO) ---
    /**
     * Elimina una instancia de WhatsApp registrada.
     * Accessible via URL: /Whatsapp/eliminarInstancia/{instanceKey}
     * @param string $instanceKey La llave de la instancia a eliminar (tomada del parámetro de la URL).
     */
    public function eliminarInstancia(string $instanceKey): void {
        
        header('Content-Type: application/json');

        if (empty($instanceKey)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Debe proporcionar la llave (instanceKey) de la instancia en la URL."]);
            return;
        }

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance();
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Llamar a la función Instance Delete (DELETE)
        $resultado = $apiClient->instanceDelete($instanceKey);
        
        // Manejar y devolver la respuesta
        if ($resultado['ok']) {
            // ÉXITO (código 200 OK)
            http_response_code(200);
            
            $mensaje = $resultado['response']['response']['message'] ?? "Instancia '{$instanceKey}' eliminada con éxito.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (404 Not Found, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al eliminar la instancia.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['response']['message'][0])) {
                    // Manejo del error 404 de instancia no encontrada
                    $specificErrorMessage = "API Error: " . $apiResponse['response']['message'][0];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }

    // --- FUNCIÓN 10: ESTABLECER PRESENCIA (NUEVO) ---
    /**
     * Establece el estado de presencia (available, composing, etc.) para una instancia.
     * Accessible via URL: /Whatsapp/setPresence/{instanceKey}
     * @param string $instanceKey La llave de la instancia.
     */
    public function setPresence(string $instanceKey): void {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["ok" => false, "error" => "Método no permitido. Use POST."]);
            return;
        }

        header('Content-Type: application/json');

        if (empty($instanceKey)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Debe proporcionar la llave (instanceKey) de la instancia en la URL."]);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $presence = $data['presence'] ?? null; // e.g., 'available', 'composing', 'paused'

        if (!$presence) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Falta el parámetro requerido: 'presence'."]);
            return;
        }

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance();
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Llamar a la función Instance Set Presence (POST)
        $resultado = $apiClient->instanceSetPresence($instanceKey, $presence);
        
        // Manejar y devolver la respuesta
        if ($resultado['ok']) {
            // ÉXITO (código 200 OK)
            http_response_code(200);
            
            // La API de Evolution puede devolver solo un mensaje simple o un objeto vacío en caso de éxito.
            $mensaje = "Presencia de la instancia '{$instanceKey}' establecida a '{$presence}' con éxito.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (404 Not Found, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al establecer la presencia de la instancia.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['response']['message'][0])) {
                    $specificErrorMessage = "API Error: " . $apiResponse['response']['message'][0];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }

    // --- FUNCIÓN 11: ENVIAR TEXTO PLANO (NUEVO) ---
    /**
     * Envía un mensaje de texto plano a un contacto o grupo.
     * Accessible via URL: /Whatsapp/enviarTextoPlano/{instanceKey}
     * Body JSON: { "number": "...", "text": "...", "delay": 123, "linkPreview": true, ... }
     * @param string $instanceKey La llave de la instancia.
     */
    public function enviarTextoPlano(string $instanceKey): void {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["ok" => false, "error" => "Método no permitido. Use POST."]);
            return;
        }

        header('Content-Type: application/json');

        if (empty($instanceKey)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Debe proporcionar la llave (instanceKey) de la instancia en la URL."]);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $number = $data['number'] ?? null; 
        $text = $data['text'] ?? null; 

        if (!$number || !$text) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Faltan parámetros requeridos: 'number' y 'text'."]);
            return;
        }

        // Configuración (Asegurar que la configuración está lista)
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance();
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Separar opciones avanzadas (todo lo que no es number ni text)
        $opcionesAvanzadas = array_diff_key($data, array_flip(['number', 'text']));
        
        // Llamar a la función sendText del cliente API
        $resultado = $apiClient->sendText($instanceKey, $number, $text, $opcionesAvanzadas);
        
        // Manejar y devolver la respuesta
        if ($resultado['ok']) {
            // ÉXITO (código 201 Created/Accepted)
            http_response_code(201);
            
            $remoteJid = $resultado['response']['key']['remoteJid'] ?? 'Desconocido';
            $mensaje = "Mensaje de texto enviado a '{$remoteJid}' con éxito.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (404 Not Found, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al enviar el mensaje de texto.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['response']['message'][0])) {
                    $specificErrorMessage = "API Error: " . $apiResponse['response']['message'][0];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }

    // --- FUNCIÓN 12: ENVIAR ESTADO (STATUS) (NUEVO) ---
    /**
     * Envía un estado (texto, imagen o audio) a WhatsApp.
     * Accessible via URL: /Whatsapp/enviarEstado/{instanceKey}
     * Body JSON: { "type": "text|image|audio", "content": "...", "caption": "...", "allContacts": true, ... }
     * @param string $instanceKey La llave de la instancia.
     */
    public function enviarEstado(string $instanceKey): void {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["ok" => false, "error" => "Método no permitido. Use POST."]);
            return;
        }

        header('Content-Type: application/json');

        if (empty($instanceKey)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Debe proporcionar la llave (instanceKey) de la instancia en la URL."]);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $type = $data['type'] ?? null; // text, image, audio
        $content = $data['content'] ?? null; // Texto o URL
        
        $validTypes = ['text', 'image', 'audio'];

        if (!$type || !$content || !in_array($type, $validTypes)) {
            http_response_code(400);
            echo json_encode(["ok" => false, "error" => "Faltan parámetros requeridos ('type', 'content') o 'type' no es válido (solo: " . implode(', ', $validTypes) . ")."]);
            return;
        }

        // Configuración
        $configParams = [
            'api_url' => EVOLUTION_API_URL,
            'api_token' => EVOLUTION_API_TOKEN,
            'instance_key' => EVOLUTION_INSTANCE_KEY, 
            'api_key' => EVOLUTION_API_KEY,
        ];
        
        EvolutionApiConfig::resetInstance();
        $config = EvolutionApiConfig::getInstance($configParams);
        $apiClient = new EvolutionApiClient($config);
        
        // Separar opciones avanzadas
        $opcionesAvanzadas = array_diff_key($data, array_flip(['type', 'content']));
        
        // Llamar a la función sendStatus del cliente API
        $resultado = $apiClient->sendStatus($instanceKey, $type, $content, $opcionesAvanzadas);
        
        // Manejar y devolver la respuesta
        if ($resultado['ok']) {
            // ÉXITO (código 201 Created)
            http_response_code(201);
            
            $mensaje = "Estado de tipo '{$type}' enviado con éxito.";

            echo json_encode([
                "ok" => true, 
                "mensaje" => $mensaje,
                "data" => $resultado['response']
            ]);
            
        } else {
            // ERROR (400, 404, 500, o cURL error)
            http_response_code($resultado['status_code'] ?? 500);

            $apiResponse = $resultado['response'];
            $specificErrorMessage = "Error al enviar el estado.";

            if (is_array($apiResponse)) {
                if (isset($apiResponse['error'])) {
                    $specificErrorMessage = "API Error: " . (is_string($apiResponse['error']) ? $apiResponse['error'] : json_encode($apiResponse['error']));
                } elseif (isset($apiResponse['response']['message'][0])) {
                    $specificErrorMessage = "API Error: " . $apiResponse['response']['message'][0];
                }
            }
            
            if (!empty($resultado['curl_error'])) {
                $specificErrorMessage = "cURL/Network Error: " . $resultado['curl_error'];
            }

            echo json_encode([
                "ok" => false, 
                "error" => $specificErrorMessage,
                "details" => $resultado['curl_error'], 
                "api_response" => $apiResponse,
                "http_code" => $resultado['status_code']
            ]);
        }
    }


}