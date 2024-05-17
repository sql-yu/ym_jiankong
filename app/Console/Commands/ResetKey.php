<?php
namespace App\Console\Commands;

use App\Models\YmAccount;
use Illuminate\Console\Command;
use QL\QueryList;
use Jaeger\GHttp;//引入自带的库

#检测账号状态，从重置key 到 重置key完成的状态
#    /usr/bin/php /www/wwwroot/54.173.118.3/jiankong/artisan pa:reset_key
class ResetKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pa:reset_key';

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

    public function handle()
    {
        $data = YmAccount::query()->where('key_status',2)->get(['id','key_complete_time','status'])->toArray();

        if($data){
            foreach($data as $v){
                if((time() - $v['key_complete_time']) > (2*24*60*60)){#大于两天
                    YmAccount::query()->where('id',$v['id'])->update(['key_status'=>3]);
                }
            }

        }
        echo 'ok';

    }


}
