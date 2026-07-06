<div style="margin:0;padding:24px;background:#f4f7f6;font-family:Inter,Arial,sans-serif;color:#1f2c29;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7e0;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(14,102,86,.08);">
        <div style="padding:24px 28px;background:linear-gradient(135deg,#0e6656,#3d9c87);color:#ffffff;">
            <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.85;">SMART SIAMI</div>
            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.25;">{{ $title }}</h1>
            <div style="display:inline-block;margin-top:14px;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.16);font-size:12px;font-weight:700;">{{ $notificationType }}</div>
        </div>
        <div style="padding:28px;">
            <p style="margin:0 0 14px;font-size:15px;line-height:1.65;color:#42534e;">Yth. <strong style="color:#1f2c29;">{{ $recipientName }}</strong>,</p>
            <p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#42534e;">Ada pembaruan informasi pada sistem audit mutu internal yang memerlukan perhatian Anda.</p>
            <div style="margin:0 0 22px;padding:18px;border-radius:14px;background:#f7fbfa;border:1px solid #dcebe7;">
                <div style="margin-bottom:8px;font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#0e6656;">Ringkasan Informasi</div>
                <div style="font-size:15px;line-height:1.7;color:#2f423d;">{!! nl2br(e($body)) !!}</div>
            </div>
            <a href="{{ $targetUrl }}" style="display:inline-block;padding:12px 18px;border-radius:12px;background:#0e6656;color:#ffffff;text-decoration:none;font-weight:700;">Buka Detail di SMART SIAMI</a>
            <p style="margin:22px 0 0;font-size:13px;line-height:1.6;color:#6b7b76;">
                Silakan buka sistem untuk melihat detail, meninjau data terkait, atau melanjutkan proses audit sesuai peran Anda.
            </p>
            <p style="margin:18px 0 0;padding-top:16px;border-top:1px solid #e5e7e0;font-size:12px;line-height:1.55;color:#6b7b76;">
                Email ini dikirim otomatis oleh {{ $institution }} melalui SMART SIAMI. Jika tombol tidak bisa dibuka, salin tautan berikut ke browser:<br>
                <span style="word-break:break-all;color:#0e6656;">{{ $targetUrl }}</span>
            </p>
        </div>
    </div>
</div>
