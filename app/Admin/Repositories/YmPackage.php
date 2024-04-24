<?php

namespace App\Admin\Repositories;

use App\Models\YmPackage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class YmPackage extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
