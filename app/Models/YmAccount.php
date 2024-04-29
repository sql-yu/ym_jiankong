<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\OperationLog;

class YmAccount extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'ym_accounts';
    public $timestamps = true;

    protected static function boot()
    {
        parent::boot();
 
        static::deleting(function($model) {
            #删除时记录日志
            $request = request();
            OperationLog::logDesc($request,'ym_accounts','de','account',$model->id);
        });
    }

}
