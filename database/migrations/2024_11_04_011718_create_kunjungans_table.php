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
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('no_nasabah');
            $table->datetime('tanggal');
            $table->string('koordinat');
            $table->string('keterangan');
            $table->string('bukti_gambar');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('no_nasabah')->references('no')->on('nasabahs')->onDelete('cascade');
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
        Schema::dropIfExists('kunjungan');
    }
};