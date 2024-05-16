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

        // $ga = new GoogleAuthenticator();
        // $secret = "zkog j745 gziw dnz7 sgf6 porb e35h 7haw";
        // $oneCode = $ga->getCode($secret);
        // echo $oneCode.'='.date('y-m-d H:i:s',time());
        // exit;



        return Grid::make(new YmAccount(), function (Grid $grid) {
            $grid->async();
            $status = request()->get('status', 99);
            if(is_array($status)){
                if(in_array(2,$status)){
                    $grid->model()->orderBy('sealed_time','desc');
                }
            }



            $grid->model()->orderBy('name','desc');
//            $grid->paginate(3);

            $grid->model()->where('account_type', '=', 1);

            $grid->column('id')->sortable();
            $grid->column('name')->copyable();
            $grid->column('login_ip')->copyable();
            // $grid->column('login_username');
            $grid->column('login_password')->copyable();
            $grid->column('type')->display(function ($value) {
                $arr = config('account.type');

                return "<span>{$arr[$value]}</span>";
            })->sortable();
            $grid->column('google_authenticator');
            $grid->column('status')->options()->radio(config('account.status'));

            $intersection=0;
            if(is_array($status)){
                $intersection = array_intersect($status, [4, 5]);
            }else{
                $intersection = (in_array($status, [4, 5]) || ($status==99));
            }
//            if (in_array($status, [4, 5])) {
            if (!empty($intersection)) {
                $grid->column('reset_key_time', '重置key时间')->display(function () {
                    if(empty($this->reset_key_time)){
                        return '';
                    }
                    return date('Y-m-d H:i:s', $this->reset_key_time);
                });
            }

            $is_sealed = 0;
            if(is_array($status)){
                $is_sealed = array_intersect($status, [2]);
            }else{
                $is_sealed = (in_array($status, [2]) || ($status==99));
            }
            if (!empty($is_sealed)) {
                $grid->column('sealed_time','封号时间')->display(function(){
                    if(empty($this->sealed_time)){
                        return '';
                    }
                    return $this->sealed_time;
                });
            }

            // $grid->column('num_sus');
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

                $filter->in('status','账号状态')->multipleSelect(config('account.status'))->default('1')->width(3);
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
                if (in_array($form->status,[4,5])) {
                    // echo $form->status.'-'.$form->model()->id;

                    $form->model()->reset_key_time = time();
                }

                #封号时 记录时间
                if (in_array($form->status,[2])) {
                    // echo $form->status.'-'.$form->model()->id;

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
