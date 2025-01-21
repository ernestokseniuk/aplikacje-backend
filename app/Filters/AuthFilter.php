<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;

class AuthFilter implements FilterInterface
{
    private $secretKey = 'testowy_klucz';

    public function before(RequestInterface $request, $arguments = null)
    {
        log_message('critical', 'AuthFilter::before');
        // Zezwalaj na zapytania OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return $this->allowPreflightResponse();
        }

        // Sprawdzenie nagłówka Authorization w przypadku innych metod
        $authHeader = $request->getHeader('Authorization');

        if ($authHeader) {
            $jwt = str_replace('Bearer ', '', $authHeader->getValue());
            try {
                // Dekodowanie tokenu JWT
                $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
                return;
            } catch (\Exception $e) {
                // Zwracanie odpowiedzi 401 Unauthorized w przypadku niepoprawnego lub wygasłego tokenu
                return $this->responseUnauthorized($e->getMessage());
            }
        } else {
            // Zwracanie odpowiedzi 401 Unauthorized, jeśli brak jest tokenu
            return $this->responseUnauthorized('Authorization token missing');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nie robimy nic po wykonaniu zapytania
    }

    // Funkcja do zwrócenia odpowiedzi z kodem 401
    private function responseUnauthorized($message)
    {
        $response = service('response');
        $response->setStatusCode(401); // Kod odpowiedzi 401 Unauthorized
        $response->setJSON(['error' => $message]);
        return $response;
    }

    // Funkcja do obsługi odpowiedzi na zapytania OPTIONS
    private function allowPreflightResponse()
    {
        $response = service('response');
        $response->setStatusCode(200); // Odpowiedź 200 OK dla zapytań OPTIONS
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        return $response;
    }
}
