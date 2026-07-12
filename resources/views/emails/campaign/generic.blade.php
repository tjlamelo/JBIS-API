<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Message {{ $brandName }}</title>
    <link rel="icon" href="{{ $logoUrl }}">
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background:#f4f6f8;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:680px; background:#ffffff; border-radius:10px; overflow:hidden;">
                    @include('emails.partials.brand-header', ['headerSubtitle' => 'Excellence · Innovation · Opportunités'])

                    <tr>
                        <td style="padding:32px 24px; color:#111827; font-size:15px; line-height:1.6; text-align:left;">
                            @if(!empty($template['title']))
                                <h2 style="margin:0 0 14px; font-size:22px; line-height:1.3; color:#111827;">{{ $template['title'] }}</h2>
                            @endif

                            @if(!empty($template['intro']))
                                <p style="margin:0 0 16px; color:#374151;">{{ $template['intro'] }}</p>
                            @endif

                            @if(!empty($template['sections']) && is_array($template['sections']))
                                @foreach($template['sections'] as $section)
                                    @if(!empty($section['title']))
                                        <h3 style="margin:18px 0 8px; font-size:17px; color:#111827;">{{ $section['title'] }}</h3>
                                    @endif
                                    @if(!empty($section['text']))
                                        <p style="margin:0 0 10px; color:#374151;">{!! nl2br(e($section['text'])) !!}</p>
                                    @endif
                                @endforeach
                            @elseif(!empty($content))
                                <div style="color:#374151;">{!! nl2br(e($content)) !!}</div>
                            @endif

                            @if(!empty($template['ctas']) && is_array($template['ctas']))
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:22px;">
                                    <tr>
                                        @foreach($template['ctas'] as $cta)
                                            @if(!empty($cta['label']) && !empty($cta['url']))
                                                <td style="padding:0 8px 8px 0;">
                                                    <a href="{{ $cta['url'] }}" style="display:inline-block; background:#0f172a; color:#ffffff; text-decoration:none; font-weight:600; font-size:13px; padding:10px 14px; border-radius:6px;">
                                                        {{ $cta['label'] }}
                                                    </a>
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    @include('emails.partials.brand-footer', [
                        'footerNote' => !empty($template['footer_note'])
                            ? $template['footer_note']
                            : 'Ce message vous est envoyé par '.($brandName ?? 'MyJob Best').'.',
                    ])
                </table>
            </td>
        </tr>
    </table>

    <div style="display:none; white-space:nowrap; font:15px courier; line-height:0;">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </div>
</body>
</html>
