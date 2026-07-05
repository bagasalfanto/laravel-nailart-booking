<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Verifikasi — {{ config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background:#fdeae6; font-family: 'Helvetica Neue', Arial, sans-serif; color:#3a3133;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fdeae6; padding:40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 12px 40px rgba(205,163,173,0.18);">

                    {{-- Header --}}
                    <tr>
                        <td style="padding:32px 40px 16px; text-align:center; background:linear-gradient(180deg,#fdf2f5,#ffffff);">
                            <p style="margin:0; color:#a8526b; font-size:12px; letter-spacing:3px; text-transform:uppercase; font-weight:600;">
                                {{ config('app.name', 'Nailby Bilda') }}
                            </p>
                            <h1 style="margin:16px 0 0; color:#221d1f; font-size:32px; font-weight:normal; font-family:'Georgia', serif;">
                                Verify Your Email
                            </h1>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:8px 40px 16px; color:#3a3133; font-size:15px; line-height:1.6;">
                            <p style="margin:16px 0;">Halo <strong>{{ $user->full_name ?: $user->email }}</strong>,</p>
                            <p style="margin:16px 0;">Terima kasih sudah bergabung dengan kami. Gunakan kode berikut untuk memverifikasi alamat email kamu:</p>
                        </td>
                    </tr>

                    {{-- Code --}}
                    <tr>
                        <td style="padding:8px 40px 24px; text-align:center;">
                            <div style="display:inline-block; padding:24px 40px; background:#fdf2f5; border-radius:16px;">
                                <p style="margin:0; font-size:42px; letter-spacing:14px; color:#a23f66; font-weight:bold; font-family:'Courier New', monospace;">{{ $code }}</p>
                            </div>
                        </td>
                    </tr>

                    {{-- Notes --}}
                    <tr>
                        <td style="padding:0 40px 32px; color:#64748b; font-size:13px; line-height:1.6; text-align:center;">
                            <p style="margin:0 0 8px;">Kode aktif <strong style="color:#a23f66;">{{ $expiryMinutes }} menit</strong>.</p>
                            <p style="margin:0; color:#94a3b8;">Jangan bagikan kode ini ke siapa pun. Tim kami tidak akan pernah meminta kode verifikasi.</p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:20px 40px; background:#fdf2f5; text-align:center; color:#a8526b; font-size:12px; line-height:1.6;">
                            <p style="margin:0 0 4px;">
                                Tidak merasa membuat akun di {{ config('app.name') }}? Abaikan email ini.
                            </p>
                            <p style="margin:0; color:#c9a3ad;">© {{ date('Y') }} {{ config('app.name') }}. Luxury Nail Studio.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
