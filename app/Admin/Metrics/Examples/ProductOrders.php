<?php

namespace App\Admin\Metrics\Examples;

use App\Models\YmPackage;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Metrics\Round;
use Illuminate\Http\Request;

class ProductOrders extends Round
{
    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->title('统计');
        $this->chartLabels(['正式', '审核中', '下架']);
        $this->dropdown([
            '0' => 'All',
            '30' => 'This Month',
            '31' => 'Last Month',
        ]);
    }

    /**
     * 处理请求
     *
     * @param Request $request
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        $a0 = 0;
        $a1 = 0;
        $a2 = 0;
        $a3 = 0;
        switch ($request->get('option')) {
            case '30':
                $time = \Carbon\Carbon::now()->startOfMonth()->toDateTimeString();;
                $a0 = YmPackage::where(['package_status'=>1])->where('review_time','>=',$time)->count();
                
                $a1 = YmPackage::where(['package_status'=>0])->where('review_time','>=',$time)->count();
                $a2 = YmPackage::where(['package_status'=>2])->where('review_time','>=',$time)->count();
                $a3 = YmPackage::where('pass_time','!=','null')->where('review_time','>=',$time)->count();
                break;
            case '31':
                $firstOfMonth = (new Carbon('first day of last month'))->startOfMonth()->toDateTimeString();
                $endOfMonth = (new Carbon('first day of last month'))->endOfMonth()->toDateTimeString();
                $a0 = YmPackage::where(['package_status'=>1])->whereBetween('review_time',[$firstOfMonth,$endOfMonth])->count();
                $a1 = YmPackage::where(['package_status'=>0])->whereBetween('review_time',[$firstOfMonth,$endOfMonth])->count();
                $a2 = YmPackage::where(['package_status'=>2])->whereBetween('review_time',[$firstOfMonth,$endOfMonth])->count();
                $a3 = YmPackage::where(['pass_time','!=','null'])->whereBetween('review_time',[$firstOfMonth,$endOfMonth])->count();
                break;
            default:
                $a0 = YmPackage::where(['package_status'=>1])->count();
                $a1 = YmPackage::where(['package_status'=>0])->count();
                $a2 = YmPackage::where(['package_status'=>2])->count();
                $a3 = YmPackage::where('pass_time','!=','null')->count();
                break;
        }
        // 卡片内容
        $this->withContent($a0, $a1, $a2,$a3);

        // 图表数据
        $this->withChart([$a0,$a1,$a2]);

        // 总数
        $this->chartTotal('Total', $a0+$a1+$a2);
    }

    /**
     * 设置图表数据.
     *
     * @param array $data
     *
     * @return $this
     */
    public function withChart(array $data)
    {
        return $this->chart([
            'series' => $data,
        ]);
    }

    /**
     * 卡片内容.
     *
     * @param int $finished
     * @param int $pending
     * @param int $rejected
     *
     * @return $this
     */
    public function withContent($finished, $pending, $rejected,$go)
    {
        return $this->content(
            <<<HTML
<div class="col-12 d-flex flex-column flex-wrap text-center" style="max-width: 220px">
    <div class="chart-info d-flex justify-content-between mb-1 mt-2" >
          <div class="series-info d-flex align-items-center">
              <i class="fa fa-circle-o text-bold-700 text-primary"></i>
              <span class="text-bold-600 ml-50">正式</span>
          </div>
          <div class="product-result">
              <span>{$finished}</span>
          </div>
    </div>
    
    <div class="chart-info d-flex justify-content-between mb-1" >
          <div class="series-info d-flex align-items-center">
              <i class="fa fa-circle-o text-bold-700 text-primary"></i>
              <span class="text-bold-600 ml-50">通过</span>
          </div>
          <div class="product-result">
              <span>{$go}</span>
          </div>
    </div>

    <div class="chart-info d-flex justify-content-between mb-1">
          <div class="series-info d-flex align-items-center">
              <i class="fa fa-circle-o text-bold-700 text-warning"></i>
              <span class="text-bold-600 ml-50">审核中</span>
          </div>
          <div class="product-result">
              <span>{$pending}</span>
          </div>
    </div>

     <div class="chart-info d-flex justify-content-between mb-1">
          <div class="series-info d-flex align-items-center">
              <i class="fa fa-circle-o text-bold-700 text-danger"></i>
              <span class="text-bold-600 ml-50">下架</span>
          </div>
          <div class="product-result">
              <span>{$rejected}</span>
          </div>
    </div>
</div>
HTML
        );
    }
}
