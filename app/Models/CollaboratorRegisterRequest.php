<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;

class CollaboratorRegisterRequest extends Model
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];

    protected $searchable = [
        'columns' => [],
    ];

    protected $with = [
        'collaborator',
    ];

    public function collaborator()
    {
        return $this->belongsto(Collaborator::class);
    }
}
