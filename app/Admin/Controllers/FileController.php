<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Traits\HasUploadedFile;

class FileController
{
    use HasUploadedFile;

    #允许上传的文件类型
    public  $allowable_file_ytpe = ['jks','keystore'];

    public function handle()
    {
        $disk = $this->disk();

        // 判断是否是删除文件请求
        if ($this->isDeleteRequest()) {
            // 删除文件并响应
            return $this->deleteFileAndResponse($disk);
        }

        // 获取上传的文件
        $file = $this->file();
//        echo $file->getFilename();echo 1;exit;

        // 获取上传的字段名称
        $column = $this->uploader()->upload_column;

        if(!in_array($file->getClientOriginalExtension(),$this->allowable_file_ytpe)){
            return $this->responseErrorMessage('上传文件类型非法');
        }

        $dir = date('Ymd');
        $newName = $this->generateRandomString(24).'.'.$file->getClientOriginalExtension();

        $result = $disk->putFileAs($dir, $file, $newName);

        $path = "{$dir}/$newName";
//        var_dump($result);#20240510/mc6cija9qum5td07xgop4tij.exe

        return $result
            ? $this->responseUploaded($path, $disk->url($path))
            : $this->responseErrorMessage('文件上传失败');
    }


    private function generateRandomString($length) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }


}
