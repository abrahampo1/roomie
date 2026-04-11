<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $creative['subject_line'] ?? 'Roomie' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#faf8f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1a1a2e;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#faf8f5;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#1a1a2e;color:#faf8f5;padding:32px 40px;">
                            <p style="margin:0 0 8px;font-size:11px;text-transform:uppercase;letter-spacing:2px;color:#c8956c;">{{ $hotelName }}</p>
                            <h1 style="margin:0;font-size:28px;line-height:1.2;font-weight:600;">{{ $creative['headline'] ?? '' }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 40px;color:#1a1a2e;font-size:15px;line-height:1.6;">
                            {!! $rewrittenBody !!}
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 40px 40px;">
                            @if (! empty($creative['cta_text']))
                                <a href="{{ $ctaUrl }}" rel="noopener nofollow" style="display:inline-block;background-color:#c8956c;color:#1a1a2e;text-decoration:none;padding:14px 28px;border-radius:999px;font-weight:500;font-size:14px;">
                                    {{ $creative['cta_text'] }}
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 40px;border-top:1px solid #e2d1c3;color:#1a1a2e99;font-size:11px;line-height:1.5;">
                            <p style="margin:0 0 8px;">Eurostars Hotel Company · Paseo de la Castellana 259, 28046 Madrid.</p>
                            <p style="margin:0;">
                                Recibes este correo porque figuras en la base de clientes de Roomie para {{ $hotelName }}.
                                <a href="{{ $unsubscribeUrl }}" style="color:#1a1a2ecc;text-decoration:underline;">Darse de baja</a>.
                            </p>
                        </td>
                    </tr>
                </table>
                <img src="{{ $openPixelUrl }}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0;">
            </td>
        </tr>
    </table>
</body>
</html>
