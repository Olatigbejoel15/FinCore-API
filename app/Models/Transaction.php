<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',      // Owner of the transaction
        'type',         // deposit or withdrawal
        'amount',       // Transaction amount
        'description',  // Optional transaction note
    ];

    // Relationship: a transaction belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
