<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('Product')) {
            Schema::create('Product', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('Id');
                $table->string('Naam');
                $table->char('Barcode', 13);
            });
        }

        if (! Schema::hasTable('Allergeen')) {
            Schema::create('Allergeen', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('Id');
                $table->string('Naam');
                $table->string('Omschrijving');
            });
        }

        if (! Schema::hasTable('Leverancier')) {
            Schema::create('Leverancier', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('Id');
                $table->string('Naam');
                $table->string('ContactPersoon');
                $table->string('LeverancierNummer', 20);
                $table->string('Mobiel', 20);
            });
        }

        if (! Schema::hasTable('Magazijn')) {
            Schema::create('Magazijn', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('Id');
                $table->unsignedInteger('ProductId');
                $table->decimal('VerpakkingsEenheidInKilogram', 5, 2);
                $table->unsignedInteger('AantalAanwezig')->nullable();
                $table->foreign('ProductId', 'FK_Magazijn_Product')
                    ->references('Id')
                    ->on('Product');
            });
        }

        if (! Schema::hasTable('ProductPerAllergeen')) {
            Schema::create('ProductPerAllergeen', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('Id');
                $table->unsignedInteger('ProductId');
                $table->unsignedInteger('AllergeenId');
                $table->foreign('ProductId', 'FK_ProductPerAllergeen_Product')
                    ->references('Id')
                    ->on('Product');
                $table->foreign('AllergeenId', 'FK_ProductPerAllergeen_Allergeen')
                    ->references('Id')
                    ->on('Allergeen');
            });
        }

        if (! Schema::hasTable('ProductPerLeverancier')) {
            Schema::create('ProductPerLeverancier', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('Id');
                $table->unsignedInteger('LeverancierId');
                $table->unsignedInteger('ProductId');
                $table->date('DatumLevering');
                $table->unsignedInteger('Aantal');
                $table->date('DatumEerstVolgendeLevering')->nullable();
                $table->foreign('LeverancierId', 'FK_ProductPerLeverancier_Leverancier')
                    ->references('Id')
                    ->on('Leverancier');
                $table->foreign('ProductId', 'FK_ProductPerLeverancier_Product')
                    ->references('Id')
                    ->on('Product');
            });
        }
    }

    public function down(): void
    {
        // De oorspronkelijke magazijntabellen worden niet automatisch verwijderd.
    }
};
