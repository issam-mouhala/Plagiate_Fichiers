<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();

            // --- User (nullable si pas d'auth) ---
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // --- File info ---
            $table->string('filename');
            $table->string('file_path')->nullable();        // chemin stockage local
            $table->string('original_extension', 20);
            $table->string('detected_type', 30);            // text, code, pdf, docx, image, zip
            $table->unsignedBigInteger('file_size')->default(0); // en bytes
            $table->boolean('is_zip')->default(false);

            // --- Contenu extrait ---
            $table->longText('extracted_text')->nullable();  // texte extrait du fichier
            $table->unsignedInteger('content_length')->default(0);
            $table->unsignedInteger('images_extracted')->default(0);
            $table->unsignedInteger('pages')->default(0)->nullable(); // pour PDF

            // --- Résultats analyse ---
            $table->float('overall_score')->default(0);       // 0.0 - 1.0
            $table->string('overall_level', 20)->default('none'); // none, low, medium, high, critical
            $table->boolean('plagiarism_detected')->default(false);
            $table->unsignedInteger('num_comparisons')->default(0);
            $table->unsignedInteger('matches_count')->default(0);
            $table->unsignedInteger('text_matches_count')->default(0);
            $table->unsignedInteger('image_matches_count')->default(0);
            $table->unsignedInteger('cross_matches_count')->default(0);

            // --- JSON détaillé ---
            $table->json('engines_used')->nullable();         // ["tfidf", "semantic_mpnet", ...]
            $table->json('content_analysis')->nullable();     // analyse texte complète
            $table->json('text_matches')->nullable();          // matches texte détaillés
            $table->json('image_matches')->nullable();         // matches images détaillés
            $table->json('cross_matches')->nullable();         // matches croisés (ZIP)
            $table->json('per_file_results')->nullable();      // résultats par fichier (ZIP)
            $table->json('zip_info')->nullable();              // infos ZIP
            $table->json('extraction')->nullable();            // métadonnées extraction
            $table->json('timing')->nullable();                 // temps d'exécution
            $table->json('raw_response')->nullable();          // réponse API brute

            // --- Métadonnées ---
            $table->string('source')->default('upload');       // upload, api
            $table->string('ip_address', 45)->nullable();
            $table->string('session_id')->nullable();

            $table->timestamps();

            // Index pour recherches rapides
            $table->index('user_id');
            $table->index('overall_score');
            $table->index('overall_level');
            $table->index('plagiarism_detected');
            $table->index('detected_type');
            $table->index('created_at');
        });

        // --- Table séparée pour les fichiers ZIP internes ---
        Schema::create('submission_zip_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('extension', 20);
            $table->string('file_type', 30);                // text, code, pdf, docx, image
            $table->unsignedInteger('content_length')->default(0);
            $table->float('max_score')->default(0);
            $table->string('max_level', 20)->default('none');
            $table->string('best_match')->nullable();
            $table->json('matches')->nullable();             // top 5 matches
            $table->timestamps();

            $table->index('submission_id');
        });

        // --- Table pour les historiques d'analyse (log par fichier analysé) ---
        Schema::create('submission_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('analysis_type', 30);              // single, zip, paraphrase
            $table->float('overall_score')->default(0);
            $table->string('overall_level', 20)->default('none');
            $table->boolean('plagiarism_detected')->default(false);
            $table->float('duration_seconds')->default(0);
            $table->json('api_response')->nullable();       // réponse brute
            $table->timestamps();

            $table->index('submission_id');
            $table->index('analysis_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_analyses');
        Schema::dropIfExists('submission_zip_files');
        Schema::dropIfExists('submissions');
    }
};
