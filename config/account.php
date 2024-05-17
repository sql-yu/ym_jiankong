<?php

return [
    'type'=>[
        0=>'新账号(14天过包)',
        1=>'老账号',
        2=>'转移号',
        3=>'火种',
        4=>'接受号',
    ],
    'account_status'=>[
        1 => '正常',
        2 => '封号',
        3 => '验证',
        4 => '申诉中',    #'重置key',       key_status 2
//        5 => '重置key完成',               key_status 3
    ],
    'key_status' => [
        1=>'正常',
        2=>'重置中',
        3=>'重置完成',
    ],
    'transfer_status'=>[
        1=>'正常',
        2=>'需转移',
        3=>'转移中',
        4=>'转移完成',
    ],
];
