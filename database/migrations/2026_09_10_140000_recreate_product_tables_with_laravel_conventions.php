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
        if (Schema::hasTable('products')) {
            return;
        }

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->char('barcode', 13);
            $table->timestamps();
        });

        Schema::create('leveranciers', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->string('contact_persoon');
            $table->string('leverancier_nummer', 20);
            $table->string('mobiel', 20);
            $table->timestamps();
        });

        Schema::create('product_per_leveranciers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leverancier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->date('datum_levering');
            $table->unsignedInteger('aantal');
            $table->date('datum_eerst_volgende_levering')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_per_leveranciers');
        Schema::dropIfExists('leveranciers');
        Schema::dropIfExists('products');
    }
};
