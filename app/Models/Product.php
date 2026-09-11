<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $table = 'Product';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    public function magazijn(): HasOne
    {
        return $this->hasOne(Magazijn::class, 'ProductId', 'Id');
    }

    /**
     * @return HasMany<ProductPerLeverancier, $this>
     */
    public function leveringen(): HasMany
    {
        return $this->hasMany(ProductPerLeverancier::class, 'ProductId', 'Id')
            ->orderBy('DatumLevering');
    }

    /**
     * @return BelongsToMany<Allergeen, $this>
     */
    public function allergenen(): BelongsToMany
    {
        return $this->belongsToMany(
            Allergeen::class,
            'ProductPerAllergeen',
            'ProductId',
            'AllergeenId',
            'Id',
            'Id',
        );
    }
}
