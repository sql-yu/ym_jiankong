<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\OperationLog;

class YmKeyManagement extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'ym_key_management';
    public $timestamps = true;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($model) {
            #删除时记录日志
            $request = request();
            OperationLog::logDesc($request,'ym_key_management','de','key_management',$model->id);
        });
    }

    // 关联开发者账号表
//    public function account()
//    {
//        return $this->hasOne(YmAccount::class, 'package_name', 'package_name');
//    }

}
