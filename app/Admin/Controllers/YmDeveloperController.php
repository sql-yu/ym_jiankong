<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\YmDeveloper;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class YmDeveloperController extends AdminController
{
    protected $title = "开发人员列表";
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new YmDeveloper(), function (Grid $grid) {
           
            $grid->column('name');
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
        return Show::make($id, new YmDeveloper(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new YmDeveloper(), function (Form $form) {
            $form->display('id');
            $form->text('name');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
