import { 
  Activity, 
  TrendingUp, 
  Zap, 
  Clock,
  ExternalLink,
  ChevronRight
} from 'lucide-react';
import styles from './analytics.module.css';

export default function AnalyticsPage() {
  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Analytics & Monitoring</h1>
          <p className={styles.subtitle}>Deep dive into user engagement and system health.</p>
        </div>
        <div className={styles.timeRange}>
          <button className={styles.rangeBtn}>24 Hours</button>
          <button className={`${styles.rangeBtn} ${styles.active}`}>7 Days</button>
          <button className={styles.rangeBtn}>30 Days</button>
        </div>
      </div>

      <div className={styles.chartsGrid}>
        <div className={`${styles.chartCard} card`}>
          <div className={styles.cardHeader}>
            <div className={styles.labelGroup}>
              <TrendingUp size={18} className={styles.icon} />
              <h2 className={styles.cardTitle}>User Growth</h2>
            </div>
            <span className={styles.value}>+18.4%</span>
          </div>
          <div className={styles.chartPlaceholder}>
            <div className={styles.bars}>
              {[40, 60, 45, 80, 55, 90, 75].map((h, i) => (
                <div key={i} className={styles.bar} style={{ height: `${h}%` }} />
              ))}
            </div>
          </div>
        </div>

        <div className={`${styles.chartCard} card`}>
          <div className={styles.cardHeader}>
            <div className={styles.labelGroup}>
              <Zap size={18} className={styles.icon} />
              <h2 className={styles.cardTitle}>System Load</h2>
            </div>
            <span className={styles.value}>Normal</span>
          </div>
          <div className={styles.chartPlaceholder}>
            <div className={styles.waveContainer}>
              <div className={styles.wave} />
            </div>
          </div>
        </div>
      </div>

      <div className={styles.detailsSection}>
        <div className={`${styles.metricsCard} card`}>
          <h3 className={styles.sectionTitle}>Engagement Breakdown</h3>
          <div className={styles.metricRow}>
            <span>Avg. Session Length</span>
            <span className={styles.metricValue}>24m 32s</span>
          </div>
          <div className={styles.metricRow}>
            <span>Completion Rate</span>
            <span className={styles.metricValue}>92%</span>
          </div>
          <div className={styles.metricRow}>
            <span>Active VR Devices</span>
            <span className={styles.metricValue}>42 Units</span>
          </div>
        </div>

        <div className={`${styles.activeModulesCard} card`}>
          <h3 className={styles.sectionTitle}>Top Learning Modules</h3>
          <div className={styles.moduleList}>
            {[
              { name: 'Anxiety Exposure Therapy', count: 428, color: 'var(--primary)' },
              { name: 'Phobia Desensitization', count: 312, color: 'var(--success)' },
              { name: 'Mindfulness VR', count: 184, color: 'var(--warning)' }
            ].map(m => (
              <div key={m.name} className={styles.moduleItem}>
                <div className={styles.moduleInfo}>
                  <p className={styles.moduleName}>{m.name}</p>
                  <p className={styles.moduleCount}>{m.count} Sessions</p>
                </div>
                <div className={styles.moduleBar}>
                  <div className={styles.moduleProgress} style={{ width: `${(m.count / 428) * 100}%`, background: m.color }} />
                </div>
                <ChevronRight size={16} className={styles.arrow} />
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
