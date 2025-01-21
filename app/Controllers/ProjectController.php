<?php
namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class ProjectController extends ResourceController
{
    private $secretKey = 'testowy_klucz';

    public function getUserFromToken($token)
    {
        $authController = new AuthController();
        return $authController->getUserFromToken($token);
    }

    // Obsługuje zapytania OPTIONS
    public function optionsCreate()
    {
        $response = service('response');
        // Dodaj odpowiednie nagłówki CORS
        $response->setHeader('Access-Control-Allow-Origin', 'http://localhost:3000'); // Zmienna, w zależności od Twojego środowiska
        $response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        return $response->setStatusCode(200); // Odpowiedź 200 dla zapytania OPTIONS
    }

    // Zrób options dla update
    public function optionsUpdate()
    {
        $response = service('response');
        // Dodaj odpowiednie nagłówki CORS
        $response->setHeader('Access-Control-Allow-Origin', 'http://localhost:3000'); // Zmienna, w zależności od Twojego środowiska
        $response->setHeader('Access-Control-Allow-Methods', 'PUT, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        return $response->setStatusCode(200); // Odpowiedź 200 dla zapytania OPTIONS
    }



    public function create()
    {
        log_message('critical', 'uruchamiam create');
        $authHeader = $this->request->getHeader('Authorization');
        if ($authHeader) {
            $token = str_replace('Bearer ', '', $authHeader->getValue());
            $user = $this->getUserFromToken($token);
            log_message('critical', 'User from token: ' . json_encode($user));
            if ($user && isset($user['user_id'])) {
                $model = new ProjectModel();
                $projectData = [
                    'user_id' => $user['user_id'],
                    'title' => 'Untitled',
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                if ($model->save($projectData)) {
                    $projectData['id'] = $model->insertID();
                    log_message('critical', 'Project ID after insert: ' . $projectData['id']);
                    $project = $model->find($projectData['id']);
                     unset($project['uploaded_image']);
                    if ($project) {
                        return $this->respond(['message' => 'Project created successfully', 'project' => $project], 200);
                    } else {
                        log_message('critical', 'Project not found after creation');
                        return $this->fail('Project not found after creation', 404);
                    }
                } else {
                    log_message('critical', 'Failed to save project');
                    return $this->fail('Failed to save project', 500);
                }
            } else {
                return $this->failUnauthorized('Invalid token or user ID not found');
            }
        } else {
            return $this->failUnauthorized('Authorization token missing');
        }
    }

    private function getUser() {
        $authHeader = $this->request->getHeader('Authorization');
        if ($authHeader) {
            $token = str_replace('Bearer ', '', $authHeader->getValue());
            $user = $this->getUserFromToken($token);
            return $user;
        } else {
            return null;
        }
    }



    public function update($id = null)
    {
        $json = $this->request->getJSON(true);
        $user = $this->getUser();
        if ($json && isset($json['user_id']) && isset($json['title']) && isset($json['image'])) {
            $model = new ProjectModel();
            $project = $model->where('user_id', $user['user_id'])->find($id);
            $id = $json['id'];

            if ($project) {
            $projectData = [
                'user_id' => $json['user_id'],
                'title' => $json['title'],
                'changes' => $json['changes'],
                'updated_at' => date('Y-m-d H:i:s'),
                'public' => $json['public'],
            ];
            log_message('critical', 'Project data: ' . json_encode($projectData));

            // Decode base64 image
           $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $json['image']);
           $imageData = base64_decode($imageData);
           $imageName = uniqid() . '.png';
           $imagePath = WRITEPATH . 'uploads/' . $imageName;

           if (isset($json['result_image'])) {
                $resultImageData = preg_replace('#^data:image/\w+;base64,#i', '', $json['result_image']);
                $resultImageData = base64_decode($resultImageData);
                $resultImageName = uniqid() . '.png';
                $resultImagePath = WRITEPATH . 'uploads/' . $resultImageName;
           }


            if (file_put_contents($imagePath, $imageData)) {
                $projectData['uploaded_image'] = $imageName;
                if (isset($resultImageData) && file_put_contents($resultImagePath, $resultImageData)) {
                    $projectData['result_image'] = $resultImageName;
                }


                    if ($model->update($id, $projectData)) {
                        $project = $model->find($id);
                    unset($project['uploaded_image']);
                        return $this->respond(['message' => 'Project updated successfully', 'project' => $project], 200);
                } else {
                        log_message('critical', 'Failed to update project');
                        return $this->fail('Failed to update project', 500);
                }
            } else {
                log_message('critical', 'Failed to save image');
                return $this->fail('Failed to save image', 500);
            }
            } else {
                log_message('critical', 'Project not found');
                return $this->fail('Project not found', 404);
            }
        } else {
            return $this->fail('Invalid JSON data', 400);
        }
    }

    public function optionsShow()
    {
        $response = service('response');
        // Dodaj odpowiednie nagłówki CORS
        $response->setHeader('Access-Control-Allow-Origin', 'http://localhost:3000'); // Zmienna, w zależności od Twojego środowiska
        $response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Authorization');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        return $response->setStatusCode(200); // Odpowiedź 200 dla zapytania OPTIONS
    }

    public function show($id = null)
    {
        $model = new ProjectModel();
        $user = $this->getUser();

        $project = $model->where('user_id', $user['user_id'])->find($id);
        if ($project && $user) {
            if (isset($project['uploaded_image'])) {
                $imagePath = WRITEPATH . 'uploads/' . $project['uploaded_image'];
                if (file_exists($imagePath)) {
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $project['image'] = $imageData;
                    unset($project['uploaded_image']);
                } else {
                    unset($project['uploaded_image']);
                }
            }
            if (isset($project['result_image'])) {
                $resultImagePath = WRITEPATH . 'uploads/' . $project['result_image'];
                if (file_exists($resultImagePath)) {
                    $resultImageData = base64_encode(file_get_contents($resultImagePath));
                    $project['result_image'] = $resultImageData;
                } else {
                    unset($project['result_image']);
                }
            }
            return $this->respond(['project' => $project],200);
        } else {
            return $this->failNotFound('Project not found');
        }
    }

    public function setShareProject($id = null){
        $model = new ProjectModel();
        $user = $this->getUser();
        $project = $model->where('user_id', $user['user_id'])->find($id);
        $json = $this->request->getJSON(true);
        if ($project) {
            $projectData = [
                'public' => $json['public'],
            ];
            log_message('critical', 'Project data: ' . json_encode($projectData));
            if ($model->update($id, $projectData)) {
                $project = $model->find($id);
                return $this->respond(['message' => 'Project updated successfully', 'project' => $project], 200);
            } else {
                return $this->fail('Failed to update project', 500);
            }
        } else {
            return $this->failNotFound('Project not found');
        }

    }


    public function showUserProjectsList()
    {

        $model = new ProjectModel();
        $user = $this->getUser();

        $json = $this->request->getJSON(true);
        if (isset($json['order'])) {
            $order = $json['order'];
            if ($order == 'asc') {
                $projects = $model->where('user_id', $user['user_id'])->orderBy('updated_at', 'asc')->findAll();
            } else {
                $projects = $model->where('user_id', $user['user_id'])->orderBy('updated_at', 'desc')->findAll();
            }
        }else{
            $projects = $model->where('user_id', $user['user_id'])->findAll();
        }

        if (isset($json['count'])) {
            $projects = array_slice($projects, 0, $json['count']);
        }


        if ($projects) {
                $projectList = [];
                foreach ($projects as $project) {
                    $projectData = [
                        'id' => $project['id'],
                        'title' => $project['title'],
                        'image' => null,
                        'public' => $project['public'],
                    ];

                    if (isset($project['result_image'])) {
                        $resultImagePath = WRITEPATH . 'uploads/' . $project['result_image'];
                        if (file_exists($resultImagePath)) {
                            $resultImageData = base64_encode(file_get_contents($resultImagePath));
                            $projectData['image'] = $resultImageData;
                        }
                    }

                    $projectList[] = $projectData;
                }
                return $this->respond(['projects' => $projectList], 200);
        } else {
            return $this->respond(['projects' => []], 200);
        }
    }

    public function delete($id = null)
    {
        $model = new ProjectModel();
        $user = $this->getUser();
        $project = $model->where('user_id', $user['user_id'])->find($id);
        if ($project) {
            if ($model->delete($id)) {
                return $this->respondDeleted(['message' => 'Project deleted successfully']);
            } else {
                return $this->fail('Failed to delete project', 500);
            }
        } else {
            return $this->failNotFound('Project not found');
        }
    }




    public function copyProject($id = null){
        $model = new ProjectModel();
        $project = $model->find($id);
        $user = $this->getUser();

        if ($project && $project['public'] == 1) {
            $projectData = [
                'user_id' => $user['user_id'],
                'title' => $project['title'],
                'uploaded_image' => $project['uploaded_image'],
                'changes' => $project['changes'],
                'result_image' => $project['result_image'],
                'published_at' => date('Y-m-d H:i:s'),
                'public' => 0,
            ];
            if ($model->save($projectData)) {
                $projectData['id'] = $model->insertID();
                $project = $model->find($projectData['id']);
                unset($project['uploaded_image']);
                return $this->respond(['message' => 'Project copied successfully', 'project' => $project], 200);
            } else {
                return $this->fail('Failed to copy project', 500);
            }
        } else {
            return $this->failNotFound('Project not found or not public');
        }
    }

    private function getUserFromUsername($username){
        $model = new UserModel();
        $user = $model->where('username', $username)->first();
        return $user;
    }

    private function getUsernameFromId($id){
        $model = new UserModel();
        $user = $model->find($id);
        return $user['username'];
    }

    public function getPublicProjects($username = null){

        $model = new ProjectModel();
        if ($username) {
            $user = $this->getUserFromUsername($username);
            $projects = $model->where('user_id', $user['id'])->where('public', 1)->findAll();
        } else {
            $projects = $model->where('public', 1)->findAll();
        }
        $projectList = [];
        if ($projects){
            foreach ($projects as $project) {
                $projectData = [
                    'id' => $project['id'],
                    'title' => $project['title'],
                    'image' => null,
                    'public' => $project['public'],
                    'author' => $this->getUsernameFromId($project['user_id']),
                ];
                if (isset($project['result_image'])) {
                    $resultImagePath = WRITEPATH . 'uploads/' . $project['result_image'];
                    if (file_exists($resultImagePath)) {
                        $resultImageData = base64_encode(file_get_contents($resultImagePath));
                        $projectData['image'] = $resultImageData;
                    }
                }
                $projectList[] = $projectData;
            }


            return $this->respond(['projects' => $projectList], 200);
        } else {
            return $this->respond(['projects' => []], 200);
        }
    }



}
