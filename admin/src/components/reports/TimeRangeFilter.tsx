'use client';

import styles from './TimeRangeFilter.module.css';

interface TimeRangeFilterProps {
  value: string;
  onChange: (v: string) => void;
  options?: { label: string; value: string }[];
}

const defaultOptions = [
  { label: '7 Hari', value: '7d' },
  { label: '30 Hari', value: '30d' },
  { label: '90 Hari', value: '90d' },
  { label: 'Semua', value: 'all' },
];

export default function TimeRangeFilter({ value, onChange, options = defaultOptions }: TimeRangeFilterProps) {
  return (
    <div className={styles.filterBar}>
      {options.map((opt) => (
        <button
          key={opt.value}
          className={`${styles.btn} ${value === opt.value ? styles.active : ''}`}
          onClick={() => onChange(opt.value)}
        >
          {opt.label}
        </button>
      ))}
    </div>
  );
}
