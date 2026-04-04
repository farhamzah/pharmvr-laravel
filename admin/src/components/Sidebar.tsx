import Link from 'next/link';
import Image from 'next/image';
import { 
  LayoutDashboard, 
  Users, 
  Vibrate, 
  BarChart3, 
  History, 
  Settings,
  LogOut,
  FileBarChart,
  GitCompareArrows,
  HelpCircle,
  Filter,
  Bot,
  TrendingUp,
} from 'lucide-react';
import styles from './Sidebar.module.css';

const navItems = [
  { name: 'Dashboard', icon: LayoutDashboard, href: '/admin' },
  { name: 'User Management', icon: Users, href: '/admin/users' },
  { name: 'VR Sessions', icon: Vibrate, href: '/admin/sessions' },
  { name: 'Analytics', icon: BarChart3, href: '/admin/analytics' },
  { name: 'Audit Logs', icon: History, href: '/admin/logs' },
];

const reportItems = [
  { name: 'Reports Hub', icon: FileBarChart, href: '/admin/reports' },
  { name: 'Pre vs Post Test', icon: GitCompareArrows, href: '/admin/reports/pretest-posttest' },
  { name: 'Analisis Soal', icon: HelpCircle, href: '/admin/reports/question-analysis' },
  { name: 'Completion Funnel', icon: Filter, href: '/admin/reports/completion-funnel' },
  { name: 'VR Performance', icon: Vibrate, href: '/admin/reports/vr-performance' },
  { name: 'AI Usage', icon: Bot, href: '/admin/reports/ai-usage' },
  { name: 'Trends', icon: TrendingUp, href: '/admin/reports/trends' },
];

export default function Sidebar() {
  return (
    <aside className={styles.sidebar}>
      <div className={styles.logoContainer}>
        <Image 
          src="/brand/logo.png" 
          alt="PharmVR Logo" 
          width={40} 
          height={40} 
          className={styles.logo}
        />
        <span className={styles.brandName}>PharmVR <span className={styles.adminTag}>Admin</span></span>
      </div>

      <nav className={styles.nav}>
        <div className={styles.navGroup}>
          <p className={styles.groupLabel}>Control Center</p>
          {navItems.slice(0, 3).map((item) => (
            <Link key={item.name} href={item.href} className={styles.navLink}>
              <item.icon size={20} />
              <span>{item.name}</span>
            </Link>
          ))}
        </div>

        <div className={styles.navGroup}>
          <p className={styles.groupLabel}>Insights</p>
          {navItems.slice(3).map((item) => (
            <Link key={item.name} href={item.href} className={styles.navLink}>
              <item.icon size={20} />
              <span>{item.name}</span>
            </Link>
          ))}
        </div>

        <div className={styles.navGroup}>
          <p className={styles.groupLabel}>Reports</p>
          {reportItems.map((item) => (
            <Link key={item.name} href={item.href} className={styles.navLink}>
              <item.icon size={20} />
              <span>{item.name}</span>
            </Link>
          ))}
        </div>
      </nav>

      <div className={styles.footer}>
        <Link href="/settings" className={styles.navLink}>
          <Settings size={20} />
          <span>Settings</span>
        </Link>
        <Link href="/auth/login" className={styles.logoutBtn}>
          <LogOut size={20} />
          <span>Logout</span>
        </Link>
      </div>
    </aside>
  );
}
