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
            //$table->engine = "MyISAM";
            $table->increments('id');
            $table->text('title');
            $table->longText('description')->nullable();
            $table->longText('content');
            $table->longText('refer')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->integer('pub_id')->unsigned();
            $table->foreign('pub_id')->references('id')->on('publishers');
            $table->text('from');
        });

        DB::statement('ALTER TABLE news ADD FULLTEXT title(title) WITH PARSER ngram');
        DB::statement('ALTER TABLE news ADD FULLTEXT content(content) WITH PARSER ngram');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news', function($table)
        {
            $table->dropForeign('news_pub_id_foreign'); // Drop foreign key 'user_id' from 'posts' table
            $table->dropIndex('title');
            $table->dropIndex('content');
        });
        Schema::drop('news');
    }
}
