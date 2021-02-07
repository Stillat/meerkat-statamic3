<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeerkatTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meerkat_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('system_id');
            $table->string('task_code');
            $table->integer('task_status');
            $table->string('task_name');
            $table->dateTime('completed_on')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->boolean('was_canceled')->default(false);
            $table->json('task_args');
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
        Schema::dropIfExists('meerkat_tasks');
    }
}
