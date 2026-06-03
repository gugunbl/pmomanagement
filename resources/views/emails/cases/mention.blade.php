<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Case Mention Notification</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333333; line-height: 1.5;">
    <p>Halo {{ $mentionedUser->name }},</p>

    <p>
        {{ optional($comment->user)->name ?? 'User' }} mention Anda pada case
        <strong>#{{ $case->id_case }}</strong>
        @if(!empty($case->subject))
            - {{ $case->subject }}
        @endif
    </p>

    <div style="margin: 16px 0; padding: 14px 16px; border-left: 4px solid #da251d; background: #f8f9fa;">
        {!! nl2br(e($comment->comment)) !!}
    </div>

    <p>
        <a href="{{ $caseUrl }}" style="display: inline-block; padding: 10px 16px; background: #da251d; color: #ffffff; text-decoration: none; border-radius: 4px;">
            Buka Case di System
        </a>
    </p>

    <p style="font-size: 12px; color: #777777;">
        Jika tombol tidak bisa dibuka, salin link berikut ke browser:<br>
        <a href="{{ $caseUrl }}">{{ $caseUrl }}</a>
    </p>
</body>
</html>
