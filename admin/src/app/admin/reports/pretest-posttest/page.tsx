'use client';

import { useState, useEffect } from 'react';
import { GitCompareArrows, Users, TrendingUp, Award } from 'lucide-react';
import ReportCard from '@/components/reports/ReportCard';
import TimeRangeFilter from '@/components/reports/TimeRangeFilter';
import ExportButton from '@/components/reports/ExportButton';
import DataTable, { Badge, Column } from '@/components/reports/DataTable';
import { ComparisonBarChart } from '@/components/reports/RechartsThemed';
import { fetchReport, getExportCsvUrl } from '@/lib/api';
import styles from '../reports.module.css';

interface StudentRow {
  user_id: number;
  name: string;
  email: string;
  module: string;
  pretest_score: number | null;
  posttest_score: number | null;
  learning_gain: number | null;
  gain_category: string;
  [key: string]: unknown;
}

interface ModuleRow {
  module_id: number;
  module_title: string;
  avg_pretest: number;
  avg_posttest: number;
  avg_gain: number;
  student_count: number;
}

interface ReportData {
  summary: {
    avg_pretest: number;
    avg_posttest: number;
    avg_learning_gain: number;
    total_students: number;
  };
  per_student: StudentRow[];
  per_module: ModuleRow[];
}

// Demo data used when API is unavailable (development)
const DEMO_DATA: ReportData = {
  summary: { avg_pretest: 58.3, avg_posttest: 81.7, avg_learning_gain: 23.4, total_students: 24 },
  per_student: [
    { user_id: 1, name: 'Andi Pratama', email: 'andi@mail.com', module: 'Farmakologi Dasar', pretest_score: 45, posttest_score: 82, learning_gain: 37, gain_category: 'high' },
    { user_id: 2, name: 'Siti Rahayu', email: 'siti@mail.com', module: 'Farmakologi Dasar', pretest_score: 72, posttest_score: 90, learning_gain: 18, gain_category: 'medium' },
    { user_id: 3, name: 'Budi Santoso', email: 'budi@mail.com', module: 'Farmakologi Klinik', pretest_score: 60, posttest_score: 65, learning_gain: 5, gain_category: 'low' },
    { user_id: 4, name: 'Dewi Kartika', email: 'dewi@mail.com', module: 'Farmakologi Klinik', pretest_score: 80, posttest_score: 72, learning_gain: -8, gain_category: 'negative' },
    { user_id: 5, name: 'Rizky Maulana', email: 'rizky@mail.com', module: 'Teknologi Sediaan', pretest_score: 35, posttest_score: 78, learning_gain: 43, gain_category: 'high' },
    { user_id: 6, name: 'Putri Amelia', email: 'putri@mail.com', module: 'Teknologi Sediaan', pretest_score: 55, posttest_score: 88, learning_gain: 33, gain_category: 'high' },
  ],
  per_module: [
    { module_id: 1, module_title: 'Farmakologi Dasar', avg_pretest: 55, avg_posttest: 84, avg_gain: 29, student_count: 10 },
    { module_id: 2, module_title: 'Farmakologi Klinik', avg_pretest: 62, avg_posttest: 76, avg_gain: 14, student_count: 8 },
    { module_id: 3, module_title: 'Teknologi Sediaan', avg_pretest: 48, avg_posttest: 85, avg_gain: 37, student_count: 6 },
  ],
};

const columns: Column<StudentRow>[] = [
  { key: 'name', label: 'Nama' },
  { key: 'module', label: 'Modul' },
  { key: 'pretest_score', label: 'Pre-Test', render: (r) => r.pretest_score ?? '-' },
  { key: 'posttest_score', label: 'Post-Test', render: (r) => r.posttest_score ?? '-' },
  {
    key: 'learning_gain',
    label: 'Learning Gain',
    render: (r) => {
      if (r.learning_gain == null) return '-';
      const prefix = r.learning_gain > 0 ? '+' : '';
      return <strong style={{ color: r.learning_gain >= 0 ? 'var(--success)' : 'var(--error)' }}>{prefix}{r.learning_gain}</strong>;
    },
  },
  {
    key: 'gain_category',
    label: 'Kategori',
    render: (r) => {
      const v: Record<string, 'green' | 'yellow' | 'red' | 'cyan'> = { high: 'green', medium: 'yellow', low: 'cyan', negative: 'red', none: 'cyan' };
      return <Badge variant={v[r.gain_category] ?? 'cyan'}>{r.gain_category}</Badge>;
    },
  },
];

export default function PretestPosttestPage() {
  const [period, setPeriod] = useState('90d');
  const [data, setData] = useState<ReportData>(DEMO_DATA);

  useEffect(() => {
    fetchReport<ReportData>('pretest-posttest', { period })
      .then(setData)
      .catch(() => setData(DEMO_DATA));
  }, [period]);

  const chartData = data.per_module.map((m) => ({
    name: m.module_title.length > 20 ? m.module_title.substring(0, 18) + '…' : m.module_title,
    pretest: m.avg_pretest,
    posttest: m.avg_posttest,
  }));

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <div className={styles.titleGroup}>
          <h1>Pre-Test vs Post-Test</h1>
          <p>Perbandingan skor dan learning gain untuk mengukur efektivitas pelatihan VR.</p>
        </div>
        <div className={styles.actions}>
          <TimeRangeFilter value={period} onChange={setPeriod} />
          <ExportButton href={getExportCsvUrl('pretest-posttest', { period })} />
        </div>
      </div>

      <div className={styles.kpiGrid}>
        <ReportCard title="Rata-rata Pre-Test" value={data.summary.avg_pretest} icon={GitCompareArrows} color="var(--warning)" />
        <ReportCard title="Rata-rata Post-Test" value={data.summary.avg_posttest} icon={Award} color="var(--success)" />
        <ReportCard title="Learning Gain" value={`+${data.summary.avg_learning_gain}`} icon={TrendingUp} color="var(--primary)" subtitle="Selisih rata-rata skor" trend="positive" />
        <ReportCard title="Total Mahasiswa" value={data.summary.total_students} icon={Users} />
      </div>

      <div className={styles.chartCard}>
        <div className={styles.chartHeader}>
          <h2 className={styles.sectionTitle}>Perbandingan per Modul</h2>
        </div>
        <ComparisonBarChart data={chartData} />
      </div>

      <div className={styles.tableCard}>
        <div className={styles.chartHeader}>
          <h2 className={styles.sectionTitle}>Detail per Mahasiswa</h2>
        </div>
        <DataTable columns={columns} data={data.per_student} />
      </div>
    </div>
  );
}
