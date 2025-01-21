<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\Response;

class AvatarController extends Controller
{
    public function show($filename)
    {
        $path = WRITEPATH . '/uploads/avatars/' . $filename;
        log_message('critical', 'AvatarController::show: ' . $path);

        if (!is_file($path)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException($filename);
        }

        $mimeType = mime_content_type($path);
        return $this->response->setContentType($mimeType)->setBody(file_get_contents($path));
    }
}