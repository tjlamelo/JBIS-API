<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brandName }} Newsletter</title>
    <link rel="icon" href="{{ $logoUrl }}">
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; background:#f4f6f8;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:680px; background:#ffffff; border-radius:10px; overflow:hidden;">
                @include('emails.partials.brand-header', ['headerSubtitle' => 'Newsletter offres'])
                <tr>
                    <td style="padding:28px 24px; color:#111827; font-size:15px; line-height:1.6;">
                        <p style="margin:0 0 12px;">{{ $copy['greeting'] }},</p>
                        <p style="margin:0 0 24px; color:#374151;">{{ $copy['intro'] }}</p>

                        @if($content['has_national'])
                            <h2 style="margin:0 0 12px; font-size:16px; color:#b8860b;">{{ $copy['national_title'] }}</h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:24px;">
                                @foreach($content['national'] as $offer)
                                    <tr>
                                        <td style="padding:12px 0; border-bottom:1px solid #e5e7eb;">
                                            <a href="{{ $offer['url'] }}" style="color:#0f172a; font-weight:700; text-decoration:none; font-size:15px;">{{ $offer['title'] }}</a>
                                            <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">
                                                {{ collect([$offer['company'], $offer['city'], $offer['country']])->filter()->implode(' · ') }}
                                                @if(!empty($offer['published_at'])) · {{ $offer['published_at'] }} @endif
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif

                        @if($content['has_international'])
                            <h2 style="margin:0 0 12px; font-size:16px; color:#b8860b;">{{ $copy['international_title'] }}</h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:24px;">
                                @foreach($content['international'] as $offer)
                                    <tr>
                                        <td style="padding:12px 0; border-bottom:1px solid #e5e7eb;">
                                            <a href="{{ $offer['url'] }}" style="color:#0f172a; font-weight:700; text-decoration:none; font-size:15px;">{{ $offer['title'] }}</a>
                                            <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">
                                                {{ collect([$offer['company'], $offer['city'], $offer['country']])->filter()->implode(' · ') }}
                                                @if(!empty($offer['published_at'])) · {{ $offer['published_at'] }} @endif
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif

                        <p style="margin:0 0 20px;">
                            <a href="{{ $offersUrl }}" style="display:inline-block; background:#0f172a; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:6px; font-weight:600;">
                                {{ $copy['view_all'] }}
                            </a>
                        </p>
                    </td>
                </tr>
                @include('emails.partials.brand-footer', ['footerNote' => $copy['footer']])
                <tr>
                    <td style="padding:0 24px 16px; text-align:center;">
                        <a href="{{ $unsubscribeUrl }}" style="color:#6b7280; font-size:12px; text-decoration:underline;">{{ $copy['unsubscribe'] }}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
