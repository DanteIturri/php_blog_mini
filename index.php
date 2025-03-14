<?php
// Incluir archivo de funciones
require_once __DIR__ . '/functions.php';

// Cargar variables de entorno
loadEnv();

// Configuración de API
$newsApiKey = getenv('NEWS_API_KEY') ?: ''; // Obtener API key desde variables de entorno
$category = 'general'; 
$debug = true; 

// Gestión de paginación
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

// Obtener datos
$newsData = getNews($newsApiKey, $category, $currentPage);

// Si hay un error, usar datos de muestra
if (isset($newsData['status']) && $newsData['status'] === 'error') {
    $errorMessage = $newsData['message'];
    $debugInfo = $debug ? $newsData['debug'] : null;
    
    // Usar datos de ejemplo si hay error
    $useSample = true;
    $newsData = getSampleNews();
} else {
    $errorMessage = null;
    $debugInfo = null;
    $useSample = false;
}

$authors = getRandomAuthors(10);

// Total de páginas disponibles (con verificación)
$totalResults = isset($newsData['totalResults']) ? $newsData['totalResults'] : 0;
$totalPages = ceil($totalResults / 10);
if ($totalPages < 1) $totalPages = 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mini Blog de <?= ucfirst($category) ?></title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .author-img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
    }
    .card {
      transition: transform 0.3s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="#">Mini Blog</a>
    </div>
  </nav>
  
  <div class="container my-4">
    <h1 class="mb-4 text-center">Noticias de <?= ucfirst($category) ?></h1>
    
    <?php if ($errorMessage): ?>
      <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">Aviso: Usando datos de muestra</h4>
        <p>No se pudo conectar con News API. <?= htmlspecialchars($errorMessage) ?></p>
        <p class="mb-0">Se están mostrando noticias de muestra para fines de demostración.</p>
        <?php if ($debug && $debugInfo): ?>
          <hr>
          <details>
            <summary>Información de depuración</summary>
            <pre class="mt-2"><?= htmlspecialchars(print_r($debugInfo, true)) ?></pre>
          </details>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    
    <div class="row row-cols-1 row-cols-md-2 g-4 mb-4">
      <?php if (isset($newsData['articles']) && count($newsData['articles']) > 0): ?>
        <?php foreach ($newsData['articles'] as $index => $article): ?>
          <?php $author = !empty($authors) ? $authors[$index % count($authors)] : null; ?>
          <div class="col">
            <div class="card h-100 shadow-sm">
              <?php if (!empty($article['urlToImage'])): ?>
                <img src="<?= $article['urlToImage'] ?>" class="card-img-top" alt="Imagen de la noticia" style="height: 200px; object-fit: cover;">
              <?php else: ?>
                <div class="bg-light text-center py-5">
                  <p class="text-muted">Imagen no disponible</p>
                </div>
              <?php endif; ?>
              <div class="card-body">
                <h5 class="card-title"><?= $article['title'] ?></h5>
                <p class="card-text"><?= $article['description'] ?? 'No hay descripción disponible.' ?></p>
              </div>
              <div class="card-footer bg-white">
                <?php if ($author): ?>
                <div class="d-flex align-items-center">
                  <img src="<?= $author['picture']['medium'] ?>" class="author-img me-2" alt="Foto del autor">
                  <div>
                    <p class="mb-0 fw-bold"><?= $author['name']['first'] . ' ' . $author['name']['last'] ?></p>
                    <small class="text-muted"><?= date('d/m/Y', strtotime($article['publishedAt'])) ?></small>
                  </div>
                </div>
                <?php else: ?>
                <div class="d-flex align-items-center">
                  <div class="author-img me-2 bg-secondary"></div>
                  <div>
                    <p class="mb-0 fw-bold">Autor Desconocido</p>
                    <small class="text-muted"><?= date('d/m/Y', strtotime($article['publishedAt'])) ?></small>
                  </div>
                </div>
                <?php endif; ?>
                <a href="<?= $article['url'] ?>" class="btn btn-sm btn-outline-primary mt-2" target="_blank">Leer más</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <p>No hay noticias disponibles en este momento.</p>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Paginación -->
    <nav aria-label="Navegación de páginas">
      <ul class="pagination justify-content-center">
        <?php if ($currentPage > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?page=<?= $currentPage - 1 ?>" aria-label="Anterior">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
        <?php else: ?>
          <li class="page-item disabled">
            <a class="page-link" href="#" aria-label="Anterior">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
        <?php endif; ?>
        
        <?php
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $startPage + 4);
        if ($endPage - $startPage < 4) {
            $startPage = max(1, $endPage - 4);
        }
        ?>
        
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
          <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        
        <?php if ($currentPage < $totalPages): ?>
          <li class="page-item">
            <a class="page-link" href="?page=<?= $currentPage + 1 ?>" aria-label="Siguiente">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        <?php else: ?>
          <li class="page-item disabled">
            <a class="page-link" href="#" aria-label="Siguiente">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
  
  <footer class="bg-dark text-white text-center py-3 mt-4">
    <div class="container">
      <p class="mb-0">Mini Blog &copy; <?= date('Y') ?> - Desarrollado con 
      <?= $useSample ? 'datos de muestra' : 'News API' ?> y 
      <?= !empty($authors) ? 'Random User API' : 'datos de muestra de autores' ?></p>
    </div>
  </footer>
  
  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>