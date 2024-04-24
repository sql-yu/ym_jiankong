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

}
