<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\YmAccount;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

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
            $grid->model()->where('account_type','=',0);

            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('login_ip');
            $grid->column('login_username');
            $grid->column('login_password');
            $grid->column('type');
            $grid->column('status')->options()->radio([
                1 => '正常',
                2 => '封号',
            ]);
            // $grid->column('num_sus');
            $grid->column('phone_no');
            $grid->column('phone_number');
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                
                $filter->where('别名/ip', function ($query) {

                    $query->where('name', 'like', "%{$this->input}%")
                        ->orWhere('login_ip', 'like', "%{$this->input}%");
                
                });
        
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
            $show->field('type');
            $show->field('status');

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
            $form->text('type');
            $form->text('status')->saving(function ($v) {
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

        });
    }
}
