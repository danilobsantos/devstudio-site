<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Mail\LeadNotificationMail;
use App\Models\Lead;
use App\Models\Service;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LeadCaptureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'app.url' => 'http://devstudio.test',
        ]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
    }

    private function createPublishedService(): Service
    {
        $service = new Service;
        $service->title = 'Desenvolvimento de Software';
        $service->slug = 'desenvolvimento-software';
        $service->status = 'published';
        $service->locale = 'pt_BR';
        $service->sort_order = 1;
        $service->is_featured = true;
        $service->save();

        return $service;
    }

    public function test_contact_page_can_be_rendered(): void
    {
        $service = $this->createPublishedService();

        $response = $this->get('/contato');
        $response->assertOk();
        $response->assertSee('Fale com a DevStudio');
        $response->assertSee('Nome completo');
        $response->assertSee('E-mail corporativo');
        $response->assertSee('Desenvolvimento de Software');
        $response->assertSee('hp_verification');
    }

    public function test_valid_lead_submission_persists_in_database_and_redirects(): void
    {
        Mail::fake();
        $service = $this->createPublishedService();

        $payload = [
            'name' => 'Carlos Silva',
            'company' => 'Empresa Exemplo Ltda',
            'email' => 'carlos@empresa.com.br',
            'phone' => '(11) 98765-4321',
            'solution_type' => 'service',
            'service_id' => $service->id,
            'message' => 'Gostaria de uma proposta comercial para desenvolvimento de um sistema interno.',
            'privacy_accepted' => '1',
            'source_url' => 'http://devstudio.test/solucoes',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'software_2026',
        ];

        $response = $this->post('/contato', $payload);
        $response->assertRedirect('/contato');
        $response->assertSessionHas('status', 'success');

        $this->assertDatabaseHas('leads', [
            'name' => 'Carlos Silva',
            'company' => 'Empresa Exemplo Ltda',
            'email' => 'carlos@empresa.com.br',
            'solution_type' => 'service',
            'service_id' => $service->id,
            'status' => LeadStatus::New->value,
            'utm_source' => 'google',
        ]);

        $lead = Lead::where('email', 'carlos@empresa.com.br')->first();
        $this->assertNotNull($lead);
        $this->assertNotNull($lead->privacy_accepted_at);

        Mail::assertSent(LeadNotificationMail::class, function ($mail) use ($lead) {
            return $mail->lead->id === $lead->id;
        });
    }

    public function test_solution_type_other_does_not_require_service_id(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Mariana Souza',
            'email' => 'mariana@startup.io',
            'solution_type' => 'other',
            'message' => 'Preciso de uma consultoria técnica especializada para arquitetura de nuvem.',
            'privacy_accepted' => '1',
        ];

        $response = $this->post('/contato', $payload);
        $response->assertRedirect('/contato');
        $response->assertSessionHas('status', 'success');

        $this->assertDatabaseHas('leads', [
            'name' => 'Mariana Souza',
            'email' => 'mariana@startup.io',
            'solution_type' => 'other',
            'service_id' => null,
            'status' => LeadStatus::New->value,
        ]);
    }

    public function test_honeypot_rejects_bot_submissions(): void
    {
        $payload = [
            'name' => 'Bot Spammer',
            'email' => 'bot@spammer.org',
            'solution_type' => 'other',
            'message' => 'Spam link http://spam.xyz',
            'privacy_accepted' => '1',
            'hp_verification' => 'I am a robot filling this field',
        ];

        $response = $this->post('/contato', $payload);
        $response->assertSessionHasErrors(['hp_verification']);
        $this->assertDatabaseMissing('leads', ['name' => 'Bot Spammer']);
    }

    public function test_lead_persists_even_when_mail_sending_fails(): void
    {
        // Simular falha de envio de e-mail
        Mail::shouldReceive('to->send')->andThrow(new \RuntimeException('Conexão SMTP recusada.'));

        $payload = [
            'name' => 'Roberto Oliveira',
            'email' => 'roberto@resiliente.com.br',
            'solution_type' => 'other',
            'message' => 'Mensagem com falha simulada de envio de notificação por e-mail.',
            'privacy_accepted' => '1',
        ];

        $response = $this->post('/contato', $payload);
        $response->assertRedirect('/contato');
        $response->assertSessionHas('status', 'success');

        // Lead DEVE estar salvo no banco
        $this->assertDatabaseHas('leads', [
            'name' => 'Roberto Oliveira',
            'email' => 'roberto@resiliente.com.br',
            'status' => LeadStatus::New->value,
        ]);
    }

    public function test_rate_limiting_protects_contact_endpoint(): void
    {
        $payload = [
            'name' => 'Teste Limite',
            'email' => 'rate@teste.com.br',
            'solution_type' => 'other',
            'message' => 'Teste de envio consecutivo.',
            'privacy_accepted' => '1',
        ];

        // 5 requisições são permitidas pela regra throttle:5,1
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contato', $payload)->assertRedirect('/contato');
        }

        // A 6ª requisição no mesmo minuto deve ser bloqueada (HTTP 429)
        $this->post('/contato', $payload)->assertStatus(429);
    }
}
