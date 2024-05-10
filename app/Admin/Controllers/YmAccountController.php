<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\YmAccount;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Actions\Account\ShowPackageAction;
use App\Admin\Renderable\AccountPackagesTable;
use App\Services\PackageService;
use App\Libraries\OperationLog;

class YmAccountController extends AdminController
{
    public $title = '开发者账号';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        #固定首行
//         Admin::style(
//             <<<STYLE
//         tbody {
//             display: block;
//             max-height: 500px;
//             overflow-y: scroll;

//         }
//         /*设置头与内容自动对齐*/
//         table thead,tfoot,tbody tr {
//             display: table;
//             table-layout: fixed;
//             /*来自coding的添加*/
//             width:  100%;
//             word-wrap:  break-word;
//         }
//         /*给滚动条预留宽度*/
//         table thead,tfoot {
//             width: calc( 100% - 1em);
//             background: #F5F5F5;
//             font-weight: bolder;
//         }
// STYLE
//     );


        return Grid::make(new YmAccount(), function (Grid $grid) {
            $grid->async();
            $status = (int)request()->get('status', 99);
            if($status == 99){#默认查询1
                $grid->model()->where('status','=',1);
            }



            $grid->model()->where('account_type','=',0);

            $grid->column('id')->sortable();
            $grid->column('name')->copyable();
            $grid->column('login_ip')->copyable();
            // $grid->column('login_username');
            $grid->column('login_password')->copyable();
            $grid->column('type')->display(function ($value) {
                $arr = config('account.type');

                return "<span>{$arr[$value]}</span>";
            })->sortable();

            $grid->column('google_authenticator');#->editable()
            $grid->column('status')->options()->radio(config('account.status'));

            if(in_array($status,[4,5])){
                $grid->column('reset_key_time','重置key时间')->display(function(){
                    return date('Y-m-d H:i:s',$this->reset_key_time);
                });
            }

            // $grid->column('num_sus');
            $grid->column('phone_no');
            $grid->column('phone_number');

            $grid->column('所有包')->display(function (){
                return PackageService::get_account_package_count($this->id);
            })->modal(function (Grid\Displayers\Modal $modal) {
                // 标题
                $modal->title('包列表');

                // 自定义图标
                // $modal->icon('feather icon-edit');

                // 传递当前行字段值
                return AccountPackagesTable::make()->payload(['id' => $this->id]);
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->where('别名/ip', function ($query) {

                    $query->where('name', 'like', "%{$this->input}%")
                        ->orWhere('login_ip', 'like', "%{$this->input}%");

                })->width(3);

                $filter->equal('type','账号类型')->select(config('account.type'))->width(3);

                $filter->equal('status','账号状态')->select(config('account.status'))->default(1)->width(3);

            });

            $grid->toolsWithOutline(false);
            $grid->disableBatchDelete();//禁用批量删除
            $grid->disableRowSelector(); // 禁用行选择器



            // #操作设置
            // $grid->actions(function ($actions) {
            //     // 去掉删除
            //     $actions->disableDelete();
            //     // 去掉编辑
            //     $actions->disableEdit();
            //     if($actions->row['status'] == 1){
            //         $url = 'platformcredituserconsumption?Frepayment_order_id='.$actions->row['orderID'];
            //         $actions->add(new ShowPackageAction($url));
            //     }

            // });

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
        return Show::make($id, new YmAccount(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('login_ip');
            $show->field('login_username');
            $show->field('login_password');
            $show->field('status')->as(function ($status) {
                $arr = config('account.status');
                return $arr[$status];

            });
            $show->field('type')->as(function ($type) {
                $arr = config('account.type');
                return $arr[$type];

            });

            $show->field('google_email');
            $show->field('google_password');
            $show->field('phone_no');
            $show->field('phone_number');
            $show->field('pds');
            $show->field('google_authenticator');
            $show->field('spare_code');

            $show->field('num_sus');

            $show->field('other_data');
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
            $form->radio('status')->options(config('account.status'))->default('1')->saving(function ($v) {
                if(empty($v)){
                    return 1;
                }else{
                    return $v;
                }
            });
            $form->text('google_email');
            $form->text('google_password');
            $form->text('phone_no');
            $form->text('phone_number');
            $form->text('pds')->saving(function ($v) {
                if(empty($v)){
                    return '';
                }else{
                    return $v;
                }
            });;
            $form->text('google_authenticator');

            $form->list('spare_code')->saving(function ($v) {
                // 转化为json格式存储
                return json_encode($v);
            });

            $form->number('num_sus');

            $form->textarea('other_data')->saving(function ($v) {
                if(empty($v)){
                    return '';
                }else{
                    return $v;
                }
            });;

            #处理日志逻辑
            $request = request();
            if ($form->model()->exists) {
                // 模型已存在，执行更新操作
                // 你的更新逻辑代码
                // 假设你是通过 PUT 方法提交表单
                if ($request->method() == 'PUT') {#更新操作
                    OperationLog::logDesc($request,'ym_accounts','up','account',$form->model()->id);
                }

            } else {
                // 模型不存在，执行插入操作
                // 你的插入逻辑代码
                if ($request->method() == 'POST') {#插入操作
                    OperationLog::logDesc($request,'ym_accounts','in','account');
                }

            }

            //保存前回调
            $form->saving(function (Form $form) {

                #key重置状态 需要记录重置时间
                if ($form->status == 4) {
                    // echo $form->status.'-'.$form->model()->id;

                    $form->model()->reset_key_time = time();
                }

            });






        });
    }
}
