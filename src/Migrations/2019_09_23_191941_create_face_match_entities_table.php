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
            $table->string('collection_arn')->nullable(false);
            $table->string('collection_id')->nullable(false);
            $table->string('face_id')->nullable(false);
            $table->string('entity')->nullable(false);
            $table->string('entity_ref')->nullable(false);
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
        Schema::dropIfExists('face_match_entities');
    }
}
