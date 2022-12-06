<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_reviews', function (Blueprint $table) {
            //create attr
            $table->id();
            $table->text('comment');
            $table->boolean('edited');

            //create attr
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('user_id');


            //create foreing key

        });

        Schema::table('book_reviews', function(Blueprint $table){

            $table->foreign('book_id')->references('id')->on('books');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_reviews');
    }
}
