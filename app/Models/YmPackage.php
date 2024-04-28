<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

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
        return $this->hasOne(YmAccount::class, 'id', 'account_id')->where('account_type','=',0);
    }


    // 关联开发者接收账号表
    public function receive_account()
    {
        return $this->hasOne(YmAccount::class, 'id', 'receive_account_id')->where('account_type','=',1);
    }
}
