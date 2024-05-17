<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\YmAccount;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Renderable\ReceiveAccountPackagesTable;
use Dcat\Admin\Admin;
use App\Services\PackageService;
use App\Libraries\OperationLog;

use App\Libraries\GoogleAuthenticator;

class YmReceiveAccountController extends AdminController
{
    public $title = '开发者接收者账号';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        return Grid::make(new YmAccount(), function (Grid $grid) {
            $grid->async();
            $account_status = request()->get('account_status', 99);
            $key_status = request()->get('key_status', 99);
            if(is_array($account_status)){
                if(in_array(2,$account_status)){
                    $grid->model()->orderBy('sealed_time','desc');
                }
            }

            $grid->model()->orderBy('name','desc');
            $grid->model()->where('account_type', '=', 1);



            $grid->column('id')->sortable();
            $grid->column('name')->copyable();
            $grid->column('login_ip')->copyable();
            $grid->column('login_password')->copyable();
            $grid->column('type')->display(function ($value) {
                $arr = config('account.type');
                return "<span>{$arr[$value]}</span>";
            })->sortable();
            $grid->column('google_authenticator');
            $grid->column('account_status')->options()->radio(config('account.account_status'));
            $grid->column('key_status')->options()->radio(config('account.key_status'));

            $intersection=0;
            if(is_array($key_status)){
                $intersection = array_intersect($key_status, [2,3]);
            }else{
                $intersection = (in_array($key_status, [2,3]) || ($key_status==99));
            }
            if (!empty($intersection)) {
                $grid->column('key_complete_time', '重置key时间')->display(function () {
                    if(empty($this->key_complete_time)){
                        return '';
                    }
                    if($this->key_status == 1){
                        return '';
                    }
                    return date('Y-m-d H:i:s', $this->key_complete_time);
                });
            }

            $is_sealed = 0;
            if(is_array($account_status)){
                $is_sealed = array_intersect($account_status, [2]);
            }else{
                $is_sealed = (in_array($account_status, [2]) || ($account_status==99));
            }
            if (!empty($is_sealed)) {
                $grid->column('sealed_time','封号时间')->display(function(){
                    if(empty($this->sealed_time)){
                        return '';
                    }
                    return $this->sealed_time;
                });
            }

            $grid->column('phone_no');
            $grid->column('phone_number');

            $grid->column('所有包')->display(function () {
                return PackageService::get_receive_account_package_count($this->id);
            })->modal(function (Grid\Displayers\Modal $modal) {
                // 标题
                $modal->title('包列表');

                // 自定义图标
                // $modal->icon('feather icon-edit');

                // 传递当前行字段值
                return ReceiveAccountPackagesTable::make()->payload(['id' => $this->id]);
            });


            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->where('别名/ip', function ($query) {

                    $query->where('name', 'like', "%{$this->input}%")
                        ->orWhere('login_ip', 'like', "%{$this->input}%");
                })->width(3);

                $filter->in('type','账号类型')->multipleSelect(config('account.type'))->width(3);
                $filter->in('account_status','账号状态')->multipleSelect(config('account.account_status'))->default('1')->width(3);
                $filter->in('key_status','key状态')->multipleSelect(config('account.key_status'))->width(3);
                $filter->in('transfer_status','转移状态')->multipleSelect(config('account.transfer_status'))->width(3);
            });

            $grid->toolsWithOutline(false);
            $grid->disableBatchDelete(); //禁用批量删除
            $grid->disableRowSelector(); // 禁用行选择器

            // 添加查看包的key文件按钮
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                //关闭行操作 删除
                $actions->disableDelete();
                // 去掉查看
                $actions->disableView();


//                // 默认编辑按钮
//                $actions->append('<a href="key_management?&package_name='.$this->package_name.'" >查看key文件</a>');
            });

        });
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new YmAccount(), function (Form $form) {
            $form->display('id');
            $form->text('name')->rules('required');
            $form->ip('login_ip')->rules('required');
            $form->text('login_username')->default('Administrator')->rules('required');
            $form->text('login_password')->rules('required');
            $form->radio('type')->options(config('account.type'))->default('1');
            $form->radio('account_status')->options(config('account.account_status'))->default('1')->saving(function ($v) {
                if (empty($v)) {
                    return 1;
                } else {
                    return $v;
                }
            });
            $form->radio('key_status')->options(config('account.key_status'))->default('1')->saving(function ($v) {
                if (empty($v)) {
                    return 1;
                } else {
                    return $v;
                }
            });
            $form->radio('transfer_status')->options(config('account.transfer_status'))->default('1')->saving(function ($v) {
                if (empty($v)) {
                    return 1;
                } else {
                    return $v;
                }
            });

            $form->text('google_email');
            $form->text('google_password');
            $form->text('phone_no');
            $form->text('phone_number');
            $form->text('pds')->saving(function ($v) {
                if (empty($v)) {
                    return '';
                } else {
                    return $v;
                }
            });;
            $form->text('google_authenticator');

            $form->hidden('account_type')->value(1);

            $form->list('spare_code')->saving(function ($v) {
                // 转化为json格式存储
                return json_encode($v);
            });

            $form->number('num_sus');

            $form->datetime('sealed_time');

            $form->textarea('other_data')->saving(function ($v) {
                if (empty($v)) {
                    return '';
                } else {
                    return $v;
                }
            });

            #处理日志逻辑
            $request = request();
            if ($form->model()->exists) {
                // 模型已存在，执行更新操作
                // 你的更新逻辑代码
                // 假设你是通过 PUT 方法提交表单
                if ($request->method() == 'PUT') { #更新操作
                    OperationLog::logDesc($request, 'ym_accounts', 'up', 'receive_account', $form->model()->id);
                }
            } else {
                // 模型不存在，执行插入操作
                // 你的插入逻辑代码
                if ($request->method() == 'POST') { #插入操作
                    OperationLog::logDesc($request, 'ym_accounts', 'in', 'receive_account');
                }
            }

            //保存前回调
            $form->saving(function (Form $form) {

                #key重置状态 需要记录重置时间
                if (in_array($form->key_status,[2,3])) {
                    // echo $form->account_status.'-'.$form->model()->id;

                    $form->model()->key_complete_time = time();
                }

                #封号时 记录时间
                if (in_array($form->account_status,[2])) {
                    // echo $form->account_status.'-'.$form->model()->id;

                    $form->model()->sealed_time = date('Y-m-d H:i:s',time());
                }
            });


            // 去掉删除按钮
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
                // 去掉查看
                $tools->disableView();
            });



        });
    }
}
