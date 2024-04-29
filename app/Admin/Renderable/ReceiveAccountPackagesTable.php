<?php

namespace App\Admin\Renderable;

use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use App\Models\YmPackage;

class ReceiveAccountPackagesTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new YmPackage(), function (Grid $grid) {
            $id = $this->payload['id'];
            $grid->model()->where('receive_account_id','=',$id);
            $grid->model()->where('transfer_status','=',2);#只查询转移完成的


            $grid->column('id', 'ID')->sortable();
            $grid->column('package_name','包名');

            $grid->paginate(10);
            $grid->disableActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('package_name')->width(4);
            });
        });
    }
}
