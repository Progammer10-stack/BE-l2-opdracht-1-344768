<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPerLeverancier extends Model
{
    protected $table = 'ProductPerLeverancier';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'DatumLevering' => 'date',
            'DatumEerstVolgendeLevering' => 'date',
        ];
    }

    // ProductPerLeverancier.LeverancierId verwijst naar Leverancier.Id.
    public function leverancier(): BelongsTo
    {
        return $this->belongsTo(Leverancier::class, 'LeverancierId', 'Id');
    }

    // ProductPerLeverancier.ProductId verwijst naar Product.Id.
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductId', 'Id');
    }
}
