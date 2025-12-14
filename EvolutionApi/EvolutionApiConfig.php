<?php
// libreries/EvolutionApi/EvolutionApiConfig.php

namespace Libraries\EvolutionApi;

class EvolutionApiConfig {
    
    // Configuración de la API (propiedades)
    protected string $apiUrl = "http://31.97.175.37:8080";
    protected string $apiToken = "D2CF2860B8CA-4424-A804-E912FD0D63D4";
    protected string $instanceKey = "envios_wa_master";
    protected string $apiKey = "44444444444444";
    
    protected static ?self $instance = null;

    /**
     * Constructor privado que carga la configuración.
     */
    protected function __construct(array $config = []) {
        $this->load($config);
    }
    
    /**
     * Obtiene la instancia de configuración (Singleton).
     * @param array $config Opcional: Configuración a cargar/sobrescribir en la primera llamada.
     */
    public static function getInstance(array $config = []): self {
        if (self::$instance === null) {
            self::$instance = new self($config);
        } else if (!empty($config)) {
            // Si ya existe y se pasan parámetros, los carga para sobrescribir si es necesario
            self::$instance->load($config);
        }
        return self::$instance;
    }

    /**
     * Resetea la instancia Singleton. Útil para re-inicializar con nueva configuración.
     */
    public static function resetInstance(): void {
        self::$instance = null;
    }

    /**
     * Carga y sobrescribe la configuración.
     */
    public function load(array $config = []): void {
        if (isset($config['api_url'])) {
            $this->apiUrl = $config['api_url'];
        }
        if (isset($config['api_token'])) {
            $this->apiToken = $config['api_token'];
        }
        if (isset($config['instance_key'])) {
            $this->instanceKey = $config['instance_key'];
        }
        if (isset($config['api_key'])) {
            $this->apiKey = $config['api_key'];
        }
    }
    
    // --- Métodos Getters ---
    
    public function getApiUrl(): string {
        return $this->apiUrl;
    }
    
    public function getApiToken(): string {
        return $this->apiToken;
    }

    public function getApiKey(): string {
        return $this->apiKey;
    }
    
    public function getInstanceKey(): string {
        return $this->instanceKey;
    }
 

    /**
     * Construye la URL completa del endpoint de la API.
     */
    public function getBaseUrl(string $endpoint): string {
        return $this->getApiUrl() . $endpoint . "/" . $this->getInstanceKey();
    }
}