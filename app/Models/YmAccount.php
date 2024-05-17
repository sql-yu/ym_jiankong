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

        #删除
        static::deleting(function($model) {
            #删除时记录日志
            $request = request();
            OperationLog::logDesc($request,'ym_accounts','de','account',$model->id);
        });

        #创建
        static::creating(function ($model) {
            #封号记录时间
            if($model->account_status == 2){
                $model->sealed_time = date('Y-m-d H:i:s',time());
            }

            #key重置状态 需要记录重置时间
            if (in_array($model->key_status,[2,3])) {
                $model->key_complete_time = time();
            }

        });

    }

}
