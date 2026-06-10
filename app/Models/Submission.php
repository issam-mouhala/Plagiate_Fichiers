<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        // User
        'user_id',

        // File
        'filename',
        'file_path',
        'original_extension',
        'detected_type',
        'file_size',
        'is_zip',

        // Content
        'extracted_text',
        'content_length',
        'images_extracted',
        'pages',

        // Results
        'overall_score',
        'overall_level',
        'plagiarism_detected',
        'num_comparisons',
        'matches_count',
        'text_matches_count',
        'image_matches_count',
        'cross_matches_count',

        // JSON
        'engines_used',
        'content_analysis',
        'text_matches',
        'image_matches',
        'cross_matches',
        'per_file_results',
        'zip_info',
        'extraction',
        'timing',
        'raw_response',

        // Metadata
        'source',
        'ip_address',
        'session_id',
    ];

    protected $casts = [
        'is_zip'              => 'boolean',
        'plagiarism_detected' => 'boolean',
        'overall_score'       => 'float',
        'engines_used'        => 'array',
        'content_analysis'    => 'array',
        'text_matches'        => 'array',
        'image_matches'       => 'array',
        'cross_matches'       => 'array',
        'per_file_results'    => 'array',
        'zip_info'            => 'array',
        'extraction'          => 'array',
        'timing'              => 'array',
        'raw_response'        => 'array',
        'file_size'           => 'integer',
        'content_length'      => 'integer',
        'images_extracted'    => 'integer',
        'pages'               => 'integer',
        'num_comparisons'     => 'integer',
        'matches_count'       => 'integer',
        'text_matches_count'  => 'integer',
        'image_matches_count' => 'integer',
        'cross_matches_count' => 'integer',
    ];

    // =============================================
    //  RELATIONS
    // =============================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zipFiles(): HasMany
    {
        return $this->hasMany(SubmissionZipFile::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(SubmissionAnalysis::class);
    }

    // =============================================
    //  SCOPES — Filtres pratiques
    // =============================================

    public function scopePlagiarized($query)
    {
        return $query->where('plagiarism_detected', true);
    }

    public function scopeClean($query)
    {
        return $query->where('plagiarism_detected', false);
    }

    public function scopeCritical($query)
    {
        return $query->where('overall_level', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->whereIn('overall_level', ['critical', 'high']);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('detected_type', $type);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeFromUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // =============================================
    //  ACCESSEURS — Formattage
    // =============================================

    public function getScorePercentAttribute(): float
    {
        return round($this->overall_score * 100, 1);
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    public function getLevelLabelAttribute(): string
    {
        return match ($this->overall_level) {
            'critical' => 'Critique',
            'high'     => 'Élevé',
            'medium'   => 'Moyen',
            'low'      => 'Faible',
            default    => 'Aucun',
        };
    }

    public function getLevelColorAttribute(): string
    {
        return match ($this->overall_level) {
            'critical' => '#dc2626',
            'high'     => '#f97316',
            'medium'   => '#f59e0b',
            'low'      => '#3b82f6',
            default    => '#10b981',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->detected_type) {
            'text'  => 'bi-file-text',
            'code'  => 'bi-code-slash',
            'pdf'   => 'bi-file-earmark-pdf',
            'docx'  => 'bi-file-earmark-word',
            'image' => 'bi-image',
            'zip'   => 'bi-file-earmark-zip',
            default => 'bi-file-earmark',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->detected_type) {
            'text'  => 'Texte',
            'code'  => 'Code',
            'pdf'   => 'PDF',
            'docx'  => 'Word',
            'image' => 'Image',
            'zip'   => 'ZIP',
            default => ucfirst($this->detected_type),
        };
    }

    // =============================================
    //  MÉTHODES UTILES
    // =============================================

    /**
     * Compte les soumissions par niveau (pour dashboard stats).
     */
    public static function getLevelStats(): array
    {
        return [
            'total'      => self::count(),
            'plagiarized'=> self::plagiarized()->count(),
            'clean'      => self::clean()->count(),
            'critical'   => self::critical()->count(),
            'high'       => self::high()->count(),
            'avg_score'  => round(self::avg('overall_score') * 100, 1),
            'today'      => self::whereDate('created_at', today())->count(),
            'this_week'  => self::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
        ];
    }

    /**
     * Dernières soumissions (pour tableau de bord).
     */
    public static function latestSubmissions(int $limit = 10)
    {
        return self::with('user')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Supprime le fichier physique du disque.
     */
    public function deleteFile(): bool
    {
        if ($this->file_path && file_exists(storage_path('app/' . $this->file_path))) {
            return unlink(storage_path('app/' . $this->file_path));
        }
        return true;
    }

    /**
     * Boot — supprime le fichier quand on supprime le modèle.
     */
    protected static function booted(): void
    {
        static::deleting(function (Submission $submission) {
            $submission->deleteFile();
        });
    }
}
