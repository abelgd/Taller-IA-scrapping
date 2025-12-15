<?php
require_once 'config.php';

$verified = isset($_SESSION['turnstile_verified']) && $_SESSION['turnstile_verified'] === true;
$error = '';

if (!$verified && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cf-turnstile-response'])) {
    $token = $_POST['cf-turnstile-response'];
    $secretKey = getenv('TURNSTILE_SECRET_KEY');
    $ip = $_SERVER['REMOTE_ADDR'];

    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => $secretKey,
        'response' => $token,
        'remoteip' => $ip
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    if ($response['success']) {
        $_SESSION['turnstile_verified'] = true;
        $verified = true;
        // Optional: Redirect to self to clear POST data
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = 'Verificación fallida. Por favor, inténtalo de nuevo.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad y Verificación</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        /* Default Light Theme */
        :root {
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --accent-color: #0d6efd;
            --border-color: #dee2e6;
            --font-main: 'Inter', system-ui, -apple-system, sans-serif;
            --header-bg: #ffffff;
            --shadow-color: rgba(0, 0, 0, 0.05);
        }

        /* Dark Theme Override */
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #121212;
                --card-bg: #1e1e1e;
                --text-primary: #e0e0e0;
                --text-secondary: #a0a0a0;
                --accent-color: #3b82f6;
                --border-color: #333;
                --header-bg: #1e1e1e;
                --shadow-color: rgba(0, 0, 0, 0.2);
            }
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: var(--font-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify_content: center;
            margin: 0;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .main-container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 24px var(--shadow-color);
            transition: background-color 0.3s ease, border-color 0.3s ease;
            overflow: hidden;
        }

        .card-header {
            background-color: var(--header-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 2rem 2.5rem;
            border-radius: 12px 12px 0 0 !important;
        }

        .card-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .card-body {
            padding: 2.5rem;
        }

        .lead {
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 400;
        }

        .alert-info {
            background-color: rgba(13, 110, 253, 0.1);
            border: 1px solid rgba(13, 110, 253, 0.2);
            color: var(--accent-color);
        }

        h4,
        h5 {
            color: var(--text-primary);
            font-weight: 600;
            margin-top: 1.5rem;
        }

        .feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
            border-radius: 12px;
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--accent-color);
        }

        .list-group-item {
            background-color: transparent;
            border-color: var(--border-color);
            color: var(--text-secondary);
            padding: 1rem 0;
        }

        .list-group-item:first-child {
            border-top: none;
        }

        .btn-outline-secondary {
            border-color: var(--border-color);
            color: var(--text-secondary);
        }

        .btn-outline-secondary:hover {
            background-color: var(--text-secondary);
            color: #fff;
        }

        hr {
            border-color: var(--border-color);
            opacity: 0.5;
            margin: 2rem 0;
        }

        /* Fix for Bootstrap text utility classes in dark mode */
        .text-secondary,
        .text-muted {
            color: var(--text-secondary) !important;
        }
    </style>
</head>

<body>

    <div class="container main-container">
        <?php if (!$verified): ?>
            <div class="card text-center">
                <div class="card-header border-0 pb-0">
                    <h3 class="mb-0">Verificación de Seguridad</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4 text-secondary">Para proteger nuestro contenido de accesos automatizados, por
                        favor completa
                        la siguiente verificación.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="d-flex justify-content-center mb-4">
                            <!-- Turnstile Widget (Auto Theme) -->
                            <div class="cf-turnstile" data-sitekey="<?php echo getenv('TURNSTILE_SITE_KEY'); ?>"
                                data-callback="javascriptCallback" data-theme="auto"></div>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                // Auto-submit the form when the challenge is solved
                function javascriptCallback(token) {
                    document.querySelector('form').submit();
                }
            </script>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Informe de Seguridad: Cloudflare Turnstile</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="bi bi-check-circle-fill flex-shrink-0 me-2" viewBox="0 0 16 16">
                            <path
                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                        </svg>
                        <div>
                            <strong>Acceso Verificado:</strong> Has demostrado ser humano mediante análisis de
                            comportamiento.
                        </div>
                    </div>

                    <p class="lead mb-5">
                        A diferencia de los CAPTCHAs tradicionales que interrumpen la experiencia del usuario pidiendo
                        identificar objetos, Turnstile utiliza un enfoque invisible y centrado en la privacidad.
                    </p>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <h4>Detección de Comportamiento</h4>
                            <p class="text-secondary">
                                El sistema analiza micro-interacciones sutiles, como el movimiento del cursor, los patrones
                                de desplazamiento y la cadencia de interacción. Las IAs y los bots suelen tener patrones
                                lineales o instantáneos que los delatan.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h4>Prueba de Trabajo (PoW)</h4>
                            <p class="text-secondary">
                                Tu navegador resuelve desafíos matemáticos complejos en segundo plano. Esto verifica que la
                                solicitud proviene de un entorno de navegador legítimo y no de un script automatizado
                                simple.
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">¿Por qué es efectivo?</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">
                            <strong>Privacidad Primero:</strong> No utiliza cookies para rastrear al usuario por internet,
                            sino que valida la sesión actual.
                        </li>
                        <li class="list-group-item">
                            <strong>Sin Fricción:</strong> La mayoría de los usuarios humanos pasan la prueba
                            automáticamente sin necesidad de hacer clic ("Smart Challenge").
                        </li>
                    </ul>

                    <a href="?logout=1" class="btn btn-outline-secondary btn-sm"
                        onclick="<?php session_destroy(); ?>">Reiniciar Demostración</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>