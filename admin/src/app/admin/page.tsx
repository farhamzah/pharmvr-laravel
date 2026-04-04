import { 
  Users, 
  Vibrate, 
  Activity, 
  AlertCircle,
  PlayCircle,
  MoreVertical
} from 'lucide-react';
import StatCard from '@/components/StatCard';
import styles from './page.module.css';

export default function Home() {
  return (
    <div className={styles.dashboard}>
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>System Overview</h1>
          <p className={styles.subtitle}>Real-time performance and user engagement metrics.</p>
        </div>
        <button className="btn-primary">Generate Report</button>
      </div>

      <div className={styles.grid}>
        <StatCard 
          title="Total Patients" 
          value="1,284" 
          change="+12.5%" 
          isPositive={true} 
          icon={Users} 
        />
        <StatCard 
          title="VR Sessions" 
          value="8,422" 
          change="+8.2%" 
          isPositive={true} 
          icon={Vibrate} 
          color="#00E676"
        />
        <StatCard 
          title="Avg. Engagement" 
          value="24m 12s" 
          change="-2.4%" 
          isPositive={false} 
          icon={Activity} 
          color="#FFB74D"
        />
        <StatCard 
          title="System Alerts" 
          value="3" 
          change="Critical" 
          isPositive={false} 
          icon={AlertCircle} 
          color="#CF6679"
        />
      </div>

      <div className={styles.bottomSection}>
        <div className={`${styles.mainCard} card`}>
          <div className={styles.cardHeader}>
            <h2 className={styles.cardTitle}>Recent VR Activity</h2>
            <button className={styles.moreBtn}><MoreVertical size={20} /></button>
          </div>
          <div className={styles.placeholderChart}>
            {/* Visual representation of a chart using CSS */}
            <div className={styles.chartLine} />
            <div className={styles.chartArea} />
          </div>
        </div>

        <div className={`${styles.sideCard} card`}>
          <div className={styles.cardHeader}>
            <h2 className={styles.cardTitle}>Active Sessions</h2>
          </div>
          <ul className={styles.sessionList}>
            {[1, 2, 3, 4].map(i => (
              <li key={i} className={styles.sessionItem}>
                <div className={styles.sessionIcon}><PlayCircle size={18} /></div>
                <div className={styles.sessionInfo}>
                  <p className={styles.sessionUser}>Patient #{1000 + i}</p>
                  <p className={styles.sessionModule}>Anxiety Relief Module</p>
                </div>
                <div className={styles.pulse} />
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  );
}
