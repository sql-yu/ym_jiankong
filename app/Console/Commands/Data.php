<?php

namespace App\Console\Commands;

use App\Models\YmPackage;
use Illuminate\Console\Command;
use QL\QueryList;
use Jaeger\GHttp;//引入自带的库

class Data extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pa:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = "https://play.google.com/store/apps/details?id=";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $package = YmPackage::where(['package_status'=>[0]])->get();
        $zhengshi_package = YmPackage::where(['package_status'=>[1]])->orderBy('review_time','desc')->get();
        
        
        echo "审核状态检查中:\n";
        foreach($package as $item){
            if(!$item->package_name){
                break;
            }
            $all_url = $url . $item->package_name."&hl=en&gl=US";
            echo $all_url . "\n";
            try {
                $numm = rand(100, 999);
                $res = $client->request('GET', $all_url, [
                    'headers' => [
                        'User-Agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/{$numm}.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/{$numm}.36",
                        'Accept-Encoding' => 'gzip, deflate, br',
                    ]
                ]);
                $html = (string)$res->getBody();
                $data = QueryList::html($html)->find('div .Il7kR>img')->attrs('src')->toArray();
      
               
                if(!empty($data)){
                    $this->send($all_url,"初版上线\n{$item->package_name}\ncom.unity3d.player.UnityPlayerActivity\n{$item->remark}");
                    $item->package_status = 1;
                    $item->pass_time = date("Y-m-d H:i:s");
                    $item->updated_two_at =  date("Y-m-d H:i:s");
                    $item->version = $this->getVersion($html);
                    $item->icon = $this->setIcon($client,$data);
                    $item->save();
                }
            }catch (\Exception $e){
              // echo $e->getMessage();

            }
            $timeeee  = rand(5,15);
            sleep($timeeee);
        }

       

        foreach($zhengshi_package as $item){
            if(!$item->package_name){
                break;
            }
            $all_url =  $url.$item->package_name."&hl=en&gl=US";
            echo "正式状态复查中:\n";
            echo $all_url . "\n";
            try {
                $res = $client->request('GET', $all_url, [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
                        'Accept-Encoding' => 'gzip, deflate, br',
                    ]
                ]);
                $html = (string)$res->getBody();
                $data = QueryList::html($html)->find('div .Il7kR>img')->attrs('src')->toArray();
                if(!empty($data)){
                    $version = $this->getVersion($html);
                    echo $item->version . "\n";
                    echo $version. "\n";
                    if($item->version == ""){
                        $item->version = $version;
                    }else{
                        if($item->version != $version){
                            $item->version = $version;
                            $item->icon = $this->setIcon($client,$data);
                            $item->updated_two_at =  date("Y-m-d H:i:s");
                            $this->send($all_url,"更新上线，新版本{$version},更新成功!\n{$item->package_name}\ncom.unity3d.player.UnityPlayerActivity\n{$item->remark}");
                        }
                    }
                    if($item->icon == ""){
                         $item->icon = $this->setIcon($client,$data);
                    }
                  
                    $item->save();
                }
            }catch (\Exception $e){
                // echo $e->getMessage();
                $this->send($all_url,"！！！ 下线 ！！！\n{$item->package_name}\n{$item->remark}");
                $item->package_status = 2;
                $item->takedown_time = date("Y-m-d");
                $item->save();
            }

            $timeeee  = rand(5,15);
            sleep($timeeee);
        }
    }

    private function send($url,$message){
        try{
            $client = new \GuzzleHttp\Client(['verify'=>false]);
            $message = "{$url}\n{$message}\n";
            $response=$client->post("https://api.telegram.org/bot6259441275:AAHgZzm6UyJYHjWQM0SmPu_k26Agmi-LoIQ/sendMessage",[
                    'form_params'=>[
                    'chat_id'=>"-941996643",
                    // 'chat_id'=>"5317659555",
                    'text'=>$message,
                ]
            ]);

            $body = $response->getBody(); //获取响应体，对象
            $bodyStr = (string)$body; //对象转字串
            $this->sendJishu($url,$message);
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }
    
    private function sendJishu($url,$message){
        try{
            $client = new \GuzzleHttp\Client(['verify'=>false]);
            $message = "{$message}";
            $response=$client->post("https://api.telegram.org/bot6259441275:AAHgZzm6UyJYHjWQM0SmPu_k26Agmi-LoIQ/sendMessage",[
                    'form_params'=>[
                    'chat_id'=>"-933333595",
                    // 'chat_id'=>"5317659555",
                    'text'=>$message,
                ]
            ]);

            $body = $response->getBody(); //获取响应体，对象
            $bodyStr = (string)$body; //对象转字串
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }
    
    private function getVersion($html){
        try{
            $pattern = '/AF_initDataCallback\(\{key:\s*\'ds:5\'.*?\}\)/s';
            if (preg_match_all($pattern, $html, $matches)) {
                $extractedData = $matches[0]??''; // 提取匹配到的内容
                if($extractedData != ''){
                    $indexData = strpos($extractedData[0],'data:');
                    $extractedData = substr($extractedData[0],$indexData+5);
                    $searchIndex = strpos($extractedData,'sideChannel:');
    
                    $extractedData = substr($extractedData,0,-(strlen($extractedData)-$searchIndex+2));
      
                    $versionData = json_decode($extractedData,true);
                    $varsion1 = $versionData[1][2];
                    $varsion2 = $varsion1[count($varsion1)-15];
                    $varsion3 = $varsion2[0][0][0];
                    return $varsion3;
                }
            }
            return "";
        }catch(\Exception $e){
            echo $e->getMessage();
            return "";
        }
    }
    
    private function setIcon($client,$data){
        if(isset($data[0])){
            $imageUrl = $data[0];
            $name = "/icon/" .md5($imageUrl). '.webp';
            $path =  base_path() . "/public".$name;
            GHttp::download($imageUrl,$path);
            return $name;
        }
        return "";
    }
    
    
}
