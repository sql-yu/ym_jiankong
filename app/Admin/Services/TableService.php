<?php
namespace App\Admin\Services;

use Dcat\Admin\Admin;

class TableService
{
    // 双击表格行打开打开菜单
    public function enableDoubleClickMenu()
    {
        $script = <<<JS
        // 监听每行的双击事件，如果该行有编辑按钮，就主动触发其点击
        $("#grid-table > tbody > tr").on("dblclick",function() {
        // 这里通过2种方式查找编辑按钮，如果禁用了默认的编辑按钮，只要你自定义的编辑按钮的class带有icon-edit也能触发，这个是编辑图标的css类
           var obj = $(this).find(".grid__actions__ [class*=icon-edit]");
           var obj2 = $(this).find(".grid__actions__ [action=edit]");
           // console.log(obj,obj2)
           if (obj.length === 1) {
               obj.trigger("click")
           }else if(obj2.length===1){
               obj2.trigger("click");
           }
        })
JS;
        Admin::script($script);
        return $this;
    }
}
