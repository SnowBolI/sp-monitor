<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        {
            Schema::create('surat_peringatans', function (Blueprint $table) {
                $table->id('id_peringatan');
                $table->unsignedBigInteger('no');
                $table->string('kategori');
                $table->integer('tingkat');
                $table->datetime('dibuat')->nullable();
                $table->datetime('kembali')->nullable();
                $table->datetime('diserahkan')->nullable();
                $table->string('bukti_gambar')->nullable();
                $table->string('scan_pdf')->nullable();
                $table->unsignedBigInteger('id_account_officer');
                $table->timestamps();

                $table->foreign('no')->references('no')->on('nasabahs')->onDelete('cascade');
                $table->foreign('id_account_officer')->references('id')->on('users')->onDelete('cascade');
            });
        }  
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_peringatans');
    }
};
