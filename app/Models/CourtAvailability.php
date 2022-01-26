<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtAvailability extends Model
{
    use HasFactory;
    protected $table = 'courtavailability';
    public $timestamps = false;
}
