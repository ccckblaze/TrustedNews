<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->longText('description')->nullable();
            $table->longText('content');
            $table->longText('refer')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->integer('pub_id')->unsigned();;
            $table->text('from');
            $table->foreign('pub_id')->references('id')->on('publishers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('news');
    }
}
