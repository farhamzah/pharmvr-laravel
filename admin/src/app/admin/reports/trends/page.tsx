'use client';

import { useState, useEffect, useMemo } from 'react';
import { TrendingUp, UserPlus, ClipboardCheck, Vibrate, Bot } from 'lucide-react';
import ReportCard from '@/components/reports/ReportCard';
import TimeRangeFilter from '@/components/reports/TimeRangeFilter';
import ExportButton from '@/components/reports/ExportButton';
import { MultiAreaChart } from '@/components/reports/RechartsThemed';
import { fetchReport, getExportCsvUrl } from '@/lib/api';
import styles from '../reports.module.css';

interface DateCount { date: string; count: number; avg_score?: number }

interface ReportData {
  period: string;
  registrations: DateCount[];
  assessments: DateCount[];
  vr_sessions: DateCount[];
  ai_interactions: DateCount[];
}

function generateDemoDates(days: number): string[] {
  const dates: string[] = [];
  for (let i = days - 1; i >= 0; i--) {
    const d = new Date();
    d.setDate(d.getDate() - i);
    dates.push(d.toISOString().split('T')[0]);
  }
  return dates;
}

function makeDemoData(days: number): ReportData {
  const dates = generateDemoDates(days);
  return {
    period: `${days}d`,
    registrations: dates.map((d) => ({ date: d, count: Math.floor(Math.random() * 8) + 1 })),
    assessments: dates.map((d) => ({ date: d, count: Math.floor(Math.random() * 15) + 3, avg_score: Math.floor(Math.random() * 30) + 55 })),
    vr_sessions: dates.map((d) => ({ date: d, count: Math.floor(Math.random() * 12) + 2 })),
    ai_interactions: dates.map((d) => ({ date: d, count: Math.floor(Math.random() * 30) + 5 })),
  };
}

export default function TrendsPage() {
  const [period, setPeriod] = useState('30d');
  const [data, setData] = useState<ReportData>(() => makeDemoData(30));

  useEffect(() => {
    const days = period === '7d' ? 7 : period === '30d' ? 30 : period === '90d' ? 90 : 365;
    fetchReport<ReportData>('trends', { period })
      .then(setData)
      .catch(() => setData(makeDemoData(days)));
  }, [period]);

  const mergedData = useMemo(() => {
    const regs = new Map(data.registrations.map((d) => [d.date, d.count]));
    const assess = new Map(data.assessments.map((d) => [d.date, d.count]));
    const vr = new Map(data.vr_sessions.map((d) => [d.date, d.count]));
    const ai = new Map(data.ai_interactions.map((d) => [d.date, d.count]));

    const allDates = [...new Set([...regs.keys(), ...assess.keys(), ...vr.keys(), ...ai.keys()])].sort();

    return allDates.map((date) => ({
      date: date.substring(5), // MM-DD
      registrations: regs.get(date) ?? 0,
      assessments: assess.get(date) ?? 0,
      vr_sessions: vr.get(date) ?? 0,
      ai_interactions: ai.get(date) ?? 0,
    }));
  }, [data]);

  const totals = useMemo(() => ({
    regs: data.registrations.reduce((s, d) => s + d.count, 0),
    assess: data.assessments.reduce((s, d) => s + d.count, 0),
    vr: data.vr_sessions.reduce((s, d) => s + d.count, 0),
    ai: data.ai_interactions.reduce((s, d) => s + d.count, 0),
  }), [data]);

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <div className={styles.titleGroup}>
          <h1>Trend Report</h1>
          <p>Tren aktivitas platform dari waktu ke waktu.</p>
        </div>
        <div className={styles.actions}>
          <TimeRangeFilter value={period} onChange={setPeriod} />
          <ExportButton href={getExportCsvUrl('trends', { period })} />
        </div>
      </div>

      <div className={styles.kpiGrid}>
        <ReportCard title="Registrasi" value={totals.regs} icon={UserPlus} subtitle={`Periode ${period}`} />
        <ReportCard title="Assessment" value={totals.assess} icon={ClipboardCheck} color="var(--success)" subtitle={`Periode ${period}`} />
        <ReportCard title="VR Sessions" value={totals.vr} icon={Vibrate} color="var(--warning)" subtitle={`Periode ${period}`} />
        <ReportCard title="AI Interactions" value={totals.ai} icon={Bot} color="#CE93D8" subtitle={`Periode ${period}`} />
      </div>

      <div className={styles.chartCard}>
        <div className={styles.chartHeader}>
          <h2 className={styles.sectionTitle}>Semua Metrik</h2>
        </div>
        <MultiAreaChart
          data={mergedData}
          lines={[
            { key: 'registrations', color: '#00E5FF', name: 'Registrasi' },
            { key: 'assessments', color: '#00E676', name: 'Assessment' },
            { key: 'vr_sessions', color: '#FFB74D', name: 'VR Sessions' },
            { key: 'ai_interactions', color: '#CE93D8', name: 'AI Interactions' },
          ]}
          height={350}
        />
      </div>

      <div className={styles.dualGrid}>
        <div className={styles.chartCard}>
          <h2 className={styles.sectionTitle} style={{ marginBottom: 16 }}>Registrasi & Assessment</h2>
          <MultiAreaChart
            data={mergedData}
            lines={[
              { key: 'registrations', color: '#00E5FF', name: 'Registrasi' },
              { key: 'assessments', color: '#00E676', name: 'Assessment' },
            ]}
            height={250}
          />
        </div>
        <div className={styles.chartCard}>
          <h2 className={styles.sectionTitle} style={{ marginBottom: 16 }}>VR & AI Activity</h2>
          <MultiAreaChart
            data={mergedData}
            lines={[
              { key: 'vr_sessions', color: '#FFB74D', name: 'VR Sessions' },
              { key: 'ai_interactions', color: '#CE93D8', name: 'AI Interactions' },
            ]}
            height={250}
          />
        </div>
      </div>
    </div>
  );
}
