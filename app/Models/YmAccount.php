<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class YmAccount extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'ym_accounts';
    public $timestamps = true;

}
