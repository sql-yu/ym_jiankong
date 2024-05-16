<?php

namespace App\Admin\Renderable;

use App\Admin\Repositories\YmAccount;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use App\Models\YmAccount as M_YmAccount;

class AccountInfoTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new M_YmAccount(), function (Grid $grid) {
            $id = $this->payload['id'];
            $grid->model()->where('id','=',$id);


//            $grid->column('id', '用户ID')->sortable();
            $grid->column('name','账号别名')->copyable();
            $grid->column('login_ip','IP')->copyable();
            $grid->column('login_username','vps-账号')->copyable();
            $grid->column('login_password','vps-密码')->copyable();

            $grid->disableActions();
            $grid->disableRowSelector(); // 禁用行选择器

        });
    }
}
