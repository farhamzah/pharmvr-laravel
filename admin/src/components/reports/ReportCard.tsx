import { LucideIcon } from 'lucide-react';
import styles from './ReportCard.module.css';

interface ReportCardProps {
  title: string;
  value: string | number;
  subtitle?: string;
  icon: LucideIcon;
  color?: string;
  trend?: 'positive' | 'negative' | 'neutral';
}

export default function ReportCard({ title, value, subtitle, icon: Icon, color = 'var(--primary)', trend }: ReportCardProps) {
  return (
    <div className={styles.card}>
      <div className={styles.header}>
        <div className={styles.iconWrap} style={{ color, background: `${color}18` }}>
          <Icon size={20} />
        </div>
        <span className={styles.label}>{title}</span>
      </div>
      <p className={styles.value}>{value}</p>
      {subtitle && (
        <p className={`${styles.sub} ${trend === 'positive' ? styles.positive : trend === 'negative' ? styles.negative : ''}`}>
          {subtitle}
        </p>
      )}
    </div>
  );
}
