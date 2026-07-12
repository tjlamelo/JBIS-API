<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ $logoUrl }}">
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; background:#f4f6f8;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:680px; background:#ffffff; border-radius:10px; overflow:hidden;">
                @include('emails.partials.brand-header', ['headerSubtitle' => $headerSubtitle ?? null])
                <tr>
                    <td style="padding:28px 24px; color:#111827; font-size:15px; line-height:1.6;">
                        <p style="margin:0 0 12px;">Bonjour {{ $userName }},</p>
                        @foreach($lines as $line)
                            <p style="margin:0 0 12px;">{{ $line }}</p>
                        @endforeach
                        @if(!empty($actionUrl) && !empty($actionLabel))
                            <p style="margin:20px 0 0;">
                                <a href="{{ $actionUrl }}" style="display:inline-block; background:#0f172a; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:6px; font-weight:600;">
                                    {{ $actionLabel }}
                                </a>
                            </p>
                        @endif
                    </td>
                </tr>
                @include('emails.partials.brand-footer')
            </table>
        </td>
    </tr>
</table>
</body>
</html>
