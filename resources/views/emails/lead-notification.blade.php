<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Novo Lead Comercial — DevStudio</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #F6F6F7; color: #17171B; margin: 0; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 8px; border: 1px solid #E5E7EB; padding: 32px;">
        <tr>
            <td>
                <h1 style="color: #D10047; font-size: 22px; margin-top: 0; margin-bottom: 20px;">Novo Lead Comercial Recebido</h1>
                <p style="font-size: 15px; line-height: 1.5; margin-bottom: 24px;">Um novo contato foi enviado pelo formulário institucional da DevStudio:</p>

                <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 14px; border-collapse: collapse; margin-bottom: 24px;">
                    <tr style="border-bottom: 1px solid #F0F0F0;">
                        <td width="35%" style="font-weight: bold; color: #717171;">Nome:</td>
                        <td>{{ $lead->name }}</td>
                    </tr>
                    @if ($lead->company)
                    <tr style="border-bottom: 1px solid #F0F0F0;">
                        <td style="font-weight: bold; color: #717171;">Empresa:</td>
                        <td>{{ $lead->company }}</td>
                    </tr>
                    @endif
                    <tr style="border-bottom: 1px solid #F0F0F0;">
                        <td style="font-weight: bold; color: #717171;">E-mail:</td>
                        <td><a href="mailto:{{ $lead->email }}" style="color: #D10047;">{{ $lead->email }}</a></td>
                    </tr>
                    @if ($lead->phone)
                    <tr style="border-bottom: 1px solid #F0F0F0;">
                        <td style="font-weight: bold; color: #717171;">WhatsApp / Telefone:</td>
                        <td>{{ $lead->phone }}</td>
                    </tr>
                    @endif
                    <tr style="border-bottom: 1px solid #F0F0F0;">
                        <td style="font-weight: bold; color: #717171;">Interesse:</td>
                        <td>
                            @if ($lead->solution_type === 'service' && $lead->service)
                                {{ $lead->service->title }} (Serviço)
                            @else
                                Outro / Demanda Customizada
                            @endif
                        </td>
                    </tr>
                    @if ($lead->source_url)
                    <tr style="border-bottom: 1px solid #F0F0F0;">
                        <td style="font-weight: bold; color: #717171;">Página de Origem:</td>
                        <td><a href="{{ $lead->source_url }}" style="color: #D10047; word-break: break-all;">{{ $lead->source_url }}</a></td>
                    </tr>
                    @endif
                    @if ($lead->utm_source || $lead->utm_campaign)
                    <tr style="border-bottom: 1px solid #F0F0F0;">
                        <td style="font-weight: bold; color: #717171;">Campanha UTM:</td>
                        <td>{{ $lead->utm_source }} / {{ $lead->utm_campaign }}</td>
                    </tr>
                    @endif
                </table>

                <div style="background-color: #F6F6F7; border-left: 4px solid #D10047; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
                    <div style="font-weight: bold; font-size: 13px; text-transform: uppercase; color: #717171; margin-bottom: 8px;">Mensagem:</div>
                    <div style="font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $lead->message }}</div>
                </div>

                <p style="font-size: 12px; color: #717171; margin-top: 32px; border-top: 1px solid #E5E7EB; padding-top: 16px;">
                    Consentimento de privacidade aceito em: {{ $lead->privacy_accepted_at?->format('d/m/Y H:i:s') ?? now()->format('d/m/Y H:i:s') }} UTC.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
