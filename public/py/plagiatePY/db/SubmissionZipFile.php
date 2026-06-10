<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionZipFile extends Model
{
    use HasFactory;

    protected $table = 'submission_zip_files';

    protected $fillable = [
        'submission_id',
        'filename',
        'extension',
        'file_type',
        'content_length',
        'max_score',
        'max_level',
        'best_match',
        'matches',
    ];

    protected $casts = [
        'matches'      => 'array',
        'max_score'    => 'float',
        'content_length' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
