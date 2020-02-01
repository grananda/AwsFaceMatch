<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaceMatchEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('face_match_entities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->nullable();
            $table->uuid('collection_id')->nullable(false);
            $table->string('face_id')->nullable(false);
            $table->string('entity_ref')->nullable(false);
            $table->string('image_id')->nullable(false);
            $table->timestamps();
        });

        Schema::table('face_match_entities', function (Blueprint $table) {
            $table->foreign('collection_id')->references('id')->on('face_match_collections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('face_match_entities');
    }
}
