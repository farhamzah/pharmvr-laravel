'use client';

import { Download } from 'lucide-react';

interface ExportButtonProps {
  href: string;
  label?: string;
}

export default function ExportButton({ href, label = 'Export CSV' }: ExportButtonProps) {
  return (
    <a
      href={href}
      download
      style={{
        display: 'inline-flex',
        alignItems: 'center',
        gap: '6px',
        padding: '6px 14px',
        borderRadius: 'var(--radius-md)',
        border: '1px solid var(--border-subtle)',
        background: 'transparent',
        color: 'var(--text-secondary)',
        fontSize: '13px',
        fontWeight: 500,
        textDecoration: 'none',
        cursor: 'pointer',
        transition: 'var(--transition-fast)',
      }}
      onMouseEnter={(e) => {
        e.currentTarget.style.borderColor = 'var(--primary)';
        e.currentTarget.style.color = 'var(--primary)';
      }}
      onMouseLeave={(e) => {
        e.currentTarget.style.borderColor = 'var(--border-subtle)';
        e.currentTarget.style.color = 'var(--text-secondary)';
      }}
    >
      <Download size={14} />
      {label}
    </a>
  );
}
