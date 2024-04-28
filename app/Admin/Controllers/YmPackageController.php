<?php

namespace App\Admin\Controllers;

use App\Models\YmPackage;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
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
 
        return Grid::make(YmPackage::orderBy("review_time",'desc'), function (Grid $grid) {
            // $grid->column('id')->hidden();
            $grid->column('icon','icon')->image("http://54.173.118.3",100,100);
            $grid->column('package_name','Package')->width('5%')->copyable();
            $grid->column('version','版本')->width('10%');
            $grid->column('review_time','提审时间')->width('10%')->substr(0, 10);
            $grid->column('pass_time','通过时间')->width('10%')->substr(0, 10);
            $grid->column('updated_two_at','更新时间')->width('10%')->substr(0, 10);
            //  $item->updated_two_at =  date("Y-m-d H:i:s");
            $grid->column('takedown_time','下架时间')->width('10%')->substr(0, 10);
            $grid->column('b_url','b面连接')->width('10%');
            $grid->column('remark','备注')->width('10%')->limit(50, '...');
            $grid->column('package_status','状态')->using(\App\Models\YmPackage::$status)->dot(
                [
                    0=>'primary',
                    1=>'success',
                    2=>'danger',
                ]
            );

            // $grid->column('transfer_status')->options()->radio([0=>'未操作',1 => '转移中',2 => '转移完成',]);

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

                $filter->equal('package_status')->select(\App\Models\YmPackage::$status)->width(3);

                $filter->equal('account_id','开发者账号')->select(function(){
                        return \App\Models\YmAccount::where('account_type','=',0)->pluck('name', 'id')->toArray();
                })->width(3);

                $filter->equal('receive_account_id','开发者接收账号')->select(function(){
                    return \App\Models\YmAccount::where('account_type','=',1)->pluck('name', 'id')->toArray();
                })->width(3);



        
            });
        
            $grid->toolsWithOutline(false);
            $grid->disableBatchDelete();
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

            if ($form->model()->transfer_status == 0) {
                $form->select('account_id','开发者账号')->options(function(){
                return \App\Models\YmAccount::where('account_type','=',0)->pluck('name', 'id')->toArray();
                })->saving(function ($v) {
                    if(empty($v)){
                        return 0;
                    }else{
                        return $v;
                    }
                });
            }

            

            $form->select('receive_account_id','开发者接收账号')->options(function(){
                return \App\Models\YmAccount::where('account_type','=',1)->pluck('name', 'id')->toArray();
            })->saving(function ($v) {
                if(empty($v)){
                    return 0;
                }else{
                    return $v;
                }
            });

            $form->radio('transfer_status')->options(['0' => '未操作', '1'=> '转移中','2'=>'转移完成'])->default('0')->saving(function ($v) {
                if($v == 2){
                    $this->account_id = 0;
                }
              return $v;
            });


            $form->textarea('remark');

            $form->saving(function (Form $form) {
                
                if($form->transfer_status == 2){
                    $form->account_id = 0;
                }

            });

        });
    }
}
