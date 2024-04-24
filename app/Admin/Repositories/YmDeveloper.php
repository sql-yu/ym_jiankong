<?php

namespace App\Admin\Repositories;

use App\Models\YmDeveloper as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class YmDeveloper extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
