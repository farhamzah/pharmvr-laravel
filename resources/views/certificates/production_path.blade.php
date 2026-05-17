<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $certificate->certificate_id }} - PharmVR Certificate</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #172033;
            background: #ffffff;
        }

        .page {
            width: 100%;
            padding: 24px 32px;
            border: 10px solid #0f2438;
        }

        .outer-border {
            border: 2px solid #38bdf8;
            padding: 18px 24px;
        }

        /* ── Header ── */
        .brand {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 6px;
            color: #0f91b8;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .cert-title {
            text-align: center;
            font-size: 26pt;
            font-weight: bold;
            color: #0f2438;
            margin-bottom: 3px;
        }

        .cert-subtitle {
            text-align: center;
            font-size: 9pt;
            color: #526174;
            margin-bottom: 12px;
        }

        .divider {
            border: none;
            border-top: 1px solid #94a3b8;
            margin: 10px 0;
        }

        /* ── Recipient ── */
        .recipient-section {
            text-align: center;
            margin: 12px 0;
        }

        .recipient-label {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #64748b;
        }

        .recipient-name {
            font-size: 20pt;
            font-weight: bold;
            color: #0f2438;
            margin-top: 4px;
        }

        /* ── Path Title ── */
        .path-box {
            border: 1px solid #d8e0e7;
            background: #f8fafc;
            padding: 8px 16px;
            text-align: center;
            margin: 10px auto;
            width: 75%;
        }

        .path-label {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #64748b;
            margin-bottom: 3px;
        }

        .path-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f2438;
        }

        /* ── Meta Table ── */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .meta-table td {
            border: 1px solid #d8e0e7;
            padding: 8px 12px;
            vertical-align: top;
            background: #fbfdff;
        }

        .meta-label {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            display: block;
            margin-bottom: 3px;
        }

        .meta-value {
            font-size: 10pt;
            font-weight: bold;
            color: #172033;
        }

        /* ── Footer with QR ── */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .footer-table td {
            vertical-align: bottom;
            padding: 0 6px;
        }

        .footer-left {
            font-size: 8pt;
            color: #526174;
            width: 50%;
        }

        .footer-center {
            text-align: center;
            width: 20%;
            vertical-align: middle;
        }

        .footer-right {
            text-align: center;
            width: 30%;
        }

        .signature-line {
            border-top: 1px solid #94a3b8;
            padding-top: 5px;
            font-size: 9pt;
            font-weight: bold;
            color: #0f2438;
        }

        .qr-label {
            font-size: 7pt;
            color: #64748b;
            text-align: center;
            margin-top: 4px;
        }

        .verify-url {
            font-size: 6.5pt;
            color: #0f91b8;
            word-break: break-all;
            text-align: center;
            margin-top: 2px;
        }

        .watermark-note {
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
            margin-top: 8px;
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
        <div class="outer-border">

            {{-- Brand --}}
            <div class="brand">PharmVR</div>

            {{-- Title --}}
            <div class="cert-title">Certificate of Completion</div>
            <div class="cert-subtitle">
                This certificate is awarded for completing the PharmVR CPOB/GMP virtual reality production training path.
            </div>

            <hr class="divider">

            {{-- Recipient --}}
            <div class="recipient-section">
                <div class="recipient-label">This certifies that</div>
                <div class="recipient-name">{{ $recipientName }}</div>
            </div>

            {{-- Path Title --}}
            <div class="path-box">
                <div class="path-label">has successfully completed</div>
                <div class="path-title">{{ $productionPathTitle }}</div>
            </div>

            {{-- Meta Grid --}}
            <table class="meta-table">
                <tr>
                    <td style="width:25%">
                        <span class="meta-label">Completion</span>
                        <span class="meta-value">Production Path Completed</span>
                    </td>
                    <td style="width:25%">
                        <span class="meta-label">Scenes Completed</span>
                        <span class="meta-value">{{ $completedScenesCount }} / {{ $totalScenesCount }} scenes</span>
                    </td>
                    <td style="width:25%">
                        <span class="meta-label">Certificate ID</span>
                        <span class="meta-value">{{ $certificate->certificate_id }}</span>
                    </td>
                    <td style="width:25%">
                        <span class="meta-label">Issued Date</span>
                        <span class="meta-value">{{ $issuedAt }}</span>
                    </td>
                </tr>
            </table>

            {{-- Footer: left info | center QR | right signature --}}
            <table class="footer-table">
                <tr>
                    <td class="footer-left">
                        PharmVR &mdash; CPOB/GMP Virtual Reality Training<br>
                        Non-Sterile Solid Dosage Production Path<br><br>
                        <strong>Verify this certificate:</strong><br>
                        {{ $verifyUrl }}
                    </td>
                    <td class="footer-center">
                        @if($qrMarkup)
                            {!! $qrMarkup !!}
                            <div class="qr-label">Scan to verify</div>
                        @else
                            <div class="qr-label">Verification URL above</div>
                        @endif
                    </td>
                    <td class="footer-right">
                        <div class="signature-line">Authorized by PharmVR System</div>
                    </td>
                </tr>
            </table>

            <div class="watermark-note">
                Official digital certificate &bull; {{ $certificate->certificate_id }} &bull; PharmVR CPOB/GMP Training
            </div>

        </div>
    </div>
</body>
</html>
