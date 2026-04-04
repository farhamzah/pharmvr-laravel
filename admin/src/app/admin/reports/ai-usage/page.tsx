'use client';

import { useState, useEffect } from 'react';
import { Bot, Coins, Timer, ShieldCheck, MessageSquare } from 'lucide-react';
import ReportCard from '@/components/reports/ReportCard';
import TimeRangeFilter from '@/components/reports/TimeRangeFilter';
import ExportButton from '@/components/reports/ExportButton';
import { SimpleBarChart, SimpleLineChart } from '@/components/reports/RechartsThemed';
import { fetchReport, getExportCsvUrl } from '@/lib/api';
import styles from '../reports.module.css';

interface TypeUsage {
  type: string;
  count: number;
  tokens: number;
  avg_latency_ms: number;
}

interface DailyUsage {
  date: string;
  interactions: number;
  tokens: number;
}

interface ReportData {
  summary: { total_interactions: number; total_tokens: number; estimated_cost_usd: number; avg_latency_ms: number; safe_response_rate: number };
  by_type: TypeUsage[];
  daily_usage: DailyUsage[];
}

const DEMO: ReportData = {
  summary: { total_interactions: 1842, total_tokens: 3250000, estimated_cost_usd: 0.49, avg_latency_ms: 920, safe_response_rate: 98.7 },
  by_type: [
    { type: 'chat', count: 980, tokens: 1800000, avg_latency_ms: 850 },
    { type: 'vr_hint', count: 520, tokens: 900000, avg_latency_ms: 650 },
    { type: 'avatar_guide', count: 220, tokens: 380000, avg_latency_ms: 1200 },
    { type: 'vr_feedback', count: 122, tokens: 170000, avg_latency_ms: 1100 },
  ],
  daily_usage: [
    { date: '2026-03-25', interactions: 45, tokens: 75000 },
    { date: '2026-03-26', interactions: 62, tokens: 110000 },
    { date: '2026-03-27', interactions: 38, tokens: 65000 },
    { date: '2026-03-28', interactions: 78, tokens: 135000 },
    { date: '2026-03-29', interactions: 55, tokens: 92000 },
    { date: '2026-03-30', interactions: 91, tokens: 158000 },
    { date: '2026-03-31', interactions: 70, tokens: 120000 },
  ],
};

function formatTokens(n: number): string {
  if (n >= 1000000) return `${(n / 1000000).toFixed(1)}M`;
  if (n >= 1000) return `${(n / 1000).toFixed(0)}K`;
  return n.toString();
}

const TYPE_COLORS: Record<string, string> = {
  chat: '#00E5FF',
  vr_hint: '#00E676',
  avatar_guide: '#CE93D8',
  vr_feedback: '#FFB74D',
};

export default function AiUsagePage() {
  const [period, setPeriod] = useState('30d');
  const [data, setData] = useState<ReportData>(DEMO);

  useEffect(() => {
    fetchReport<ReportData>('ai-usage', { period })
      .then(setData)
      .catch(() => setData(DEMO));
  }, [period]);

  const typeChartData = data.by_type.map((t) => ({
    name: t.type.replace('_', ' '),
    value: t.count,
  }));

  const dailyChartData = data.daily_usage.map((d) => ({
    date: d.date.substring(5), // MM-DD
    tokens: d.tokens,
    interactions: d.interactions,
  }));

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <div className={styles.titleGroup}>
          <h1>AI Usage & Cost</h1>
          <p>Monitor konsumsi token AI, latency, dan biaya estimasi.</p>
        </div>
        <div className={styles.actions}>
          <TimeRangeFilter value={period} onChange={setPeriod} />
          <ExportButton href={getExportCsvUrl('ai-usage', { period })} />
        </div>
      </div>

      <div className={styles.kpiGrid}>
        <ReportCard title="Total Interactions" value={data.summary.total_interactions.toLocaleString()} icon={MessageSquare} />
        <ReportCard title="Total Tokens" value={formatTokens(data.summary.total_tokens)} icon={Bot} color="var(--primary)" />
        <ReportCard title="Est. Cost" value={`$${data.summary.estimated_cost_usd}`} icon={Coins} color="var(--warning)" />
        <ReportCard title="Avg. Latency" value={`${data.summary.avg_latency_ms}ms`} icon={Timer} color="var(--info)" subtitle={`Safe: ${data.summary.safe_response_rate}%`} trend="positive" />
      </div>

      <div className={styles.dualGrid}>
        <div className={styles.chartCard}>
          <div className={styles.chartHeader}>
            <h2 className={styles.sectionTitle}>Interactions per Type</h2>
          </div>
          <SimpleBarChart data={typeChartData} color="var(--primary)" height={260} />
          {/* Detail list */}
          <div style={{ marginTop: 16, display: 'flex', flexDirection: 'column', gap: 8 }}>
            {data.by_type.map((t) => (
              <div key={t.type} style={{ display: 'flex', alignItems: 'center', gap: 10, fontSize: 12, padding: '4px 0', borderBottom: '1px solid var(--border-subtle)' }}>
                <div style={{ width: 10, height: 10, borderRadius: '50%', background: TYPE_COLORS[t.type] ?? 'var(--primary)', flexShrink: 0 }} />
                <span style={{ flex: 1, color: 'var(--text-secondary)' }}>{t.type.replace('_', ' ')}</span>
                <span style={{ color: 'var(--text-primary)', fontWeight: 600 }}>{t.count}×</span>
                <span style={{ color: 'var(--text-tertiary)', width: 80, textAlign: 'right' }}>{formatTokens(t.tokens)} tkn</span>
                <span style={{ color: 'var(--text-tertiary)', width: 70, textAlign: 'right' }}>{t.avg_latency_ms}ms</span>
              </div>
            ))}
          </div>
        </div>

        <div className={styles.chartCard}>
          <div className={styles.chartHeader}>
            <h2 className={styles.sectionTitle}>Daily Token Usage</h2>
          </div>
          <SimpleLineChart data={dailyChartData} dataKey="tokens" color="#00E5FF" height={260} />
          <div style={{ marginTop: 12 }}>
            <h3 className={styles.sectionTitle} style={{ fontSize: 13, marginBottom: 8 }}>Daily Interactions</h3>
            <SimpleLineChart data={dailyChartData} dataKey="interactions" color="#00E676" height={180} />
          </div>
        </div>
      </div>
    </div>
  );
}
