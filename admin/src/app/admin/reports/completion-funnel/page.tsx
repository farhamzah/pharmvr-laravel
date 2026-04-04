'use client';

import { useState, useEffect } from 'react';
import { Filter, Users, Award, AlertTriangle } from 'lucide-react';
import ReportCard from '@/components/reports/ReportCard';
import ExportButton from '@/components/reports/ExportButton';
import { fetchReport, getExportCsvUrl } from '@/lib/api';
import styles from '../reports.module.css';

interface ModuleFunnel {
  module_id: number;
  module_title: string;
  total_enrolled: number;
  started_pretest: number;
  completed_pretest: number;
  started_vr: number;
  completed_vr: number;
  started_posttest: number;
  passed_posttest: number;
  drop_off_rates: {
    pretest_to_vr: number;
    vr_to_posttest: number;
    posttest_pass: number;
  };
}

interface ReportData {
  modules: ModuleFunnel[];
  summary: {
    total_enrolled: number;
    total_completed: number;
    overall_completion: number;
  };
}

const DEMO: ReportData = {
  summary: { total_enrolled: 85, total_completed: 42, overall_completion: 49.4 },
  modules: [
    { module_id: 1, module_title: 'Farmakologi Dasar', total_enrolled: 35, started_pretest: 34, completed_pretest: 32, started_vr: 30, completed_vr: 28, started_posttest: 25, passed_posttest: 22, drop_off_rates: { pretest_to_vr: 6.3, vr_to_posttest: 10.7, posttest_pass: 12.0 } },
    { module_id: 2, module_title: 'Farmakologi Klinik', total_enrolled: 28, started_pretest: 26, completed_pretest: 24, started_vr: 20, completed_vr: 16, started_posttest: 14, passed_posttest: 12, drop_off_rates: { pretest_to_vr: 16.7, vr_to_posttest: 12.5, posttest_pass: 14.3 } },
    { module_id: 3, module_title: 'Teknologi Sediaan', total_enrolled: 22, started_pretest: 20, completed_pretest: 18, started_vr: 15, completed_vr: 14, started_posttest: 12, passed_posttest: 8, drop_off_rates: { pretest_to_vr: 16.7, vr_to_posttest: 14.3, posttest_pass: 33.3 } },
  ],
};

const FUNNEL_STEPS: { key: keyof ModuleFunnel; label: string; color: string }[] = [
  { key: 'total_enrolled', label: 'Enrolled', color: '#78909C' },
  { key: 'completed_pretest', label: 'Pre-Test Selesai', color: '#FFB74D' },
  { key: 'started_vr', label: 'VR Dimulai', color: '#64B5F6' },
  { key: 'completed_vr', label: 'VR Selesai', color: '#00E5FF' },
  { key: 'started_posttest', label: 'Post-Test Dimulai', color: '#CE93D8' },
  { key: 'passed_posttest', label: 'Post-Test Lulus', color: '#00E676' },
];

export default function CompletionFunnelPage() {
  const [data, setData] = useState<ReportData>(DEMO);

  useEffect(() => {
    fetchReport<ReportData>('completion-funnel')
      .then(setData)
      .catch(() => setData(DEMO));
  }, []);

  // Aggregate funnel across all modules
  const aggregated = FUNNEL_STEPS.map((step) => ({
    ...step,
    value: data.modules.reduce((sum, m) => sum + (m[step.key] as number), 0),
  }));
  const maxVal = Math.max(...aggregated.map((s) => s.value), 1);

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <div className={styles.titleGroup}>
          <h1>Completion Funnel</h1>
          <p>Lacak drop-off rate dari setiap tahap training journey.</p>
        </div>
        <div className={styles.actions}>
          <ExportButton href={getExportCsvUrl('completion-funnel')} />
        </div>
      </div>

      <div className={styles.kpiGrid}>
        <ReportCard title="Total Enrolled" value={data.summary.total_enrolled} icon={Users} />
        <ReportCard title="Total Completed" value={data.summary.total_completed} icon={Award} color="var(--success)" />
        <ReportCard title="Completion Rate" value={`${data.summary.overall_completion}%`} icon={Filter} color="var(--primary)" />
        <ReportCard title="Drop-off Rate" value={`${(100 - data.summary.overall_completion).toFixed(1)}%`} icon={AlertTriangle} color="var(--error)" />
      </div>

      {/* Aggregated Funnel */}
      <div className={styles.chartCard}>
        <div className={styles.chartHeader}>
          <h2 className={styles.sectionTitle}>Funnel Keseluruhan</h2>
        </div>
        <div className={styles.funnelContainer}>
          {aggregated.map((step, i) => {
            const widthPct = Math.max((step.value / maxVal) * 100, 15);
            const prev = i > 0 ? aggregated[i - 1].value : step.value;
            const drop = prev > 0 ? ((prev - step.value) / prev * 100).toFixed(1) : '0';
            return (
              <div key={step.key as string} className={styles.funnelStep}>
                <span className={styles.funnelLabel}>{step.label}</span>
                <div className={styles.funnelBar} style={{ width: `${widthPct}%`, background: step.color, maxWidth: 400, minWidth: 60 }}>
                  {step.value}
                </div>
                <span className={styles.funnelValue}>{step.value} users</span>
                {i > 0 && <span className={styles.funnelDrop}>↓ {drop}%</span>}
              </div>
            );
          })}
        </div>
      </div>

      {/* Per Module Breakdown */}
      <div className={styles.section}>
        <h2 className={styles.sectionTitle}>Breakdown per Modul</h2>
        {data.modules.map((m) => {
          const mMax = Math.max(m.total_enrolled, 1);
          return (
            <div key={m.module_id} className={styles.chartCard} style={{ paddingBottom: 12 }}>
              <h3 style={{ fontSize: 14, fontWeight: 600, marginBottom: 12, color: 'var(--text-primary)' }}>{m.module_title}</h3>
              {FUNNEL_STEPS.map((step) => {
                const val = m[step.key] as number;
                const wPct = Math.max((val / mMax) * 100, 8);
                return (
                  <div key={step.key as string} style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 6 }}>
                    <span style={{ width: 130, fontSize: 11, color: 'var(--text-secondary)', textAlign: 'right', flexShrink: 0 }}>{step.label}</span>
                    <div style={{ flex: 1, height: 20, background: 'var(--background)', borderRadius: 4, overflow: 'hidden' }}>
                      <div style={{ width: `${wPct}%`, height: '100%', background: step.color, borderRadius: 4, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 10, fontWeight: 600, color: 'var(--background)', minWidth: 24 }}>{val}</div>
                    </div>
                  </div>
                );
              })}
              <div style={{ display: 'flex', gap: 24, marginTop: 8, fontSize: 11, color: 'var(--text-tertiary)' }}>
                <span>Pre→VR drop: <b style={{ color: 'var(--error)' }}>{m.drop_off_rates.pretest_to_vr}%</b></span>
                <span>VR→Post drop: <b style={{ color: 'var(--error)' }}>{m.drop_off_rates.vr_to_posttest}%</b></span>
                <span>Post fail: <b style={{ color: 'var(--error)' }}>{m.drop_off_rates.posttest_pass}%</b></span>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
