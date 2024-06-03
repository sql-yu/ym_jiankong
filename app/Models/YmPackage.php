<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\OperationLog;

class YmPackage extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    public static $REVIEW = 0;
    public static $ONLINE = 1;
    public static $REMOVE = 2;

    public static $status = [
        0=>'审核中',
        1=>'正式',
        2=>'下架',
        3=>'封测',
    ];




    protected $table = 'ym_package';

    // 关联开发者账号表
    public function account()
    {
        return $this->hasOne(YmAccount::class, 'id', 'account_id')->whereIn('account_type', [0,1]);
    }


    // 关联开发者接收账号表
    public function receive_account()
    {
        return $this->hasOne(YmAccount::class, 'id', 'receive_account_id')->whereIn('account_type', [0,1]);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($model) {
            #删除时记录日志
            $request = request();
            OperationLog::logDesc($request,'ym_package','de','package',$model->id);
        });
    }

}
