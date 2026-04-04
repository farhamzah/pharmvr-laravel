'use client';

import { useState, useEffect } from 'react';
import { HelpCircle, CheckCircle, XCircle, Target } from 'lucide-react';
import ReportCard from '@/components/reports/ReportCard';
import ExportButton from '@/components/reports/ExportButton';
import DataTable, { Badge, Column } from '@/components/reports/DataTable';
import { SimpleBarChart } from '@/components/reports/RechartsThemed';
import { fetchReport, getExportCsvUrl } from '@/lib/api';
import styles from '../reports.module.css';

interface OptionData {
  option_id: number;
  text: string;
  is_correct: boolean;
  selection_count: number;
  selection_rate: number;
}

interface QuestionRow {
  question_id: number;
  question_text: string;
  module: string;
  module_id: number;
  difficulty: string;
  usage_scope: string;
  times_answered: number;
  times_correct: number;
  correct_rate: number;
  discrimination: string;
  options: OptionData[];
  [key: string]: unknown;
}

interface ReportData {
  questions: QuestionRow[];
  summary: {
    total_questions: number;
    avg_correct_rate: number;
    hardest_question_id: number | null;
    hardest_correct_rate: number | null;
    easiest_question_id: number | null;
    easiest_correct_rate: number | null;
  };
}

const DEMO: ReportData = {
  summary: { total_questions: 30, avg_correct_rate: 65.2, hardest_question_id: 5, hardest_correct_rate: 22.5, easiest_question_id: 12, easiest_correct_rate: 95.8 },
  questions: [
    { question_id: 1, question_text: 'Apa fungsi utama enzim CYP3A4 dalam metabolisme obat?', module: 'Farmakologi Dasar', module_id: 1, difficulty: 'hard', usage_scope: 'pretest', times_answered: 48, times_correct: 15, correct_rate: 31.3, discrimination: 'hard', options: [{ option_id: 1, text: 'Metabolisme fase I', is_correct: true, selection_count: 15, selection_rate: 31.3 }, { option_id: 2, text: 'Ekskresi ginjal', is_correct: false, selection_count: 18, selection_rate: 37.5 }, { option_id: 3, text: 'Distribusi jaringan', is_correct: false, selection_count: 10, selection_rate: 20.8 }, { option_id: 4, text: 'Absorbsi GI', is_correct: false, selection_count: 5, selection_rate: 10.4 }] },
    { question_id: 2, question_text: 'Parasetamol termasuk golongan obat apa?', module: 'Farmakologi Dasar', module_id: 1, difficulty: 'easy', usage_scope: 'both', times_answered: 50, times_correct: 47, correct_rate: 94.0, discrimination: 'easy', options: [{ option_id: 5, text: 'Analgesik-Antipiretik', is_correct: true, selection_count: 47, selection_rate: 94.0 }, { option_id: 6, text: 'Antibiotik', is_correct: false, selection_count: 2, selection_rate: 4.0 }, { option_id: 7, text: 'Antihipertensi', is_correct: false, selection_count: 1, selection_rate: 2.0 }] },
    { question_id: 3, question_text: 'Dosis maksimal amoksisilin dewasa per hari?', module: 'Farmakologi Klinik', module_id: 2, difficulty: 'medium', usage_scope: 'posttest', times_answered: 35, times_correct: 21, correct_rate: 60.0, discrimination: 'good', options: [{ option_id: 8, text: '3 gram', is_correct: true, selection_count: 21, selection_rate: 60.0 }, { option_id: 9, text: '1 gram', is_correct: false, selection_count: 8, selection_rate: 22.9 }, { option_id: 10, text: '5 gram', is_correct: false, selection_count: 6, selection_rate: 17.1 }] },
    { question_id: 4, question_text: 'Interaksi obat warfarin dengan aspirin menyebabkan?', module: 'Farmakologi Klinik', module_id: 2, difficulty: 'hard', usage_scope: 'posttest', times_answered: 40, times_correct: 18, correct_rate: 45.0, discrimination: 'fair', options: [{ option_id: 11, text: 'Peningkatan risiko perdarahan', is_correct: true, selection_count: 18, selection_rate: 45.0 }, { option_id: 12, text: 'Penurunan efek antikoagulan', is_correct: false, selection_count: 14, selection_rate: 35.0 }, { option_id: 13, text: 'Tidak ada interaksi', is_correct: false, selection_count: 8, selection_rate: 20.0 }] },
  ],
};

const columns: Column<QuestionRow>[] = [
  { key: 'question_id', label: '#' },
  { key: 'question_text', label: 'Soal', render: (r) => <span title={r.question_text}>{r.question_text.length > 60 ? r.question_text.substring(0, 58) + '…' : r.question_text}</span> },
  { key: 'module', label: 'Modul' },
  { key: 'usage_scope', label: 'Scope', render: (r) => <Badge variant="cyan">{r.usage_scope}</Badge> },
  { key: 'times_answered', label: 'Dijawab' },
  { key: 'correct_rate', label: 'Correct %', render: (r) => <strong style={{ color: r.correct_rate >= 70 ? 'var(--success)' : r.correct_rate >= 40 ? 'var(--warning)' : 'var(--error)' }}>{r.correct_rate}%</strong> },
  { key: 'discrimination', label: 'Level', render: (r) => { const v: Record<string, 'green' | 'yellow' | 'red' | 'cyan'> = { easy: 'green', good: 'cyan', fair: 'yellow', hard: 'red', 'N/A': 'cyan' }; return <Badge variant={v[r.discrimination] ?? 'cyan'}>{r.discrimination}</Badge>; } },
];

export default function QuestionAnalysisPage() {
  const [data, setData] = useState<ReportData>(DEMO);
  const [selected, setSelected] = useState<QuestionRow | null>(null);

  useEffect(() => {
    fetchReport<ReportData>('question-analysis')
      .then(setData)
      .catch(() => setData(DEMO));
  }, []);

  const chartData = (selected?.options ?? []).map((o) => ({
    name: o.text.length > 30 ? o.text.substring(0, 28) + '…' : o.text,
    value: o.selection_rate,
  }));

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <div className={styles.titleGroup}>
          <h1>Analisis Soal</h1>
          <p>Kualitas butir soal berdasarkan distribusi jawaban dan tingkat kesulitan.</p>
        </div>
        <div className={styles.actions}>
          <ExportButton href={getExportCsvUrl('question-analysis')} />
        </div>
      </div>

      <div className={styles.kpiGrid}>
        <ReportCard title="Total Soal Aktif" value={data.summary.total_questions} icon={HelpCircle} />
        <ReportCard title="Rata-rata Correct Rate" value={`${data.summary.avg_correct_rate}%`} icon={Target} color="var(--success)" />
        <ReportCard title="Soal Tersulit" value={`#${data.summary.hardest_question_id ?? '-'}`} icon={XCircle} color="var(--error)" subtitle={`${data.summary.hardest_correct_rate ?? 0}% correct`} trend="negative" />
        <ReportCard title="Soal Termudah" value={`#${data.summary.easiest_question_id ?? '-'}`} icon={CheckCircle} color="var(--success)" subtitle={`${data.summary.easiest_correct_rate ?? 0}% correct`} trend="positive" />
      </div>

      <div className={styles.dualGrid}>
        <div className={styles.tableCard}>
          <div className={styles.chartHeader}>
            <h2 className={styles.sectionTitle}>Daftar Soal</h2>
          </div>
          <DataTable
            columns={[
              ...columns,
              {
                key: '_action', label: 'Detail', sortable: false,
                render: (r: QuestionRow) => (
                  <button onClick={() => setSelected(r)} style={{ background: 'transparent', border: '1px solid var(--border-subtle)', color: 'var(--primary)', padding: '4px 10px', borderRadius: 6, cursor: 'pointer', fontSize: 11 }}>
                    Lihat
                  </button>
                ),
              },
            ]}
            data={data.questions}
          />
        </div>

        <div className={styles.chartCard}>
          <div className={styles.chartHeader}>
            <h2 className={styles.sectionTitle}>
              {selected ? `Distribusi Jawaban — Soal #${selected.question_id}` : 'Pilih soal untuk lihat distribusi'}
            </h2>
          </div>
          {selected ? (
            <>
              <p style={{ fontSize: 13, color: 'var(--text-secondary)', marginBottom: 16 }}>{selected.question_text}</p>
              <SimpleBarChart data={chartData} color="var(--primary)" height={220} />
              <div style={{ marginTop: 16 }}>
                {selected.options.map((o) => (
                  <div key={o.option_id} style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '6px 0', fontSize: 12 }}>
                    <span style={{ color: o.is_correct ? 'var(--success)' : 'var(--text-tertiary)', fontWeight: o.is_correct ? 700 : 400 }}>
                      {o.is_correct ? '✓' : '○'} {o.text}
                    </span>
                    <span style={{ marginLeft: 'auto', color: 'var(--text-secondary)' }}>{o.selection_count}× ({o.selection_rate}%)</span>
                  </div>
                ))}
              </div>
            </>
          ) : (
            <div className={styles.loading}>Klik &quot;Lihat&quot; pada tabel untuk melihat detail distribusi jawaban soal.</div>
          )}
        </div>
      </div>
    </div>
  );
}
