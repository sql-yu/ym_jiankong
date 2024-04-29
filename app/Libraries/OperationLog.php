<?php

namespace App\Libraries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Dcat\Admin\Admin;

use App\Models\YmOperationLog;

#自定义操作记录类
class OperationLog
{

    /**
    * @param  $request  传的值
    * @param  $table    表名称
    * @param  $type  in插入 up更新 de删除
    * @param  $url 操作菜单
    * @param  $id id
    *    OperationLog::logDesc();
    */
    public static function logDesc($request, $table,$type,$url,$id=0) {
        $fieldName = Schema::getColumnListing($table);   // 获取对应表的字段名
        
        $content = [];
        if($type == 'up'){#更新
            $input = $request->only($fieldName);

            // 查找对应表中数据
            $field = array_keys($input);    // 获取键，查找字段
            $data = DB::table($table)->where('id', $id)->get($field)->toArray();
            $data = json_decode(json_encode($data[0]),true);

            // var_dump($input,$data);exit;
            
            foreach($input as $input_k =>$input_v){
                if($input_v != $data[$input_k]){
                    $content[$input_k] = [$data[$input_k],$input_v];
                }
            }
        }elseif($type == 'in'){#插入
            $input = $request->only($fieldName);
            foreach($input as $k=>$v){
                if($v == NULL){
                    unset($input[$k]);
                }
            }
            $content = $input;
        }else{#删除
            $data = DB::table($table)->where('id', $id)->get('*')->toArray();
            $data = json_decode(json_encode($data[0]),true);
            foreach($data as $k=>$v){
                if($v == NULL){
                    unset($data[$k]);
                }
            }
            $content = $data;
        }

        $content = json_encode($content,JSON_UNESCAPED_UNICODE);

        YmOperationLog::insert([
            'user_id'=>Admin::user()->id,
            'url'=>$url,
            'type'=>$type,
            'edit_id'=>$id,
            'content'=>$content,
            'created_at'=>date('Y-m-d H:i:s'),
        ]);

        
        // var_dump($content);exit;
        // return $content;
    }

}