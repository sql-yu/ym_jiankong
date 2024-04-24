<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\BarChat;
use App\Admin\Metrics\Examples;
use App\Admin\Metrics\Examples\Bar;
use App\Http\Controllers\Controller;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('Dashboard')
            ->description('Description...')
            ->body(function (Row $row) {
                // $row->column(6, function (Column $column) {
                //     $column->row(Dashboard::title());
                //     $column->row(new Examples\Tickets());
                // });

                $row->column(6, function (Column $column) {
                    // $column->row(function (Row $row) {
                    //     $row->column(6, new Examples\NewUsers());
                    //     $row->column(6, new Examples\NewDevices());
                    // });

//                    $column->row(new Examples\Sessions());
                    $column->row(new Bar());
                    $column->row(new Examples\ProductOrders());
                });
            });
    }
}
