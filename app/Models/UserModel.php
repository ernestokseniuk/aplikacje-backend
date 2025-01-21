<?php namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'email', 'password', 'avatar', 'description'];

    protected $validationRules = [
        'id' => 'required|is_natural_no_zero',
        'username' => 'required|min_length[3]|max_length[255]',
        'email' => 'required|valid_email|max_length[255]',
        'password' => 'required|min_length[8]',
        'avatar' => 'permit_empty|string',
        'description' => 'permit_empty|string'
    ];
    public function disableUniqueValidation()
    {
        // Sprawdź, czy istnieje reguła unikalności i usuń ją
        foreach ($this->validationRules as $field => $rules) {
            // Sprawdź, czy w regułach występuje 'is_unique' i usuń
            $this->validationRules[$field] = str_replace('is_unique', '', $this->validationRules[$field]);
        }
    }


    protected $validationMessages = [
        'id' => [
            'required' => 'ID jest wymagane.',
            'is_natural_no_zero' => 'ID musi być liczbą naturalną większą od zera.'
        ],
        'username' => [
            'required' => 'Nazwa użytkownika jest wymagana.',
            'min_length' => 'Nazwa użytkownika musi mieć co najmniej 3 znaki.',
            'max_length' => 'Nazwa użytkownika nie może przekraczać 255 znaków.'
        ],
        'email' => [
            'required' => 'Email jest wymagany.',
            'valid_email' => 'Podaj prawidłowy adres email.',
            'max_length' => 'Email nie może przekraczać 255 znaków.'
        ],
        'password' => [
            'required' => 'Hasło jest wymagane.',
            'min_length' => 'Hasło musi mieć co najmniej 8 znaków.'
        ]
    ];
}