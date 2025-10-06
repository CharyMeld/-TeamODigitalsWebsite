<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // SEO Fields
            $table->string('primary_keyword')->nullable()->after('meta_description');
            $table->text('secondary_keywords')->nullable()->after('primary_keyword');
            $table->text('introduction')->nullable()->after('excerpt');
            $table->json('sections')->nullable()->after('content');
            $table->text('conclusion')->nullable()->after('sections');
            $table->string('cta_text')->nullable()->after('conclusion');
            $table->string('cta_link')->nullable()->after('cta_text');
            $table->string('featured_image')->nullable()->after('thumbnail');
            $table->string('featured_image_alt')->nullable()->after('featured_image');
            $table->text('tags')->nullable()->after('secondary_keywords');

            // Additional SEO
            $table->string('canonical_url')->nullable()->after('meta_description');
            $table->text('schema_markup')->nullable()->after('canonical_url');
            $table->integer('reading_time')->nullable()->after('views'); // in minutes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn([
                'primary_keyword',
                'secondary_keywords',
                'introduction',
                'sections',
                'conclusion',
                'cta_text',
                'cta_link',
                'featured_image',
                'featured_image_alt',
                'tags',
                'canonical_url',
                'schema_markup',
                'reading_time',
            ]);
        });
    }
};
