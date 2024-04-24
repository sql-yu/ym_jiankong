<?php

namespace App\Admin\Controllers;

use App\Models\YmPackage;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

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
            $grid->column('package_name','Package')->width('15%')->copyable();
            $grid->column('version','版本')->width('10%');
            $grid->column('review_time','提审时间')->width('10%')->substr(0, 10);
            $grid->column('pass_time','通过时间')->width('10%')->substr(0, 10);
            $grid->column('updated_two_at','更新时间')->width('10%')->substr(0, 10);
            //  $item->updated_two_at =  date("Y-m-d H:i:s");
            $grid->column('takedown_time','下架时间')->width('10%')->substr(0, 10);
            $grid->column('package_status','状态')->using(\App\Models\YmPackage::$status)->dot(
                [
                    0=>'primary',
                    1=>'success',
                    2=>'danger',
                ]
            );

            $grid->quickSearch('package_name');
            $grid->tableCollapse(false);
            
             $grid->selector(function (Grid\Tools\Selector $selector) {
                $selector->select('package_status', '状态', \App\Models\YmPackage::$status);

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
         
            $form->select('package_status')->options(\App\Models\YmPackage::$status)->default(0);
            $form->textarea('remark');

        });
    }
}
