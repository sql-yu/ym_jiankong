<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYmPackageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ym_package', function (Blueprint $table) {
            $table->increments('id');
            $table->string('package_name')->nullable()->comment('包id');
            $table->dateTime('review_time')->nullable()->comment('提审时间');
            $table->dateTime('pass_time')->nullable()->comment('通过时间');
            $table->decimal('takedown_time')->nullable()->comment('下架时间');
            $table->integer('package_status')->default('0')->comment('状态值');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ym_package');
    }
}
