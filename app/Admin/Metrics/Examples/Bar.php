<?php

namespace App\Admin\Metrics\Examples;

use Admin;
use App\Admin\Repositories\YmPackage;
use Carbon\Carbon;
use Dcat\Admin\Widgets\ApexCharts\Chart;

class Bar extends Chart
{
    public function __construct($containerSelector = null, $options = [])
    {
        parent::__construct($containerSelector, $options);

        $this->setUpOptions();
    }

    /**
     * 初始化图表配置
     */
    protected function setUpOptions()
    {
        $color = Admin::color();

        $colors = [$color->primary(), $color->danger()];

        $this->options([
            'colors' => $colors,
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                 'stacked' => true
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'dataLabels' => [
                        'position' => 'top',
                    ],
                ]
            ],
            'dataLabels' => [
                'enabled' => true,
                'offsetX' => -6,
                'style' => [
                    'fontSize' => '12px',
                    'colors' => ['#fff']
                ]
            ],
            'stroke' => [
                'show' => true,
                'width' => 1,
            ],
            'xaxis' => [
                'categories' => [],
            ],
        ]);
    }

    /**
     * 处理图表数据
     */
    protected function buildData()
    {

        $categories = [];
        $tishen = [];
        $xiajia = [];
        for ($i=0;$i<7;$i++){
            $time = Carbon::today();
            $day = $time->subDay($i)->toDateString();
            $categories[] = $day;
            $tishen[] = \App\Models\YmPackage::where(['review_time'=>$day])->count();
            $xiajia[] = \App\Models\YmPackage::where(['takedown_time'=>$day])->count();
        }
        // 执行你的数据查询逻辑
        $data = [
            [
                'data' => $tishen,
                'name' => '提审'
            ],
            [
                'data' => $xiajia,
                'name' => '下架',
            ]
        ];
        $this->withData($data);
        $this->withCategories($categories);
        $this->withLabels([]);
    }

    /**
     * 设置图表数据
     *
     * @param array $data
     *
     * @return $this
     */
    public function withData(array $data)
    {
        return $this->option('series', $data);
    }

    /**
     * 设置图表类别.
     *
     * @param array $data
     *
     * @return $this
     */
    public function withCategories(array $data)
    {
        return $this->option('xaxis.categories', $data);
//        return $this->option('xaxis.numeric', $data);
    }

    public function withLabels(array $data){
        return $this->option('labels', ["Apple", "Mango"]);
    }

    /**
     * 渲染图表
     *
     * @return string
     */
    public function render()
    {
        $this->buildData();

        return parent::render();
    }
}

