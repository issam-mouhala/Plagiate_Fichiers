<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAnalysis extends Model
{
    use HasFactory;

    protected $table = 'submission_analyses';

    protected $fillable = [
        'submission_id',
        'analysis_type',
        'overall_score',
        'overall_level',
        'plagiarism_detected',
        'duration_seconds',
        'api_response',
    ];

    protected $casts = [
        'overall_score'       => 'float',
        'plagiarism_detected' => 'boolean',
        'api_response'        => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
