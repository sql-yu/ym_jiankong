<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class YmOperationLog extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'ym_operation_log';
    public $timestamps = true;

}
