<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('type')->index();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->string('template');
            $table->json('sections')->nullable();
            $this->publication($table);
            $this->seo($table);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('icon')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('features')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $this->publication($table);
            $this->seo($table);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('tagline')->nullable();
            $table->longText('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->json('gallery')->nullable();
            $table->json('features')->nullable();
            $table->json('audiences')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('external_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $this->publication($table);
            $this->seo($table);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('client_name')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->text('summary')->nullable();
            $table->longText('challenge')->nullable();
            $table->longText('solution')->nullable();
            $table->longText('results')->nullable();
            $table->json('technologies')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $this->publication($table);
            $this->seo($table);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('cover_image')->nullable();
            $table->json('tags')->nullable();
            $this->publication($table);
            $this->seo($table, false);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('solution_type');
            $table->foreignId('service_id')->nullable()->constrained()->restrictOnDelete();
            $table->text('message');
            $table->text('source_url')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('status')->default('new')->index();
            $table->text('internal_notes')->nullable();
            $table->timestamp('privacy_accepted_at');
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('display_name')->nullable();
            $table->string('logo_light_background')->nullable();
            $table->string('logo_dark_background')->nullable();
            $table->string('icon')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->json('social_links')->nullable();
            $table->json('institutional_links')->nullable();
            $this->seo($table);
            $table->timestamps();
        });
    }

    private function publication(Blueprint $table): void
    {
        $table->string('status')->default('draft');
        $table->timestamp('published_at')->nullable();
        $table->string('locale', 16)->default('pt_BR');
        $table->uuid('translation_key')->nullable()->index();
        $table->unique(['locale', 'slug']);
        $table->index(['status', 'published_at']);
    }

    private function seo(Blueprint $table, bool $canonical = true): void
    {
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
        $table->string('og_image')->nullable();
        if ($canonical) {
            $table->string('canonical_url')->nullable();
        }
    }

    public function down(): void
    {
        foreach (['site_settings', 'leads', 'posts', 'projects', 'products', 'services', 'pages', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
