<?php

namespace App\Admin\Repositories;

use App\Models\YmAccount as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class YmAccount extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
