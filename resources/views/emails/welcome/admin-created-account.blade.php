<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Votre compte {{ $brandName }}</title>
    <link rel="icon" href="{{ $logoUrl }}">
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; background:#f4f6f8;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:680px; background:#ffffff; border-radius:10px; overflow:hidden;">
                @include('emails.partials.brand-header', ['headerSubtitle' => 'Bienvenue sur votre espace'])
                <tr>
                    <td style="padding:28px 24px; color:#111827; font-size:15px; line-height:1.6;">
                        <p style="margin:0 0 12px;">Bonjour {{ $userName }},</p>
                        <p style="margin:0 0 12px;">
                            Votre compte <strong>{{ $brandName }}</strong> a été créé par notre équipe.
                            Vous trouverez ci-dessous vos identifiants de première connexion.
                        </p>
                        <p style="margin:0 0 8px;"><strong>Adresse e-mail :</strong> {{ $user->email }}</p>
                        <p style="margin:0 0 20px;"><strong>Mot de passe temporaire :</strong> {{ $plainPassword }}</p>
                        <p style="margin:0 0 20px; color:#4b5563;">
                            Pour des raisons de sécurité, vous devrez choisir un nouveau mot de passe lors de votre première connexion,
                            puis vous reconnecter avec ce nouveau mot de passe.
                        </p>
                        <p style="margin:0 0 20px;">
                            <a href="{{ $loginUrl }}" style="display:inline-block; background:#0f172a; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:6px; font-weight:600;">
                                Se connecter pour la première fois
                            </a>
                        </p>
                        <p style="margin:0; font-size:13px; color:#6b7280;">
                            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                            <span style="word-break:break-all;">{{ $loginUrl }}</span>
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
