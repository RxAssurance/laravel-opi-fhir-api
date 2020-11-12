<?php

namespace App\Models;

use App\Interfaces\FhirEntityInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coverage extends Model implements FhirEntityInterface
{
    use HasFactory;

    public function getCreateDateFromEntry($entry) {
        return $entry->resource->effectivePeriod->start;
    }

    protected $fillable = [
        'fhir_resource_id',
        'fhir_body',
        'fhir_resource_type',
    ];
}
