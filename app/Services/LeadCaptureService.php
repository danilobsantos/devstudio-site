<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Mail\LeadNotificationMail;
use App\Models\Lead;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LeadCaptureService
{
    /**
     * Persiste o lead comercial de forma atômica e resiliente.
     * O registro é confirmado no banco antes do disparo do e-mail.
     * Falhas no servidor de correio (SMTP) não descartam o lead capturado.
     */
    public function capture(array $data): Lead
    {
        $lead = DB::transaction(function () use ($data) {
            $record = new Lead;
            $record->name = $data['name'];
            $record->company = $data['company'] ?? null;
            $record->email = $data['email'];
            $record->phone = $data['phone'] ?? null;
            $record->solution_type = $data['solution_type'];
            $record->service_id = $data['solution_type'] === 'service' ? ($data['service_id'] ?? null) : null;
            $record->message = $data['message'];
            $record->source_url = $data['source_url'] ?? null;
            $record->utm_source = $data['utm_source'] ?? null;
            $record->utm_medium = $data['utm_medium'] ?? null;
            $record->utm_campaign = $data['utm_campaign'] ?? null;
            $record->status = LeadStatus::New;
            $record->privacy_accepted_at = now();
            $record->save();

            return $record;
        });

        $this->notifyTeam($lead);
        $this->dispatchCrmWebhookIfConfigured($lead);

        return $lead;
    }

    private function notifyTeam(Lead $lead): void
    {
        try {
            $recipient = SiteSetting::query()->value('email') ?? config('mail.from.address');
            if (is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                Mail::to($recipient)->send(new LeadNotificationMail($lead));
            }
        } catch (Throwable $exception) {
            Log::warning('Falha ao enviar e-mail de notificação de novo lead: '.$exception->getMessage(), [
                'lead_id' => $lead->id,
                'email' => $lead->email,
            ]);
        }
    }

    /**
     * Ponto de extensão desacoplado para integração futura com DeskcommCRM (T5.08 / Seção 10.4).
     */
    private function dispatchCrmWebhookIfConfigured(Lead $lead): void
    {
        $webhookUrl = config('services.deskcomm_crm.webhook_url');
        if (! is_string($webhookUrl) || trim($webhookUrl) === '') {
            return;
        }

        // Futura chamada HTTP segura com timeout curto e segredo compartilhado.
    }
}
