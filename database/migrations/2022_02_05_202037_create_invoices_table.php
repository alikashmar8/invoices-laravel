<?php

use App\Enums\CurrencyEnum;
use App\Enums\DiscountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->longText('title')->default('Invoice');
            $table->float('total')->default(0);
            $table->float('gst')->default(0)->nullable(); 
            $table->float('discount')->default(0)->nullable(); 
            $table->enum('discount_type', DiscountType::getValues())->default(DiscountType::AMOUNT);
            $table->enum('currency', CurrencyEnum::getValues())->default(CurrencyEnum::AUD);
            $table->longText('reference_number')->nullable()->unique();
            $table->boolean('is_paid')->default(true);
            // $table->boolean('incoming')->default(true);
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
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
        Schema::dropIfExists('invoices');
    }
}
