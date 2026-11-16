<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Services\LeadCaptureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    private const LOCALE = 'pt_BR';

    public function show(Request $request): View
    {
        $services = Service::query()
            ->publishedInLocale(self::LOCALE)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'title', 'slug']);

        $settings = SiteSetting::query()->first();

        $selectedServiceSlug = $request->query('servico');
        $selectedServiceId = null;
        if (is_string($selectedServiceSlug)) {
            $selectedServiceId = $services->firstWhere('slug', $selectedServiceSlug)?->id;
        }

        $whatsAppNumber = preg_replace('/\D/', '', (string) ($settings?->whatsapp ?? ''));
        $whatsAppUrl = null;
        if ($whatsAppNumber !== '') {
            $message = urlencode('Olá! Gostaria de conversar com a equipe da DevStudio sobre um projeto.');
            $whatsAppUrl = 'https://wa.me/'.$whatsAppNumber.'?text='.$message;
        }

        return view('pages.contact', [
            'metaTitle' => 'Contato — DevStudio Inovação Digital',
            'metaDescription' => 'Fale com nossos especialistas em desenvolvimento de software, automação e transformação digital.',
            'services' => $services,
            'settings' => $settings,
            'selectedServiceId' => $selectedServiceId,
            'whatsAppUrl' => $whatsAppUrl,
        ]);
    }

    public function submit(StoreLeadRequest $request, LeadCaptureService $leadCapture): RedirectResponse
    {
        $leadCapture->capture($request->validated());

        return redirect()->route('contact.show')
            ->with('status', 'success')
            ->with('lead_converted', true);
    }
}
