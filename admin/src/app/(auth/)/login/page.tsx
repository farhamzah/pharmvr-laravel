import { Lock, Mail } from 'lucide-react';
import Image from 'next/image';
import styles from './login.module.css';

export default function LoginPage() {
  return (
    <div className={styles.authPage}>
      <div className={`${styles.loginCard} card`}>
        <div className={styles.logoHeader}>
          <Image 
            src="/brand/logo.png" 
            alt="PharmVR Logo" 
            width={60} 
            height={60} 
          />
          <h1 className={styles.brandTitle}>PharmVR</h1>
          <p className={styles.loginSubtitle}>Internal Administration Portal</p>
        </div>

        <form className={styles.form}>
          <div className={styles.inputGroup}>
            <label htmlFor="email">Email Address</label>
            <div className={styles.inputWrapper}>
              <Mail size={18} className={styles.inputIcon} />
              <input type="email" id="email" placeholder="admin@pharmvr.com" />
            </div>
          </div>

          <div className={styles.inputGroup}>
            <label htmlFor="password">Password</label>
            <div className={styles.inputWrapper}>
              <Lock size={18} className={styles.inputIcon} />
              <input type="password" id="password" placeholder="••••••••" />
            </div>
          </div>

          <button type="submit" className="btn-primary" style={{ width: '100%', marginTop: 'var(--space-4)' }}>
            Sign In to Portal
          </button>
        </form>

        <p className={styles.footer}>
          Forgot password? <a href="#" className={styles.link}>Contact Support</a>
        </p>
      </div>

      <div className={styles.accentGlow} />
    </div>
  );
}
