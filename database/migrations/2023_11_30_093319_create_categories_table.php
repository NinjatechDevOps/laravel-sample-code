<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('slug', 255)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('product_counts')->nullable();
            $table->longText('description')->nullable();
            $table->text('long_description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->string('image', 100)->nullable();
            $table->string('icon_name', 100)->nullable();
            $table->unsignedBigInteger('import_batch_id')->nullable()->index('categories_import_batch_id_foreign');
            $table->bigInteger('no_of_products')->default(0);
            $table->unsignedBigInteger('created_by')->nullable()->index('categories_created_by_foreign');
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
        Schema::dropIfExists('categories');
    }
};
