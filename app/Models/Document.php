<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Document extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'document_id',
        'registration_number',
        'document_date',
        'document_type',
        'language_pair',
        'client_name',
        'status',
        'is_qr_generated',
        'translator_id',
    ];

    protected $casts = [
        'document_date' => 'date',
        'is_qr_generated' => 'boolean',
    ];

    /**
     * Relasi ke User (Penerjemah)
     */
    public function translator()
    {
        return $this->belongsTo(User::class, 'translator_id');
    }
}
