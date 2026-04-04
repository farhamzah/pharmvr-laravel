import { ArrowUpRight, ArrowDownRight, LucideIcon } from 'lucide-react';
import styles from './StatCard.module.css';

interface StatCardProps {
  title: string;
  value: string;
  change: string;
  isPositive: boolean;
  icon: LucideIcon;
  color?: string;
}

export default function StatCard({ title, value, change, isPositive, icon: Icon, color }: StatCardProps) {
  return (
    <div className="card">
      <div className={styles.header}>
        <div className={styles.iconWrapper} style={{ color: color || 'var(--primary)', background: `${color || 'var(--primary)'}15` }}>
          <Icon size={20} />
        </div>
        <div className={`${styles.badge} ${isPositive ? styles.positive : styles.negative}`}>
          {isPositive ? <ArrowUpRight size={14} /> : <ArrowDownRight size={14} />}
          {change}
        </div>
      </div>
      
      <div className={styles.content}>
        <h3 className={styles.title}>{title}</h3>
        <p className={styles.value}>{value}</p>
      </div>
    </div>
  );
}
