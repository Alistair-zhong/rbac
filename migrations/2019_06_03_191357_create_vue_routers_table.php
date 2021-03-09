<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVueRoutersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vue_routers', function (Blueprint $table) {
            // $table->increments('id');
            $table->unsignedInteger('parent_id')->default(0);
            $table->string('title', 50)->default('');
            $table->string('path', 50)->default('')->nullable();
            $table->integer('order')->default(0);
            $table->string('icon', 50)->default('')->nullable();
            $table->tinyInteger('menu')->default(false);
            $table->tinyInteger('cache')->default(false);
            $table->datetime('created_at')->nullable()->comment('创建时间');
            $table->datetime('updated_at')->nullable()->comment('修改时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vue_routers');
    }
}
