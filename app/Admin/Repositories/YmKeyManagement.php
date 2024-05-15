<?php

namespace App\Admin\Repositories;

use App\Models\YmKeyManagement as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class YmKeyManagement extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
