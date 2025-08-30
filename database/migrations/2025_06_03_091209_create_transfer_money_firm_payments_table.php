<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferMoneyFirmPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('transfer_money_firm_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transactions_id');
            $table->decimal('amount', 20, 2);
            $table->unsignedInteger('currency_id');
            $table->decimal('rate_at_time', 20, 2);
            $table->string('note', 191)->nullable();
            $table->timestamps();
            $table->foreign('transactions_id')
            ->references('id')->on('transactions')
            ->onDelete('cascade');
             $table->foreign('currency_id')
            ->references('id')->on('currencies')
            ->onDelete('cascade');
              });
    }

    public function down()
    {
        Schema::dropIfExists('transfer_money_firm_payments');
    }
}
