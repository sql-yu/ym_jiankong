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

class YmKeyManagementController extends AdminController
{
    public $title = 'key管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        return Grid::make(new YmKeyManagement(), function (Grid $grid) {
            $grid->async();
            $grid->model()->orderBy('id','desc');


            $grid->column('id','ID');
            $grid->column('key')->copyable();
            $grid->column('package_name','包id')->copyable();
            $grid->column('alias','别名')->copyable();
            $grid->column('alias_password','别名密码')->copyable();
            $grid->column('key_file','密钥文件(点击下载)')->display(function (){
                return substr($this->key_file,9);#'http://'.request()->getHost().'/uploads/' .
            })->link(function (){
                return 'http://'.request()->getHost().'/uploads/' . $this->key_file;
            });


            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->where('package_name', function ($query) {

                    $query->where('package_name', 'like', "%{$this->input}%");

                })->width(3);

            });

            $grid->toolsWithOutline(false);
            $grid->disableBatchDelete();//禁用批量删除
            $grid->disableRowSelector(); // 禁用行选择器

        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new YmKeyManagement(), function (Show $show) {
            $show->field('key');
            $show->field('package_name');
            $show->field('alias','别名');
            $show->field('alias_password','别名密码');
            $show->field('key_file','密钥文件')->as(function (){
                return 'http://'.request()->getHost().'/uploads/' . $this->key_file;
            });#

        });
    }


    public function create(Content $content)
    {
        return $content
            ->title('key管理-新增')
            ->body($this->form());
    }

    protected function form()
    {
        return Form::make(new YmKeyManagement(), function (Form $form) {

            $form->row(function ($form) {
                $form->text('key','key')->rules('required');
                $form->text('alias','别名');
                $form->text('alias_password','别名密码');
                $form->list('package_name','包名');
                $form->file('key_file','密钥文件')->url('users/files')->autoUpload();#
            });

        });
    }




    // 新增存储逻辑
    public function store()
    {
        $key = request()->post('key','');
        $alias = request()->post('alias','');
        $alias_password = request()->post('alias_password','');
        $package_name = request()->post('package_name',[]);
        $key_file = request()->post('key_file','');

        unset($package_name['values']['_def_']);
        $package_name = $package_name['values'];

        if(!$key){
            return JsonResponse::make()->error('key不能为空');
        }

        if(empty($package_name)){
            return JsonResponse::make()->error('包名不能为空');
        }

        if(!$key_file){
            return JsonResponse::make()->error('密钥文件不能为空');
//            return JsonResponse::make()->success('成功！');
        }

        foreach ($package_name as $item){
            if(M_YmKeyManagement::query()->where('key',$key)->where('package_name',$item)->exists()){
                return JsonResponse::make()->error('key和【'.$item.'】已存在');
            }
        }

        $in_data = [];

        foreach ($package_name as $itemv){
            if(!copy('uploads/'.$key_file, 'uploads/'.date('Ymd').'/'.$itemv.'.jks')) {
                return JsonResponse::make()->error('文件复制失败，请联系管理员');
            }
            $in_data[] = [
                'key'=>$key,
                'alias'=>$alias,
                'alias_password'=>$alias_password,
                'key_file'=>date('Ymd').'/'.$itemv.'.jks',
                'package_name'=>$itemv,
                'created_at'=>date('Y-m-d H:i:s',time()),
            ];
        }



        try {
            DB::beginTransaction();
            $st = M_YmKeyManagement::query()->insert($in_data);
            if($st){
                DB::commit();
            }else{
                DB::rollBack();
                return JsonResponse::make()->error('数据库错误');
            }

        }catch (\Exception $exception){
            DB::rollBack();
            return JsonResponse::make()->error($exception->getMessage());
        }


        OperationLog::logDesc(request(),'ym_key_management','in','key_management');

        return JsonResponse::make()->success('成功！')->location('key_management');


//echo 1;
        // 新增保存逻辑
    }




    // 编辑逻辑
    public function edit($id, Content $content)
    {
//        return $this->form()->edit($id);

        return $content
            ->title('key管理-编辑')
            ->body($this->edit_form()->edit($id));

        // 自定义编辑页面逻辑
    }

    protected function edit_form()
    {
        return Form::make(new YmKeyManagement(), function (Form $form) {

            $form->row(function ($form) {
                $form->text('key','key')->rules('required');
                $form->text('package_name','包名');
                $form->text('alias','别名');
                $form->text('alias_password','别名密码');
                $form->file('key_file','密钥文件')->url('users/files')->autoUpload();
            });

        });
    }

    // 编辑保存逻辑
    public function update($id)
    {
        $key = request()->post('key','');
        $package_name = request()->post('package_name','');
        $alias = request()->post('alias','');
        $alias_password = request()->post('alias_password','');
        $key_file = request()->post('key_file','');


        if(!$key){
            return JsonResponse::make()->error('key不能为空');
        }

        if(!$package_name){
            return JsonResponse::make()->error('包名不能为空');
        }

        if(!$key_file){
            return JsonResponse::make()->error('密钥文件不能为空');
//            return JsonResponse::make()->success('成功！');
        }

        #检查是否更改密钥文件
        if($key_file != date('Ymd').'/'.$package_name.'.jks'){
            if(!copy('uploads/'.$key_file, 'uploads/'.date('Ymd').'/'.$package_name.'.jks')) {
                return JsonResponse::make()->error('文件复制失败，请联系管理员');
            }
        }

        $key_file = date('Ymd').'/'.$package_name.'.jks';


        OperationLog::logDesc(request(),'ym_key_management','up','key_management',$id);

        try {
            DB::beginTransaction();

            $st = M_YmKeyManagement::query()->where('id',$id)->update(['key'=>$key,'package_name'=>$package_name,'key_file'=>$key_file,'alias'=>$alias,'alias_password'=>$alias_password]);

            if($st){
                DB::commit();
            }else{
                DB::rollBack();
                return JsonResponse::make()->error('数据库错误');
            }

        }catch (\Exception $exception){
            DB::rollBack();
            return JsonResponse::make()->error($exception->getMessage());
        }



        return JsonResponse::make()->success('成功！')->location('key_management');
        // 编辑保存逻辑
    }



}
