<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'email',
        'password',
        'name',
        'role',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $returnType    = 'array';
}
