<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelocationBaFieldsToMCasesTable extends Migration
{
    public function up()
    {
        Schema::table('m_cases', function (Blueprint $table) {
            $table->string('ba_number', 100)->nullable();
            $table->date('ba_date')->nullable();
            $table->string('ba_origin_location')->nullable();
            $table->string('ba_destination_location')->nullable();
            $table->string('ba_customer_pic_name')->nullable();
            $table->string('ba_customer_pic_title')->nullable();
            $table->string('ba_executor_pic_name')->nullable();
            $table->string('ba_executor_pic_title')->nullable();
        });
    }

    public function down()
    {
        Schema::table('m_cases', function (Blueprint $table) {
            $table->dropColumn([
                'ba_number',
                'ba_date',
                'ba_origin_location',
                'ba_destination_location',
                'ba_customer_pic_name',
                'ba_customer_pic_title',
                'ba_executor_pic_name',
                'ba_executor_pic_title',
            ]);
        });
    }
}
