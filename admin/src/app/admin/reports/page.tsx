import Link from 'next/link';
import {
  GitCompareArrows,
  HelpCircle,
  Filter,
  Vibrate,
  Bot,
  TrendingUp,
} from 'lucide-react';
import styles from './reports.module.css';

const reports = [
  {
    title: 'Pre-Test vs Post-Test',
    description: 'Bandingkan skor pre-test dan post-test untuk mengukur efektivitas pelatihan VR (learning gain).',
    href: '/admin/reports/pretest-posttest',
    icon: GitCompareArrows,
    color: '#00E676',
  },
  {
    title: 'Analisis Soal',
    description: 'Identifikasi soal terlalu mudah/sulit, distribusi jawaban, dan discriminating power setiap butir soal.',
    href: '/admin/reports/question-analysis',
    icon: HelpCircle,
    color: '#FFB74D',
  },
  {
    title: 'Completion Funnel',
    description: 'Lacak drop-off rate dari Pre-Test → VR Simulation → Post-Test untuk setiap modul pelatihan.',
    href: '/admin/reports/completion-funnel',
    icon: Filter,
    color: '#00E5FF',
  },
  {
    title: 'VR Performance',
    description: 'Metrik VR session: akurasi, kecepatan, durasi, breach count, dan completion rate per modul.',
    href: '/admin/reports/vr-performance',
    icon: Vibrate,
    color: '#64B5F6',
  },
  {
    title: 'AI Usage & Cost',
    description: 'Monitor penggunaan AI: token consumption, latency, cost estimation, dan breakdown per interaction type.',
    href: '/admin/reports/ai-usage',
    icon: Bot,
    color: '#CE93D8',
  },
  {
    title: 'Trend Report',
    description: 'Grafik tren registrasi, assessment, VR session, dan AI interaction dari waktu ke waktu.',
    href: '/admin/reports/trends',
    icon: TrendingUp,
    color: '#FF8A65',
  },
];

export default function ReportsHub() {
  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <div className={styles.titleGroup}>
          <h1>Reports & Analytics</h1>
          <p>Pilih report untuk melihat analisis mendalam tentang performa platform.</p>
        </div>
      </div>

      <div className={styles.hubGrid}>
        {reports.map((r) => (
          <Link key={r.href} href={r.href} className={styles.hubCard}>
            <div className={styles.hubIcon} style={{ color: r.color, background: `${r.color}18` }}>
              <r.icon size={22} />
            </div>
            <h3>{r.title}</h3>
            <p>{r.description}</p>
          </Link>
        ))}
      </div>
    </div>
  );
}
