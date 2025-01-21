<?php
namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;


class UserController extends ResourceController{

    public function getUser($username = null){
        $model = new UserModel();
        $data = $model->where('username', $username)->first();
        if($data){
            return $this->respond($data);
        }
        return $this->failNotFound('No user found with username: '.$username);

    }

}