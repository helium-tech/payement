<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('transaction_id')->unique();
            $table->integer('amount');
            $table->string('currency');
            $table->string('type');
            $table->json('data')->nullable();
            $table->string('payement_token')->nullable();
            $table->string('status')->default("PROGRESS");
            $table->string('description');
            $table->string('plateforme');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
