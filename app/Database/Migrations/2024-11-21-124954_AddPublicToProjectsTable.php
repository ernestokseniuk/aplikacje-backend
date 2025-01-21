<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicToProjectsTable extends Migration
{
    public function up()
    {
        // Sprawdź, czy tabela 'projects' istnieje
        if ($this->db->tableExists('projects')) {
            // Dodaj kolumnę 'public' do istniejącej tabeli 'projects'
            $this->forge->addColumn('projects', [
                'public' => [
                    'type' => 'BOOLEAN',
                    'default' => false,
                ],
            ]);

            // Ustaw domyślną wartość false dla istniejących rekordów
            $db = \Config\Database::connect();
            $builder = $db->table('projects');
            $builder->set('public', false)->update();
        }
    }

    public function down()
    {

    }
}