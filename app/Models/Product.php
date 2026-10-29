<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    // De schooltabellen heten Product en Id, niet de Laravel-standaard products en id.
    protected $table = 'Product';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    // Eén magazijnrij per product. Magazijn.ProductId verwijst naar Product.Id.
    public function magazijn(): HasOne
    {
        return $this->hasOne(Magazijn::class, 'ProductId', 'Id');
    }

    // Alle leveringen van dit product, van oud naar nieuw.
    public function leveringen(): HasMany
    {
        return $this->hasMany(ProductPerLeverancier::class, 'ProductId', 'Id')
            ->orderBy('DatumLevering');
    }

    // Allergenen via de koppeltabel ProductPerAllergeen, oplopend op naam.
    public function allergenen(): BelongsToMany
    {
        return $this->belongsToMany(
            Allergeen::class,
            'ProductPerAllergeen',
            'ProductId',
            'AllergeenId',
            'Id',
            'Id',
        )->orderBy('Allergeen.Naam');
    }
}
