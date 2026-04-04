'use client';

import { useState, useEffect } from 'react';
import { Vibrate, Target, Zap, Clock } from 'lucide-react';
import ReportCard from '@/components/reports/ReportCard';
import TimeRangeFilter from '@/components/reports/TimeRangeFilter';
import ExportButton from '@/components/reports/ExportButton';
import DataTable, { Badge, Column } from '@/components/reports/DataTable';
import { MultiBarChart } from '@/components/reports/RechartsThemed';
import { fetchReport, getExportCsvUrl } from '@/lib/api';
import styles from '../reports.module.css';

interface SessionRow {
  session_id: number;
  user_name: string;
  module: string;
  status: string;
  accuracy: number;
  speed: number;
  duration_min: number;
  breach_count: number;
  date: string;
  [key: string]: unknown;
}

interface ModulePerf {
  module_id: number;
  module_title: string;
  session_count: number;
  avg_accuracy: number;
  avg_speed: number;
  avg_duration_min: number;
  avg_breach_count: number;
}

interface ReportData {
  summary: { total_sessions: number; avg_accuracy: number; avg_speed: number; avg_duration_min: number; avg_breach_count: number; completion_rate: number };
  per_module: ModulePerf[];
  recent_sessions: SessionRow[];
}

const DEMO: ReportData = {
  summary: { total_sessions: 156, avg_accuracy: 76.4, avg_speed: 68.9, avg_duration_min: 22.3, avg_breach_count: 1.4, completion_rate: 87.2 },
  per_module: [
    { module_id: 1, module_title: 'Farmakologi Dasar', session_count: 65, avg_accuracy: 80.2, avg_speed: 72.5, avg_duration_min: 20.1, avg_breach_count: 0.8 },
    { module_id: 2, module_title: 'Farmakologi Klinik', session_count: 52, avg_accuracy: 74.8, avg_speed: 66.3, avg_duration_min: 24.5, avg_breach_count: 1.6 },
    { module_id: 3, module_title: 'Teknologi Sediaan', session_count: 39, avg_accuracy: 72.1, avg_speed: 65.8, avg_duration_min: 23.8, avg_breach_count: 2.1 },
  ],
  recent_sessions: [
    { session_id: 101, user_name: 'Andi Pratama', module: 'Farmakologi Dasar', status: 'completed', accuracy: 88, speed: 75, duration_min: 18.5, breach_count: 0, date: '2026-03-31 14:20' },
    { session_id: 102, user_name: 'Siti Rahayu', module: 'Farmakologi Klinik', status: 'completed', accuracy: 72, speed: 60, duration_min: 28.3, breach_count: 2, date: '2026-03-31 10:15' },
    { session_id: 103, user_name: 'Budi Santoso', module: 'Teknologi Sediaan', status: 'interrupted', accuracy: 45, speed: 40, duration_min: 12.0, breach_count: 3, date: '2026-03-30 16:40' },
    { session_id: 104, user_name: 'Dewi Kartika', module: 'Farmakologi Dasar', status: 'completed', accuracy: 92, speed: 85, duration_min: 15.2, breach_count: 0, date: '2026-03-30 09:00' },
  ],
};

const columns: Column<SessionRow>[] = [
  { key: 'user_name', label: 'User' },
  { key: 'module', label: 'Modul' },
  { key: 'status', label: 'Status', render: (r) => <Badge variant={r.status === 'completed' ? 'green' : r.status === 'active' ? 'cyan' : 'red'}>{r.status}</Badge> },
  { key: 'accuracy', label: 'Accuracy', render: (r) => <strong style={{ color: r.accuracy >= 70 ? 'var(--success)' : 'var(--warning)' }}>{r.accuracy}%</strong> },
  { key: 'speed', label: 'Speed' },
  { key: 'duration_min', label: 'Durasi', render: (r) => `${r.duration_min} min` },
  { key: 'breach_count', label: 'Breach', render: (r) => <span style={{ color: r.breach_count > 2 ? 'var(--error)' : 'var(--text-secondary)' }}>{r.breach_count}</span> },
  { key: 'date', label: 'Tanggal' },
];

export default function VrPerformancePage() {
  const [period, setPeriod] = useState('90d');
  const [data, setData] = useState<ReportData>(DEMO);

  useEffect(() => {
    fetchReport<ReportData>('vr-performance', { period })
      .then(setData)
      .catch(() => setData(DEMO));
  }, [period]);

  const chartData = data.per_module.map((m) => ({
    name: m.module_title.length > 18 ? m.module_title.substring(0, 16) + '…' : m.module_title,
    accuracy: m.avg_accuracy,
    speed: m.avg_speed,
  }));

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <div className={styles.titleGroup}>
          <h1>VR Performance</h1>
          <p>Metrik performa VR session: akurasi, kecepatan, durasi, dan breach count.</p>
        </div>
        <div className={styles.actions}>
          <TimeRangeFilter value={period} onChange={setPeriod} />
          <ExportButton href={getExportCsvUrl('vr-performance', { period })} />
        </div>
      </div>

      <div className={styles.kpiGrid}>
        <ReportCard title="Total Sessions" value={data.summary.total_sessions} icon={Vibrate} />
        <ReportCard title="Avg. Accuracy" value={`${data.summary.avg_accuracy}%`} icon={Target} color="var(--success)" />
        <ReportCard title="Avg. Speed Score" value={data.summary.avg_speed} icon={Zap} color="var(--warning)" />
        <ReportCard title="Avg. Durasi" value={`${data.summary.avg_duration_min}m`} icon={Clock} color="var(--info)" subtitle={`Breach avg: ${data.summary.avg_breach_count}`} />
      </div>

      <div className={styles.chartCard}>
        <div className={styles.chartHeader}>
          <h2 className={styles.sectionTitle}>Accuracy & Speed per Modul</h2>
        </div>
        <MultiBarChart
          data={chartData}
          bars={[
            { key: 'accuracy', name: 'Accuracy', color: '#00E676' },
            { key: 'speed', name: 'Speed', color: '#FFB74D' },
          ]}
        />
      </div>

      <div className={styles.tableCard}>
        <div className={styles.chartHeader}>
          <h2 className={styles.sectionTitle}>Recent Sessions</h2>
        </div>
        <DataTable columns={columns} data={data.recent_sessions} />
      </div>
    </div>
  );
}
