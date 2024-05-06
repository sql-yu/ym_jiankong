<?php

namespace App\Services;

use App\Models\YmPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class PackageService
{
    #获取开发者账号下包的数量
    public static function get_account_package_count($account_id)
    {
        return YmPackage::query()->where([
            'account_id'=>$account_id
        ])->where('transfer_status','!=',2)->count();
    }


    #获取开发者接收账号下包的数量
    public static function get_receive_account_package_count($receive_account_id)
    {
        return YmPackage::query()->where([
            'receive_account_id'=>$receive_account_id,
            'transfer_status'=>2
        ])->count();

    }



}