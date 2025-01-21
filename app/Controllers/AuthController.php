<?php namespace App\Controllers;

use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    private $secretKey = 'testowy_klucz';

    public function register()
    {
        $model = new UserModel();
        $input = $this->request->getJSON(true);

        if (!$model->validate($input)) {
            return $this->respond(['error' => $model->errors()], 202);
        }

        if (isset($input['password'])) {
            $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        } else {

            return $this->respond(['error' => 'Password is required'], 202);
        }

        $avatarPath = '';
        if (isset($input['avatar']) && !empty($input['avatar'])) {
            $avatarData = explode(',', $input['avatar']);
            $avatarContent = base64_decode(end($avatarData));
            $avatarName = uniqid() . '.png'; // Możesz zmienić rozszerzenie na odpowiednie

            file_put_contents(WRITEPATH . 'uploads/avatars/' . $avatarName, $avatarContent);
            $avatarPath = $avatarName;
        }

        $input['avatar'] = $avatarPath;
        $model->save($input);

        return $this->respond(['message' => 'User registered successfully'], 200);
    }

    public function login()
    {
        $model = new UserModel();
        $input = $this->request->getJSON(true);

        if (!isset($input['email']) || !isset($input['password'])) {
            return $this->respond(['error' => 'Email or password not provided'], 401);
        }

        $user = $model->where('email', $input['email'])->first();

        if (!$user || !password_verify($input['password'], $user['password'])) {
            return $this->respond(['error' => 'Invalid email or password'], 401);
        }

        $token = $this->generateJwt($user);

        $avatar = base_url('avatar/' . $user['avatar']);
        $user['avatar'] = $avatar;


        return $this->respond(['token' => $token, 'user' => $user]);
    }

    private function generateJwt($user)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 36000;
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $user['id'],
            'username' => $user['username']
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }
      public function getUserFromToken($token)
      {
          try {
              $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
              log_message('critical', 'Decoded token: ' . json_encode($decoded));
              return (array) $decoded;
          } catch (\Exception $e) {
              log_message('critical', 'Token decode error: ' . $e->getMessage());
              return null;
          }
      }



    public function refreshToken()
    {
        $authHeader = $this->request->getHeader('Authorization');
        if (!$authHeader) {
            return $this->respond(['error' => 'Authorization token missing'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader->getValue());
        try {
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($this->secretKey, 'HS256'));
            $userModel = new UserModel();
            $user = $userModel->find($decoded->user_id);

            if (!$user) {
                return $this->respond(['error' => 'User not found'], 401);
            }

            $newToken = $this->generateJwt($user);
            return $this->respond(['token' => $newToken]);

        } catch (\Exception $e) {
            return $this->respond(['error' => 'Invalid or expired token'], 401);
        }
    }


    public function getUser($username = null)
    {
        $model = new UserModel();
        $data = $model->where('username', $username)->first();

        if ($data) {
            return $this->respond($data);
        }

        return $this->failNotFound('No user found');
    }


    public function updateAvatar()
    {
        $model = new UserModel();
        $authHeader = $this->request->getHeader('Authorization');
        if (!$authHeader) {
            return $this->respond(['error' => 'Authorization token missing'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader->getValue());
        $decoded = $this->getUserFromToken($token);
        if (!$decoded) {
            return $this->respond(['error' => 'Invalid or expired token'], 401);
        }

        $user = $model->find($decoded['user_id']);
        if (!$user) {
            return $this->respond(['error' => 'User not found'], 401);
        }

        $input = $this->request->getJSON(true);
        if (!isset($input['avatar']) || empty($input['avatar'])) {
            return $this->respond(['error' => 'Avatar data is required'], 400);
        }

        // Sprawdzenie poprawności base64
       $avatarPath = '';
               if (isset($input['avatar']) && !empty($input['avatar'])) {
                   $avatarData = explode(',', $input['avatar']);
                   $avatarContent = base64_decode(end($avatarData));
                   $avatarName = uniqid() . '.png'; // Możesz zmienić rozszerzenie na odpowiednie

                   file_put_contents(WRITEPATH . 'uploads/avatars/' . $avatarName, $avatarContent);
                   $avatarPath = $avatarName;
               }

        // Upewnij się, że katalog istnieje
        if (!is_dir($avatarPath)) {
            mkdir($avatarPath, 0755, true);
        }

        $filePath = $avatarPath . $avatarName;

        // Zapis pliku
        if (!file_put_contents($filePath, $avatarData)) {
            return $this->respond(['error' => 'Failed to save avatar'], 500);
        }

        // Aktualizacja danych użytkownika
        $user['avatar'] = $avatarName;
        if (!$model->save($user)) {
            return $this->respond(['error' => 'Failed to update user avatar', 'details' => $model->errors()], 500);
        }

        return $this->respond(['message' => 'Avatar updated successfully', 'avatar_url' => base_url('avatar/' . $avatarName)], 200);
    }

    public function updatePassword(){
        $model = new UserModel();
        $authHeader = $this->request->getHeader('Authorization');
        if (!$authHeader) {
            return $this->respond(['error' => 'Authorization token missing'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader->getValue());
        $decoded = $this->getUserFromToken($token);
        if (!$decoded) {
            return $this->respond(['error' => 'Invalid or expired token'], 401);
        }

        $user = $model->find($decoded['user_id']);
        if (!$user) {
            return $this->respond(['error' => 'User not found'], 401);
        }

        $input = $this->request->getJSON(true);
        if (!isset($input['password'])) {
            return $this->respond(['error' => 'Password is required'], 202);
        }

        $user['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        $model->save($user);

        return $this->respond(['message' => 'Password changed successfully'], 200);
    }

    public function updateDescription()
    {
        $model = new UserModel();
        $authHeader = $this->request->getHeader('Authorization');
        if (!$authHeader) {
            return $this->respond(['error' => 'Authorization token missing'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader->getValue());
        $decoded = $this->getUserFromToken($token);
        if (!$decoded) {
            return $this->respond(['error' => 'Invalid or expired token'], 401);
        }

        $user = $model->find($decoded['user_id']);
        if (!$user) {
            return $this->respond(['error' => 'User not found'], 401);
        }

        $input = $this->request->getJSON(true);
        if (!isset($input['description'])) {
            return $this->respond(['error' => 'Description is required'], 202);
        }
        log_message('critical', 'Description: ' . $input['description']);

        $user['description'] = $input['description'];

        // Wyłącz walidację unikalności dla pól username i email
        $model->disableUniqueValidation();

        if (!$model->save($user)) {
            return $this->respond(['error' => 'Failed to update description', 'details' => $model->errors()], 500);
        }

        log_message('critical', 'User: ' . json_encode($user));

        return $this->respond(['message' => 'Description updated successfully'], 200);
    }
}