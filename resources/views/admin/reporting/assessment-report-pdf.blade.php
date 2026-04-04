<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Assessment — PharmVR Pro</title>
    <style>
        @page { margin: 15mm 12mm; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', 'Inter', -apple-system, sans-serif; font-size: 10px; color: #1a1a2e; background: white; }
        
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #0f3460; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; color: #0f3460; font-weight: 900; letter-spacing: -0.5px; }
        .header .meta { text-align: right; font-size: 9px; color: #888; }
        .header .meta strong { color: #0f3460; }
        
        .stats-grid { display: flex; gap: 12px; margin-bottom: 20px; }
        .stat-card { flex: 1; background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%); border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; text-align: center; }
        .stat-card .label { font-size: 8px; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; font-weight: 800; margin-bottom: 4px; }
        .stat-card .value { font-size: 24px; font-weight: 900; color: #0f3460; }
        .stat-card .value.green { color: #059669; }
        .stat-card .value.blue { color: #3b82f6; }
        .stat-card .value.purple { color: #7c3aed; }
        .stat-card .sub { font-size: 8px; color: #94a3b8; margin-top: 2px; font-weight: 600; }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0; border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0; }
        thead th { background: #0f3460; color: white; padding: 10px 12px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; }
        thead th:first-child { border-radius: 10px 0 0 0; }
        thead th:last-child { border-radius: 0 10px 0 0; }
        tbody td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; font-size: 10px; vertical-align: middle; }
        tbody tr:nth-child(even) { background: #fafbfc; }
        tbody tr:last-child td { border-bottom: none; }
        
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 8px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }
        .badge-pass { background: #d1fae5; color: #065f46; }
        .badge-fail { background: #fee2e2; color: #991b1b; }
        .badge-pre { background: #dbeafe; color: #1e40af; }
        .badge-post { background: #ede9fe; color: #5b21b6; }
        
        .score { font-weight: 900; font-size: 13px; }
        .score.high { color: #059669; }
        .score.mid { color: #d97706; }
        .score.low { color: #dc2626; }
        
        .score-bar { width: 50px; height: 4px; background: #e2e8f0; border-radius: 2px; display: inline-block; vertical-align: middle; margin-left: 6px; overflow: hidden; }
        .score-bar-fill { height: 100%; border-radius: 2px; }
        .score-bar-fill.high { background: #059669; }
        .score-bar-fill.mid { background: #d97706; }
        .score-bar-fill.low { background: #dc2626; }
        
        .footer { margin-top: 24px; display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 2px solid #f1f5f9; font-size: 8px; color: #94a3b8; }
        .footer .brand { font-weight: 900; color: #0f3460; text-transform: uppercase; letter-spacing: 2px; }
        
        .no-print { margin-bottom: 20px; text-align: right; }
        .no-print button {
            padding: 10px 28px; background: linear-gradient(135deg, #0f3460, #1a56db); color: white;
            border: none; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 13px;
            letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(15, 52, 96, 0.3);
        }
        .no-print button:hover { transform: scale(1.02); }
        
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button>
    </div>

    <div class="header">
        <div>
            <h1>📊 Laporan Hasil Assessment</h1>
            <p style="color: #64748b; font-size: 11px; font-weight: 600; margin-top: 4px;">PharmVR Pro — Evaluasi Kompetensi Ruang Steril</p>
        </div>
        <div class="meta">
            <p>Dicetak: <strong>{{ now()->format('d M Y, H:i') }} WIB</strong></p>
            <p>Total Records: <strong>{{ $attempts->count() }}</strong></p>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Pengerjaan</div>
            <div class="value">{{ number_format($totalAttempts) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Tingkat Kelulusan</div>
            <div class="value green">{{ $totalAttempts > 0 ? number_format(($passedCount / $totalAttempts) * 100, 1) : 0 }}%</div>
            <div class="sub">{{ $passedCount }} dari {{ $totalAttempts }} lulus</div>
        </div>
        <div class="stat-card">
            <div class="label">Rata-Rata Skor</div>
            <div class="value blue">{{ number_format($avgScore, 1) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Lulus</div>
            <div class="value green">{{ $passedCount }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Tidak Lulus</div>
            <div class="value" style="color: #dc2626;">{{ $totalAttempts - $passedCount }}</div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Peserta</th>
                <th>Email</th>
                <th>Modul Pelatihan</th>
                <th style="text-align: center;">Tipe</th>
                <th style="text-align: center;">Skor</th>
                <th style="text-align: center;">Hasil</th>
                <th style="text-align: center;">Durasi</th>
                <th>Waktu Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attempts as $i => $attempt)
            @php
                $duration = ($attempt->started_at && $attempt->completed_at) ? $attempt->started_at->diffInMinutes($attempt->completed_at) : 0;
                $isPretest = ($attempt->assessment->type->value ?? '') === 'pretest';
                $typeLabel = $isPretest ? 'Pre-Test' : 'Post-Test';
                $scoreClass = $attempt->score >= 70 ? 'high' : ($attempt->score >= 40 ? 'mid' : 'low');
            @endphp
            <tr>
                <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $i + 1 }}</td>
                <td style="font-weight: 800;">{{ $attempt->user->name ?? '—' }}</td>
                <td style="color: #64748b;">{{ $attempt->user->email ?? '—' }}</td>
                <td>{{ Str::limit($attempt->assessment->trainingModule->title ?? '—', 35) }}</td>
                <td style="text-align: center;"><span class="badge {{ $isPretest ? 'badge-pre' : 'badge-post' }}">{{ $typeLabel }}</span></td>
                <td style="text-align: center;">
                    <span class="score {{ $scoreClass }}">{{ $attempt->score ?? 0 }}%</span>
                    <div class="score-bar"><div class="score-bar-fill {{ $scoreClass }}" style="width: {{ $attempt->score ?? 0 }}%"></div></div>
                </td>
                <td style="text-align: center;"><span class="badge {{ $attempt->passed ? 'badge-pass' : 'badge-fail' }}">{{ $attempt->passed ? '✓ Lulus' : '✗ Gagal' }}</span></td>
                <td style="text-align: center; color: #64748b; font-weight: 600;">{{ $duration }} min</td>
                <td style="color: #64748b;">{{ $attempt->completed_at?->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px; color: #94a3b8; font-style: italic;">Tidak ada data assessment</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <span class="brand">PharmVR Pro</span>
        <span>Assessment Report — Generated {{ now()->format('d M Y H:i:s') }} — Confidential & Internal Use Only</span>
    </div>
</body>
</html>
