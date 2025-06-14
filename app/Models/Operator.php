<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $fillable = ['name', 'telegram_id', 'status', 'lunch_order', 'last_lunch_date'];
}
