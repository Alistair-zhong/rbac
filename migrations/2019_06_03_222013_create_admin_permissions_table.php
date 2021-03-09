<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_permissions', function (Blueprint $table) {
            // $table->increments('id');
            $table->string('name', 50)->default('')->comment('名称');
            $table->string('slug', 50)->default('')->comment('标识');
            $table->string('http_method')->nullable()->default('')->comment('请求方法');
            $table->text('http_path')->nullable()->comment('请求地址');
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
        Schema::dropIfExists('admin_permissions');
    }
}
