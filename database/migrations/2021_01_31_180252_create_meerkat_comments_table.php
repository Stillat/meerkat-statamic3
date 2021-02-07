<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeerkatCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meerkat_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('parent_compatibility_id')->nullable();
            $table->bigInteger('compatibility_id');
            $table->uuid('statamic_user_id')->nullable();
            $table->uuid('thread_context_id');
            $table->longText('virtual_path');
            $table->longText('virtual_dir_path');
            $table->longText('root_path');
            $table->integer('depth');
            $table->boolean('is_root');
            $table->boolean('is_published');
            $table->boolean('is_spam')->nullable();
            $table->longText('content');
            $table->json('comment_attributes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meerkat_comments');
    }
}
