<?php
namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table      = 'projects';
    protected $primaryKey = 'id';

    protected $allowedFields = ['user_id', 'title', 'uploaded_image', 'changes', 'result_image', 'published_at','public'];
    protected $useTimestamps = true;
    public function find($id = null)
    {
        $result = parent::find($id);
        if (isset($result['uploaded_image'])) {
            $result['image'] = $result['uploaded_image'];
        }


        return $result;
    }
}
