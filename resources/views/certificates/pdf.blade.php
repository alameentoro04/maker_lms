<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; color: #14243b; }
        .frame { border: 6px solid #14243b; padding: 60px; text-align: center; }
        .brand { font-size: 14px; letter-spacing: 2px; color: #c98a2c; text-transform: uppercase; }
        h1 { font-size: 32px; margin: 20px 0 10px; }
        .holder { font-size: 26px; margin: 30px 0; border-bottom: 1px solid #c98a2c; display: inline-block; padding-bottom: 6px; }
        .meta { margin-top: 30px; font-size: 13px; color: #4c5f7d; }
        .revoked { color: #c0392b; font-weight: bold; margin-top: 20px; }
        .cert-id { margin-top: 50px; font-size: 11px; color: #a9b6cc; }
    </style>
</head>
<body>
    <div class="frame">
        <p class="brand">Makers by Al-Ismail</p>
        <h1>Certificate of Completion</h1>
        <p>This is to certify that</p>
        <p class="holder">{{ $certificate->holder_name }}</p>
        <p>has successfully completed</p>
        <p class="holder">{{ $certificate->course_title }}</p>
        @if($certificate->cohort_label)
            <p class="meta">{{ $certificate->cohort_label }}</p>
        @endif
        <p class="meta">Issued {{ $certificate->issued_at->toFormattedDateString() }}</p>

        @if(!$certificate->isValid())
            <p class="revoked">This certificate has been revoked{{ $certificate->revoked_reason ? ': '.$certificate->revoked_reason : '.' }}</p>
        @endif

        <p class="cert-id">Certificate ID: {{ $certificate->certificate_id }} — verify at /verify/{{ $certificate->certificate_id }}</p>
    </div>
</body>
</html>
