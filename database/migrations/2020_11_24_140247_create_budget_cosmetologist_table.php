<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetCosmetologistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budget_cosmetologist', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('budget_id');
            $table->unsignedInteger('cosmetologist_id');

            $table->timestamps();

            $table->index('budget_id');
            $table->index('cosmetologist_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('budget_cosmetologist');
    }
}
