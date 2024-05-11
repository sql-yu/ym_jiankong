<?php

namespace App\Admin\Controllers;

use App\Models\YmPackage;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Tools;
use Dcat\Admin\Show;
use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Libraries\OperationLog;

use App\Model\YmAccount;

class YmPackageController extends AdminController
{
    protected $title = "谷歌包管理列表";
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

        return Grid::make(YmPackage::orderBy("review_time",'desc'), function (Grid $grid) {
            $grid->async();#切换成异步请求
            $package_status = (int)request()->get('package_status', 99);
            if($package_status == 99){#默认查询1
                $grid->model()->where('package_status','=',1);
            }


            // $grid->column('id')->hidden();
            $grid->column('icon','icon')
                // ->image("http://54.173.118.3",100,100)
                ->display(function($icon){#
                    return '<img  src="http://54.173.118.3'.$icon.'" style="max-width:100px;max-height:100px;cursor:pointer" class="img img-thumbnail">';
                })
                ->link(function () {#
                // 这里可以构造详情页面的URL
                return admin_url('package')."/{$this->id}";
            });

            $grid->column('package_name', 'Package')->width('5%')->link(function (){
                    // 拼接 id
                    $_s = $this->package_name;
                    return 'https://play.google.com/store/apps/details?id=' . $_s;
            })->display(function ($value) {
                return "<div style='word-wrap: break-word; width: 200px;'>{$value}</div>";
            });


            $grid->column('manage_server_addresses', '管理服务器地址')->width('10%')->link(function (){
                $_s = $this->manage_server_addresses;
                if(empty($_s)){
                    return '';
                }
                return 'http://'.$_s.':3389/ymgadmin';
            });

            // $grid->column('version','版本')->width('10%');
            // $grid->column('review_time','提审时间')->width('10%')->substr(0, 10);
            // $grid->column('pass_time','通过时间')->width('10%')->substr(0, 10);
            $grid->column('updated_two_at','更新时间')->width('10%')->substr(0, 10);
            //  $item->updated_two_at =  date("Y-m-d H:i:s");
            $grid->column('takedown_time','下架时间')->width('10%')->substr(0, 10);

            $grid->column('rrr','开发者账号')->display(function(){
                return $this->account['name']??'';
            })->copyable();

            $grid->column('ttt','开发者账号类型')->display(function(){
                $type = $this->account['type']??'';
                $arr = [
                    0=>'新账号(14天过包)',
                    1=>'老账号',
                    2=>'转移号',
                    3=>'火种',
                    4=>'接受号',
                ];
                return $arr[$type]??'';
            });




            $grid->column('b_url','b面连接')->width('10%')->link(function (){
                // 拼接 id
                $_s = $this->b_url;

                #删除http前面的字符串
                $partToDelete = "http";
                $pattern = "/^.*?(?=$partToDelete)/";
                $replacement = '';
                $newString = preg_replace($pattern, $replacement, $_s);

                return $newString;
            });
            // $grid->column('remark','备注')->width('10%')->limit(50, '...');
            $grid->column('package_status','状态')->using(\App\Models\YmPackage::$status)->dot(
                [
                    0=>'primary',
                    1=>'success',
                    2=>'danger',
                ]
            );

            // $grid->column('transfer_status')->options()->radio([0=>'未操作',1 => '转移中',2 => '转移完成','3'=>'sus','4'=>'被取消',]);

            // $grid->quickSearch('package_name');
            $grid->tableCollapse(false);

            //  $grid->selector(function (Grid\Tools\Selector $selector) {
            //     $selector->select('package_status', '状态', \App\Models\YmPackage::$status);

            // });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();

                $filter->where('Package', function ($query) {
                    $query->where('package_name', 'like', "%{$this->input}%");
                })->width(3);

                $filter->equal('package_status')->select(\App\Models\YmPackage::$status)->default(1)->width(3);

                $filter->equal('account_id','开发者账号')->select(function(){
                        return \App\Models\YmAccount::where('account_type','=',0)->where('status','!=',2)->pluck('name', 'id')->toArray();
                })->width(3);

                $filter->equal('receive_account_id','开发者接收账号')->select(function(){
                    return \App\Models\YmAccount::where('account_type','=',1)->where('status','!=',2)->pluck('name', 'id')->toArray();
                })->width(3);




            });


            // $grid->actions(function (Grid\Displayers\Actions $actions) {
            //     $id = $actions->getKey();//获取该行数据id值
            //     $url =  admin_url('package').'/'.$id;//帮助函数admin_url   url地址拼接
            //     $actions->append("<a href={$url}><i class='fa fa-eye'>详细信息</i></a>");
            // });

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
        return Show::make($id, new YmPackage(), function (Show $show) {
            // $show->field('id');
            $show->field('package_name');
            $show->field('review_time');
            $show->field('pass_time');
            $show->field('takedown_time');
            $show->field('package_status');
            $show->field('b_url');
            $show->field('text_hash');
            $show->field('text_privacy');
            $show->field('manage_server_addresses');
            $show->field('domain_name');
            $show->field('type');
            $show->field('remark','remark')->unescape()->as(function ($content) {
                // 使用 nl2br() 函数将换行符转换为 HTML 的换行标签
                return nl2br($content);
            });

            // $show->field('account_id',)->display(function ($account_id){
            //     return YmAccount::where('id', $account_id)->value('name');
            // });

            $show->field('account_id', __('开发者账号'))->as(function (){
                return $this->account->name??'';
            });

            $show->field('receive_account', __('开发者接收账号'))->as(function (){
                return $this->receive_account->name??'';
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
        return Form::make(new YmPackage(), function (Form $form) {
            // $form->display('id');
            $form->text('package_name')->required();
            $form->datetime('review_time')->format('YYYY-MM-DD')->default(date("Y-m-d H:i:s"));
            $form->datetime('pass_time')->format('YYYY-MM-DD');
            $form->datetime('takedown_time')->format('YYYY-MM-DD');
            $form->text('b_url')->saving(function ($v) {
                if(empty($v)){
                    return '';
                }else{
                    return $v;
                }
            });

            $form->select('package_status')->options(\App\Models\YmPackage::$status)->default(0);

            // if ($form->model()->transfer_status == 0) {
                $form->select('account_id','开发者账号')->options(function(){
                return \App\Models\YmAccount::where('account_type','=',0)->where('status','!=',2)->pluck('name', 'id')->toArray();
                })->saving(function ($v) {
                    if(empty($v)){
                        return 0;
                    }else{
                        return $v;
                    }
                });
            // }



            $form->select('receive_account_id','开发者接收账号')->options(function(){
                return \App\Models\YmAccount::where('account_type','=',1)->where('status','!=',2)->pluck('name', 'id')->toArray();
            })->saving(function ($v) {
                if(empty($v)){
                    return 0;
                }else{
                    return $v;
                }
            });

            $form->radio('transfer_status')->options(['0' => '未操作', '1'=> '转移中','2'=>'转移完成','3'=>'sus','4'=>'被取消',])->default('0')->saving(function ($v) {
                if($v == 2){
                    $this->account_id = 0;
                }
              return $v;
            });

            $form->text('text_hash');
            $form->textarea('text_privacy');
            $form->ip('manage_server_addresses')->saving(function ($v) {
                if(empty($v)){
                    return '';
                }else{
                    return $v;
                }
            });

            $form->text('domain_name')->saving(function ($v) {
                if(empty($v)){
                    return '';
                }else{
                    return $v;
                }
            });

            $form->text('type')->saving(function ($v) {
                if(empty($v)){
                    return '';
                }else{
                    return $v;
                }
            })->default('com.unity3d.player.UnityPlayerActivity');



            $form->textarea('remark');

            $form->saving(function (Form $form) {

                // if($form->transfer_status == 2){
                //     $form->account_id = 0;
                // }

            });

            #处理日志逻辑
            $request = request();
            if ($form->model()->exists) {
                // 模型已存在，执行更新操作
                // 你的更新逻辑代码
                // 假设你是通过 PUT 方法提交表单
                if ($request->method() == 'PUT') {#更新操作
                    OperationLog::logDesc($request,'ym_package','up','package',$form->model()->id);
                }

            } else {
                // 模型不存在，执行插入操作
                // 你的插入逻辑代码
                if ($request->method() == 'POST') {#插入操作

                    #新建包时，检测开发者账号状态是否为重置key完成，如果是 设置为正常状态
                    $account_id = $request->post('account_id',0);
                    if($account_id){
                        $status = YmPackage::query()->where('id',$account_id)->value('status');
                        if($status == 5){
                            YmPackage::query()->where('id',$account_id)->value(['status'=>1]);
                        }
                    }

                    OperationLog::logDesc($request,'ym_package','in','package');
                }

            }



        });
    }
}
