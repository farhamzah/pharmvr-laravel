<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $certificate->certificate_id }} - PharmVR Certificate</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 9.6pt;
            color: #172033;
            background: #ffffff;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .page {
            width: auto;
            max-width: 100%;
            padding: 8mm;
            border: 2.4mm solid #0b2038;
            background: #f8fbff;
            overflow: hidden;
        }

        .shell {
            width: 100%;
            max-width: 100%;
            min-height: 160mm;
            padding: 7mm 8mm;
            border: 1.2pt solid #63e6f5;
            background: #ffffff;
            overflow: hidden;
        }

        .top-table,
        .meta-table,
        .footer-table {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .brand-cell {
            width: 45%;
            vertical-align: top;
        }

        .badge-cell {
            width: 55%;
            text-align: right;
            vertical-align: top;
            padding-right: 4mm;
        }

        .brand-mark {
            display: inline-block;
            padding: 6px 10px;
            background: #0b2038;
            color: #67e8f9;
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .brand-subtitle {
            margin-top: 6px;
            color: #526174;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .completion-badge {
            display: inline-block;
            padding: 6px 10px;
            border: 1px solid #22c55e;
            background: #ecfdf5;
            color: #0f7a45;
            font-size: 7.2pt;
            font-weight: bold;
            letter-spacing: .9px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .accent-line {
            height: 3px;
            margin: 10px 0 14px;
            background: #67e8f9;
        }

        .title {
            text-align: center;
            font-size: 27pt;
            font-weight: bold;
            color: #0b2038;
            letter-spacing: .5px;
            margin-bottom: 4px;
        }

        .subtitle {
            width: 84%;
            margin: 0 auto 12px;
            text-align: center;
            color: #526174;
            font-size: 8.5pt;
            line-height: 1.45;
        }

        .recipient {
            text-align: center;
            margin: 11px 0 11px;
        }

        .recipient-label {
            color: #64748b;
            font-size: 7.3pt;
            font-weight: bold;
            letter-spacing: 1.8px;
            text-transform: uppercase;
        }

        .recipient-name {
            margin-top: 5px;
            color: #0b2038;
            font-size: 21pt;
            font-weight: bold;
        }

        .path-box {
            width: 82%;
            max-width: 100%;
            margin: 0 auto 12px;
            padding: 8px 12px;
            text-align: center;
            border: 1px solid #d8e0e7;
            background: #f6fbff;
        }

        .path-label {
            color: #64748b;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 1.7px;
            text-transform: uppercase;
        }

        .path-title {
            margin-top: 4px;
            color: #0b2038;
            font-size: 12pt;
            font-weight: bold;
        }

        .meta-table {
            margin-top: 6px;
        }

        .meta-table td {
            width: 25%;
            padding: 7px 8px;
            border: 1px solid #d8e0e7;
            background: #fbfdff;
            vertical-align: top;
            word-wrap: break-word;
        }

        .meta-label {
            display: block;
            margin-bottom: 4px;
            color: #64748b;
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .meta-value {
            color: #172033;
            font-size: 8.5pt;
            font-weight: bold;
            line-height: 1.25;
            word-wrap: break-word;
        }

        .footer-table {
            margin-top: 12px;
        }

        .footer-table td {
            vertical-align: bottom;
            padding: 0 4px;
            word-wrap: break-word;
        }

        .verify-cell {
            width: 45%;
            color: #526174;
            font-size: 7pt;
            line-height: 1.42;
        }

        .verify-title {
            color: #0b2038;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .verify-url {
            color: #0f91b8;
            font-size: 6.2pt;
            word-break: break-all;
            word-wrap: break-word;
        }

        .qr-cell {
            width: 17%;
            text-align: center;
            vertical-align: middle;
        }

        .qr-label {
            margin-top: 4px;
            color: #64748b;
            font-size: 6.8pt;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .signature-cell {
            width: 38%;
            text-align: center;
            padding-right: 4mm;
        }

        .signature-line {
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            color: #0b2038;
            font-size: 8pt;
            font-weight: bold;
        }

        .signature-subtitle {
            margin-top: 3px;
            color: #64748b;
            font-size: 7pt;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .watermark-note {
            margin-top: 8px;
            text-align: center;
            color: #94a3b8;
            font-size: 6.2pt;
            letter-spacing: .4px;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    @php
        $recipientName = $user->name ?? $user->email ?? 'Authenticated User';
        $issuedAt = $certificate->issued_at
            ? $certificate->issued_at->format('d M Y')
            : now()->format('d M Y');
        $completedScenesCount = $completedScenes ?? 11;
        $totalScenesCount = $totalScenes ?? 11;
        $verifyUrl = $verificationUrl ?? '';
        $qrMarkup = $qrSvg ?? '';
    @endphp

    <div class="page">
        <div class="shell">
            <table class="top-table">
                <tr>
                    <td class="brand-cell">
                        <div class="brand-mark">PharmVR</div>
                        <div class="brand-subtitle">CPOB / GMP Virtual Training</div>
                    </td>
                    <td class="badge-cell">
                        <div class="completion-badge">Production Path Completed</div>
                    </td>
                </tr>
            </table>

            <div class="accent-line"></div>

            <div class="title">Certificate of Completion</div>
            <div class="subtitle">
                Awarded for completing the PharmVR non-sterile solid dosage Production Path with validated VR sessions and post-test completion.
            </div>

            <div class="recipient">
                <div class="recipient-label">This certifies that</div>
                <div class="recipient-name">{{ $recipientName }}</div>
            </div>

            <div class="path-box">
                <div class="path-label">has successfully completed</div>
                <div class="path-title">{{ $productionPathTitle }}</div>
            </div>

            <table class="meta-table">
                <tr>
                    <td>
                        <span class="meta-label">Status</span>
                        <span class="meta-value">Certificate Eligible</span>
                    </td>
                    <td>
                        <span class="meta-label">Scenes Completed</span>
                        <span class="meta-value">{{ $completedScenesCount }} / {{ $totalScenesCount }} scenes</span>
                    </td>
                    <td>
                        <span class="meta-label">Certificate ID</span>
                        <span class="meta-value">{{ $certificate->certificate_id }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Issued Date</span>
                        <span class="meta-value">{{ $issuedAt }}</span>
                    </td>
                </tr>
            </table>

            <table class="footer-table">
                <tr>
                    <td class="verify-cell">
                        <div class="verify-title">Verify this certificate</div>
                        <div class="verify-url">{{ $verifyUrl }}</div>
                        <br>
                        Official digital certificate for PharmVR CPOB/GMP training. The verification record confirms the certificate ID, recipient, issue date, and completion status.
                    </td>
                    <td class="qr-cell">
                        @if($qrMarkup)
                            {!! $qrMarkup !!}
                            <div class="qr-label">Scan to verify</div>
                        @else
                            <div class="qr-label">Verification URL above</div>
                        @endif
                    </td>
                    <td class="signature-cell">
                        <div class="signature-line">Authorized by PharmVR System</div>
                        <div class="signature-subtitle">Digital Training Record</div>
                    </td>
                </tr>
            </table>

            <div class="watermark-note">
                {{ $certificate->certificate_id }} - PharmVR CPOB/GMP Production Path Certificate
            </div>
        </div>
    </div>
</body>
</html>
