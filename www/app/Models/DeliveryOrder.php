<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasUuids;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
