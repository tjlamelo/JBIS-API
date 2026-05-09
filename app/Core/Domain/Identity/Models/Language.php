<?php
namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Location\Models\Language as CatalogLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Language extends Model
{
    use HasFactory;

    protected $table = 'user_languages';

    protected $fillable = [
        'user_id',
        'language_id',
        'proficiency_level',
        'is_approved',
        'approved_by',
        'reviewed_at',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Core\Domain\Identity\Models\User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(CatalogLanguage::class, 'language_id');
    }
}
