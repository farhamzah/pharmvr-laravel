import { 
  ShieldAlert, 
  User, 
  Cpu, 
  Globe,
  Download,
  Calendar
} from 'lucide-react';
import styles from './logs.module.css';

const logs = [
  { id: 1, event: 'User Login', user: 'Admin User', target: 'System', time: 'Mar 14, 16:30', type: 'Auth', status: 'Success' },
  { id: 2, event: 'Configuration Change', user: 'Dr. Sarah', target: 'VR Readiness', time: 'Mar 14, 15:45', type: 'System', status: 'Success' },
  { id: 3, event: 'Failed Password Attempt', user: '192.168.1.44', target: 'Marcus W.', time: 'Mar 14, 14:20', type: 'Security', status: 'Warning' },
  { id: 4, event: 'Data Export', user: 'Admin User', target: 'Analytics Data', time: 'Mar 14, 12:05', type: 'Data', status: 'Success' },
  { id: 5, event: 'Session Terminated', user: 'System', target: 'Patient #1024', time: 'Mar 14, 10:15', type: 'Session', status: 'Info' },
];

export default function AuditLogsPage() {
  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Audit Logs</h1>
          <p className={styles.subtitle}>Comprehensive record of all administrative and system activities.</p>
        </div>
        <button className={styles.downloadBtn}>
          <Download size={18} />
          <span>Export Logs</span>
        </button>
      </div>

      <div className={`${styles.logList} card`}>
        <div className={styles.listHeader}>
          <div className={styles.column}>Event / Type</div>
          <div className={styles.column}>Actor</div>
          <div className={styles.column}>Target</div>
          <div className={styles.column}>Timestamp</div>
          <div className={styles.column}>Status</div>
        </div>

        <div className={styles.listBody}>
          {logs.map((log) => (
            <div key={log.id} className={styles.logItem}>
              <div className={styles.mainInfo}>
                <div className={styles.iconBox}>
                  {log.type === 'Auth' && <User size={16} />}
                  {log.type === 'System' && <Cpu size={16} />}
                  {log.type === 'Security' && <ShieldAlert size={16} />}
                  {log.type === 'Data' && <Download size={16} />}
                  {log.type === 'Session' && <Globe size={16} />}
                </div>
                <div>
                  <p className={styles.eventName}>{log.event}</p>
                  <p className={styles.eventType}>{log.type}</p>
                </div>
              </div>
              
              <div className={styles.actor}>{log.user}</div>
              <div className={styles.target}>{log.target}</div>
              <div className={styles.time}>
                <Calendar size={12} />
                <span>{log.time}</span>
              </div>

              <div className={styles.statusCol}>
                <span className={`${styles.statusTag} ${styles[log.status.toLowerCase()]}`}>
                  {log.status}
                </span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
