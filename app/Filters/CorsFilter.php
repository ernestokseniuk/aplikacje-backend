<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');

        // Ustawienie nagłówków CORS
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigins = ['http://localhost:3000','https://311c-2a01-118f-4107-2e00-1474-3425-d5b2-1ca2.ngrok-free.app']; // Zdefiniuj tutaj wszystkie dozwolone originy
        if (in_array($origin, $allowedOrigins)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization, Dnt, Accept, X-Requested-With');

        // Obsługuje zapytania OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return $response->setStatusCode(200)
                            ->setHeader('Content-Type', 'application/json')
                            ->setBody(''); // Zwróć pustą odpowiedź
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Po wykonaniu zapytania nie ma potrzeby modyfikowania odpowiedzi
    }
}
