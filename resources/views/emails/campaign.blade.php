<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $creative['subject_line'] ?? 'Roomie' }}</title>
    <!--[if mso]>
    <style>
        * { font-family: Georgia, 'Times New Roman', serif !important; }
    </style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#faf8f5;font-family:Georgia,'Times New Roman',serif;color:#1a1a2e;-webkit-font-smoothing:antialiased;">

    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#faf8f5;">
        {{ $creative['preview_text'] ?? '' }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#faf8f5;">
        <tr>
            <td align="center" style="padding:48px 16px;">

                <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="max-width:640px;width:100%;background-color:#ffffff;border:1px solid #e2d1c3;">

                    {{-- ═══ Hero navy ═══ --}}
                    <tr>
                        <td style="background-color:#1a1a2e;padding:48px 48px 56px;">
                            <p style="margin:0 0 20px;font-family:'Courier New',Courier,monospace;font-size:11px;letter-spacing:2.5px;color:#c8956c;text-transform:uppercase;">
                                &#10022;&nbsp;&nbsp;{{ $hotelName }}
                            </p>
                            <h1 style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:36px;line-height:1.1;font-weight:normal;color:#faf8f5;letter-spacing:-0.01em;">
                                {{ $creative['headline'] ?? '' }}
                            </h1>
                        </td>
                    </tr>

                    {{-- ═══ Body ═══ --}}
                    <tr>
                        <td style="padding:48px 48px 24px;color:#1a1a2e;font-family:Georgia,'Times New Roman',serif;font-size:17px;line-height:1.7;">
                            {!! $rewrittenBody !!}
                        </td>
                    </tr>

                    {{-- ═══ Sparkle divider ═══ --}}
                    <tr>
                        <td align="center" style="padding:8px 48px 24px;">
                            <span style="color:#c8956c;font-size:14px;letter-spacing:12px;display:inline-block;">&mdash;&nbsp;&#10022;&nbsp;&mdash;</span>
                        </td>
                    </tr>

                    {{-- ═══ CTA button ═══ --}}
                    @if (! empty($creative['cta_text']))
                        <tr>
                            <td align="center" style="padding:8px 48px 56px;">
                                <!--[if mso]>
                                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $ctaUrl }}" style="height:54px;v-text-anchor:middle;width:300px;" arcsize="50%" stroke="f" fillcolor="#c8956c">
                                    <w:anchorlock/>
                                    <center style="color:#1a1a2e;font-family:Georgia,serif;font-size:15px;font-weight:bold;letter-spacing:0.02em;">{{ $creative['cta_text'] }} &#8594;</center>
                                </v:roundrect>
                                <![endif]-->
                                <!--[if !mso]><!-- -->
                                <a href="{{ $ctaUrl }}" rel="noopener nofollow" style="display:inline-block;background-color:#c8956c;color:#1a1a2e;text-decoration:none;padding:18px 40px;border-radius:999px;font-family:Georgia,'Times New Roman',serif;font-weight:600;font-size:15px;letter-spacing:0.02em;">
                                    {{ $creative['cta_text'] }}&nbsp;&nbsp;&#8594;
                                </a>
                                <!--<![endif]-->
                            </td>
                        </tr>
                    @endif

                    {{-- ═══ Footer ═══ --}}
                    <tr>
                        <td style="background-color:#faf8f5;padding:28px 48px 32px;border-top:1px solid #e2d1c3;color:#1a1a2e;font-family:'Courier New',Courier,monospace;font-size:11px;line-height:1.7;">
                            <p style="margin:0 0 8px;color:#1a1a2e99;">Eurostars Hotel Company &middot; Paseo de la Castellana 259, 28046 Madrid.</p>
                            <p style="margin:0;color:#1a1a2e99;">
                                Recibes este correo porque figuras en la base de clientes de Roomie para {{ $hotelName }}.
                                <a href="{{ $unsubscribeUrl }}" style="color:#1a1a2e;text-decoration:underline;">Darse de baja</a>.
                            </p>
                        </td>
                    </tr>
                </table>

                {{-- Sign-off below card --}}
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="max-width:640px;width:100%;">
                    <tr>
                        <td align="center" style="padding:20px 16px 0;color:#1a1a2e66;font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:13px;">
                            Make me want to travel.
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
    <img src="{{ $openPixelUrl }}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0;">
</body>
</html>
