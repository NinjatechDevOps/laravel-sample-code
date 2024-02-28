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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('part_number', 100)->unique();
            $table->unsignedBigInteger('category_id')->nullable()->index('products_category_id_foreign');
            $table->unsignedBigInteger('subcategory_id')->nullable()->index('products_subcategory_id_foreign');
            $table->unsignedBigInteger('subsubcategory_id')->nullable()->index('products_subsubcategory_id_foreign');
            $table->unsignedBigInteger('manufacturer_id')->nullable()->index('products_manufacturer_id_foreign');
            $table->text('tags')->nullable();
            $table->decimal('price_per_quantity')->nullable();
            $table->text('description')->nullable();
            $table->string('short_description', 255)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('datasheet', 255)->nullable();
            $table->string('datasheet_url', 100)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('image_url', 100)->nullable();
            $table->unsignedBigInteger('import_batch_id')->nullable()->index('products_import_batch_id_foreign');
            $table->unsignedBigInteger('updated_import_batch_id')->nullable()->index('products_updated_import_batch_id_foreign');
            $table->integer('row_number')->nullable();
            $table->boolean('is_payable')->default(false);
            $table->unsignedBigInteger('created_by')->nullable()->index('products_created_by_foreign');
            $table->softDeletes();
            $table->timestamps();
            $table->integer('extracted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
