<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class YmDeveloper extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'ym_developers';
    
}
