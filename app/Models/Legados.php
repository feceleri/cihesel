<?php

namespace App\Models;

use CodeIgniter\Model;

class Legados extends Model
{
    protected $table            = 'atendimento';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['senha', 'entrada', 'saida', 'deletado', 'obs', 'idPaciente'];

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getAll()
    {
        $result = $this->orderBy('id')->findAll();
        return $result;
    }

    public function getService($id)
    {
        $result = $this->find($id);
        return $result;
    }

    public function deleteService($id)
    {
        if ($this->find($id)) {
            $this->delete($id);
            return true;
            // return 'ACHOU!';
        } else {
            return false;
            // return 'FKIHJDES';
        }
    }
}