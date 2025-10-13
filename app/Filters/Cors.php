<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Cors implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowedOrigins = [
            'https://teleconsulta.clinicahispanarubymed.com',
            'http://teleconsulta.clinicahispanarubymed.com',
            'https://www.teleconsulta.clinicahispanarubymed.com',  // ← Agregar este
            'https://www.clinicahispanarubymed.com'
        ];

        $origin = $request->getHeaderLine('Origin') ?: ($_SERVER['HTTP_ORIGIN'] ?? '');


        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
        }

        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Max-Age: 86400");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Opcionalmente puedes agregar aquí algún encabezado si deseas
    }
}
