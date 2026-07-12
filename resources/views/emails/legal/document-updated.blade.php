<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mise à jour document légal — {{ $brandName }}</title>
    <link rel="icon" href="{{ $logoUrl }}">
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; background:#f4f6f8;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:680px; background:#ffffff; border-radius:10px; overflow:hidden;">
                @include('emails.partials.brand-header', ['headerSubtitle' => 'Mise à jour de nos documents légaux'])
                <tr>
                    <td style="padding:28px 24px; color:#111827; font-size:15px; line-height:1.6;">
                        <p style="margin:0 0 12px;">Bonjour {{ $user->name ?? 'cher utilisateur' }},</p>
                        <p style="margin:0 0 12px;">
                            Nous avons publié une nouvelle version de <strong>{{ $documentLabel }}</strong>
                            (version {{ $document->version }}).
                        </p>
                        @if($document->summary)
                            <p style="margin:0 0 12px; padding:12px 14px; background:#f8fafc; border-left:4px solid #0f172a; color:#334155;">
                                {{ $document->summary }}
                            </p>
                        @endif
                        <p style="margin:0 0 20px;">
                            Pour continuer à utiliser votre espace {{ $brandName }}, veuillez consulter le document et confirmer votre acceptation.
                        </p>
                        <p style="margin:0;">
                            <a href="{{ $consentsUrl }}" style="display:inline-block; background:#0f172a; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:6px; font-weight:600;">
                                Consulter et accepter
                            </a>
                        </p>
                    </td>
                </tr>
                @include('emails.partials.brand-footer')
            </table>
        </td>
    </tr>
</table>
</body>
</html>
