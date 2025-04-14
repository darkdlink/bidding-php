<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiddingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tabela de órgãos licitantes
        Schema::create('bidding_agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code', 50)->nullable();
            $table->string('website', 255)->nullable();
            $table->text('contact_info')->nullable();
            $table->timestamps();
        });

        // Tabela de licitações
        Schema::create('biddings', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable()->comment('ID da licitação no sistema original');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->foreignId('agency_id')->nullable()->constrained('bidding_agencies')->onDelete('set null');
            $table->enum('bidding_type', ['pregão', 'concorrência', 'tomada de preços', 'convite', 'leilão', 'concurso', 'outros']);
            $table->string('modality', 100)->nullable();
            $table->enum('status', ['draft', 'published', 'in_progress', 'closed', 'cancelled', 'awarded'])->default('draft');
            $table->date('publication_date')->nullable();
            $table->datetime('opening_date')->nullable();
            $table->datetime('closing_date')->nullable();
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->string('document_url', 255)->nullable();
            $table->string('contact_email', 100)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->timestamps();

            // Índices para melhorar consultas frequentes
            $table->index('status');
            $table->index('bidding_type');
            $table->index('publication_date');
            $table->index('closing_date');
            $table->index(['external_id', 'agency_id']);
        });

        // Tabela de itens da licitação
        Schema::create('bidding_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidding_id')->constrained()->onDelete('cascade');
            $table->string('item_number', 20)->nullable();
            $table->text('description');
            $table->decimal('quantity', 15, 2);
            $table->string('unit', 50)->nullable();
            $table->decimal('estimated_unit_price', 15, 2)->nullable();
            $table->timestamps();

            // Índice para consultas frequentes
            $table->index('bidding_id');
        });

        // Tabela de propostas
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidding_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['draft', 'submitted', 'won', 'lost', 'cancelled'])->default('draft');
            $table->datetime('submission_date')->nullable();
            $table->decimal('total_value', 15, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices para consultas frequentes
            $table->index(['bidding_id', 'user_id']);
            $table->index('status');
            $table->index('submission_date');
        });

        // Tabela de itens da proposta
        Schema::create('proposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
            $table->foreignId('bidding_item_id')->constrained()->onDelete('cascade');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índice para consultas frequentes
            $table->index('proposal_id');
        });

        // Tabela de anexos
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->enum('related_type', ['bidding', 'proposal']);
            $table->unsignedBigInteger('related_id');
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->unsignedInteger('file_size')->nullable();
            $table->string('file_type', 100)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índice para consultas frequentes
            $table->index(['related_type', 'related_id']);
        });

        // Tabela de configurações de scraping
        Schema::create('scraping_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('bidding_agencies')->onDelete('cascade');
            $table->string('url', 255);
            $table->json('selectors')->nullable();
            $table->string('schedule', 50)->nullable()->comment('Formato cron para agendamento');
            $table->datetime('last_run')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Índice para consultas frequentes
            $table->index('agency_id');
            $table->index('active');
        });

        // Tabela de logs de scraping
        Schema::create('scraping_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_id')->constrained('scraping_configs')->onDelete('cascade');
            $table->datetime('start_time');
            $table->datetime('end_time')->nullable();
            $table->enum('status', ['success', 'partial', 'failed']);
            $table->unsignedInteger('items_found')->default(0);
            $table->unsignedInteger('items_processed')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Índice para consultas frequentes
            $table->index('config_id');
            $table->index('status');
        });

        // Tabela para análise e relatórios
        Schema::create('bidding_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('reference_date');
            $table->unsignedInteger('total_active_biddings')->default(0);
            $table->unsignedInteger('total_submitted_proposals')->default(0);
            $table->unsignedInteger('total_won_proposals')->default(0);
            $table->decimal('total_value_won', 15, 2)->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->timestamps();

            // Índice para consultas frequentes
            $table->unique('reference_date');
        });

        // Tabela para notificações
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->text('message');
            $table->string('type', 50)->nullable();
            $table->string('related_type', 50)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            // Índices para consultas frequentes
            $table->index('user_id');
            $table->index('read');
            $table->index(['related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('bidding_analytics');
        Schema::dropIfExists('scraping_logs');
        Schema::dropIfExists('scraping_configs');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('proposal_items');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('bidding_items');
        Schema::dropIfExists('biddings');
        Schema::dropIfExists('bidding_agencies');
    }
}
