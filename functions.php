<?php
// Función para cargar variables de entorno desde archivo .env
function loadEnv($path = null) {
    $path = $path ?? __DIR__ . '/.env';
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '//') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Establecer variable de entorno
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
    return true;
}

// Función para obtener noticias desde News API usando cURL
function getNews($apiKey, $category, $page = 1) {
    $url = "https://newsapi.org/v2/top-headlines?category=" . $category . "&page=" . $page . "&pageSize=10&apiKey=" . $apiKey;
    
    // Verificar si podemos usar cURL
    if (function_exists('curl_version')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            return [
                'status' => 'error', 
                'message' => 'Error cURL: ' . $error,
                'debug' => ['method' => 'curl', 'http_code' => $httpCode, 'url' => $url],
                'articles' => []
            ];
        }
        
        $data = json_decode($response, true);
        
        // Verificar si la respuesta contiene artículos
        if (isset($data['status']) && $data['status'] === 'ok' && !isset($data['articles'])) {
            // Si la respuesta no tiene artículos pero es 'ok', adaptamos el formato
            if (isset($data['sources'])) {
                // Convertir fuentes a formato de artículos para compatibilidad
                $articles = [];
                foreach ($data['sources'] as $source) {
                    $articles[] = [
                        'title' => $source['name'] ?? 'Sin título',
                        'description' => $source['description'] ?? 'Sin descripción',
                        'url' => $source['url'] ?? '#',
                        'urlToImage' => null, // Las fuentes no suelen tener imagen
                        'publishedAt' => date('Y-m-d\TH:i:s\Z')
                    ];
                }
                $data['articles'] = $articles;
                $data['totalResults'] = count($articles);
            }
        }
        
        if (isset($data['status']) && $data['status'] === 'error') {
            return [
                'status' => 'error',
                'message' => 'Error en la API: ' . ($data['message'] ?? 'Error desconocido'),
                'debug' => ['method' => 'curl', 'response' => $data, 'http_code' => $httpCode, 'url' => $url],
                'articles' => []
            ];
        }
        
        return $data;
    } 
    // Si no hay cURL, intentar con file_get_contents
    else if (ini_get('allow_url_fopen')) {
        try {
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ]
            ];
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return [
                    'status' => 'error', 
                    'message' => 'Error al conectar con file_get_contents',
                    'debug' => ['method' => 'file_get_contents', 'url' => $url, 'allow_url_fopen' => ini_get('allow_url_fopen')],
                    'articles' => []
                ];
            }
            
            return json_decode($response, true);
        } catch (Exception $e) {
            return [
                'status' => 'error', 
                'message' => $e->getMessage(),
                'debug' => ['method' => 'file_get_contents', 'exception' => true],
                'articles' => []
            ];
        }
    } 
    // Si no hay ningún método disponible, usar datos de muestra
    else {
        return [
            'status' => 'error', 
            'message' => 'No se pueden hacer solicitudes HTTP. Ni cURL ni allow_url_fopen están habilitados.',
            'debug' => ['curl_exists' => function_exists('curl_version'), 'allow_url_fopen' => ini_get('allow_url_fopen')],
            'articles' => []
        ];
    }
}

// Función para obtener autores aleatorios desde Random User API con manejo mejorado
function getRandomAuthors($count = 10) {
    $url = "https://randomuser.me/api/?results={$count}";
    
    if (function_exists('curl_version')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) {
            return [];
        }
        
        $data = json_decode($response, true);
        return isset($data['results']) ? $data['results'] : [];
    } else if (ini_get('allow_url_fopen')) {
        try {
            $response = @file_get_contents($url);
            if ($response === false) {
                return [];
            }
            $data = json_decode($response, true);
            return isset($data['results']) ? $data['results'] : [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Si todo falla, devolver datos de muestra para autores
    return getSampleAuthors($count);
}

// Datos de muestra para cuando la API falle
function getSampleNews() {
    return [
        'status' => 'ok',
        'totalResults' => 10,
        'articles' => [
            [
                'title' => 'Nueva tecnología revoluciona el mercado',
                'description' => 'Una innovadora tecnología está transformando la forma en que las empresas operan en el mundo digital.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Tecnologia+Revolucionaria',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z')
            ],
            [
                'title' => 'Avances en inteligencia artificial',
                'description' => 'Investigadores desarrollan nuevos algoritmos que mejoran la capacidad de aprendizaje de los sistemas de IA.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Inteligencia+Artificial',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime('-1 day'))
            ],
            [
                'title' => 'El futuro de los dispositivos móviles',
                'description' => 'Nuevos prototipos de smartphones con características innovadoras que podrían definir el futuro de la tecnología móvil.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Dispositivos+Moviles',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime('-2 days'))
            ],
            [
                'title' => 'Ciberseguridad en la era digital',
                'description' => 'Expertos advierten sobre nuevas amenazas y recomiendan prácticas para proteger los datos en internet.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Ciberseguridad',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime('-3 days'))
            ],
            [
                'title' => 'Avances en computación cuántica',
                'description' => 'Científicos logran nuevo hito en el desarrollo de computadoras cuánticas que podrían resolver problemas complejos.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Computacion+Cuantica',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime('-4 days'))
            ],
            [
                'title' => 'El impacto de las redes sociales',
                'description' => 'Estudio revela cómo las plataformas sociales están cambiando nuestro comportamiento y relaciones interpersonales.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Redes+Sociales',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime('-5 days'))
            ],
            [
                'title' => 'Innovación en energías renovables',
                'description' => 'Nuevas tecnologías prometen hacer más eficiente y accesible el uso de energías limpias y sostenibles.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Energias+Renovables',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime('-6 days'))
            ],
            [
                'title' => 'El auge del comercio electrónico',
                'description' => 'Las compras en línea continúan creciendo y transformando el panorama del comercio minorista tradicional.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Comercio+Electronico',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime('-7 days'))
            ],
            [
                'title' => 'Nuevos avances en realidad virtual',
                'description' => 'Las tecnologías de RV están encontrando aplicaciones más allá del entretenimiento, revolucionando sectores como la educación y medicina.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Realidad+Virtual',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime('-8 days'))
            ],
            [
                'title' => 'El futuro del trabajo remoto',
                'description' => 'Empresas adoptan nuevas herramientas y políticas para facilitar el trabajo a distancia de forma permanente.',
                'urlToImage' => 'https://via.placeholder.com/600x400?text=Trabajo+Remoto',
                'url' => '#',
                'publishedAt' => date('Y-m-d\TH:i:s\Z', strtotime('-9 days'))
            ]
        ]
    ];
}

function getSampleAuthors($count = 10) {
    $authors = [];
    for ($i = 0; $i < $count; $i++) {
        $authors[] = [
            'name' => [
                'first' => 'Usuario',
                'last' => 'Ejemplo ' . ($i + 1)
            ],
            'picture' => [
                'medium' => 'https://via.placeholder.com/100?text=Autor+' . ($i + 1)
            ]
        ];
    }
    return $authors;
}