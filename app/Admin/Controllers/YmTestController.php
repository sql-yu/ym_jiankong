<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\YmKeyManagement;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Actions\Account\ShowPackageAction;
use App\Admin\Renderable\AccountPackagesTable;
use App\Services\PackageService;
use App\Libraries\OperationLog;
use Dcat\Admin\Http\JsonResponse;
use App\Models\YmKeyManagement as M_YmKeyManagement;
use Illuminate\Support\Facades\DB;

class YmTestController extends AdminController
{
    public $title = '临时调试';

    protected $key = '7bqMe5MsYu31';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        if(request()->get('key','') != $this->key){
            echo 'err';exit;
        }


        $this->test1();
    }


    protected function test1(){
            echo date('Y-m-d H:i:s');exit;
    }


}
