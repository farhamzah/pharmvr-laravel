'use client';

import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, AreaChart, Area, LineChart, Line } from 'recharts';

const COLORS = {
  primary: '#00E5FF',
  primaryDark: '#00B8D4',
  success: '#00E676',
  warning: '#FFB74D',
  error: '#CF6679',
  info: '#64B5F6',
  surface: '#151E27',
  border: '#2A3545',
  textSecondary: '#B0BEC5',
  textTertiary: '#78909C',
};

const commonAxisProps = {
  tick: { fill: COLORS.textTertiary, fontSize: 11 },
  axisLine: { stroke: COLORS.border },
  tickLine: { stroke: COLORS.border },
};

const tooltipStyle = {
  contentStyle: {
    background: COLORS.surface,
    border: `1px solid ${COLORS.border}`,
    borderRadius: 8,
    fontSize: 12,
    color: '#fff',
  },
};

interface ComparisonBarChartProps {
  data: { name: string; pretest: number; posttest: number }[];
  height?: number;
}

export function ComparisonBarChart({ data, height = 300 }: ComparisonBarChartProps) {
  return (
    <ResponsiveContainer width="100%" height={height}>
      <BarChart data={data} barGap={4}>
        <CartesianGrid strokeDasharray="3 3" stroke={COLORS.border} />
        <XAxis dataKey="name" {...commonAxisProps} />
        <YAxis {...commonAxisProps} domain={[0, 100]} />
        <Tooltip {...tooltipStyle} />
        <Legend wrapperStyle={{ fontSize: 12, color: COLORS.textSecondary }} />
        <Bar dataKey="pretest" name="Pre-Test" fill={COLORS.warning} radius={[4, 4, 0, 0]} />
        <Bar dataKey="posttest" name="Post-Test" fill={COLORS.success} radius={[4, 4, 0, 0]} />
      </BarChart>
    </ResponsiveContainer>
  );
}

interface SimpleBarChartProps {
  data: { name: string; value: number }[];
  color?: string;
  height?: number;
  barKey?: string;
}

export function SimpleBarChart({ data, color = COLORS.primary, height = 250, barKey = 'value' }: SimpleBarChartProps) {
  return (
    <ResponsiveContainer width="100%" height={height}>
      <BarChart data={data}>
        <CartesianGrid strokeDasharray="3 3" stroke={COLORS.border} />
        <XAxis dataKey="name" {...commonAxisProps} />
        <YAxis {...commonAxisProps} />
        <Tooltip {...tooltipStyle} />
        <Bar dataKey={barKey} fill={color} radius={[4, 4, 0, 0]} />
      </BarChart>
    </ResponsiveContainer>
  );
}

interface MultiAreaChartProps {
  data: Record<string, unknown>[];
  lines: { key: string; color: string; name: string }[];
  height?: number;
}

export function MultiAreaChart({ data, lines, height = 300 }: MultiAreaChartProps) {
  return (
    <ResponsiveContainer width="100%" height={height}>
      <AreaChart data={data}>
        <defs>
          {lines.map((l) => (
            <linearGradient key={l.key} id={`grad-${l.key}`} x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor={l.color} stopOpacity={0.3} />
              <stop offset="95%" stopColor={l.color} stopOpacity={0} />
            </linearGradient>
          ))}
        </defs>
        <CartesianGrid strokeDasharray="3 3" stroke={COLORS.border} />
        <XAxis dataKey="date" {...commonAxisProps} />
        <YAxis {...commonAxisProps} />
        <Tooltip {...tooltipStyle} />
        <Legend wrapperStyle={{ fontSize: 12, color: COLORS.textSecondary }} />
        {lines.map((l) => (
          <Area
            key={l.key}
            type="monotone"
            dataKey={l.key}
            name={l.name}
            stroke={l.color}
            fill={`url(#grad-${l.key})`}
            strokeWidth={2}
          />
        ))}
      </AreaChart>
    </ResponsiveContainer>
  );
}

interface MultiBarChartProps {
  data: Record<string, unknown>[];
  bars: { key: string; color: string; name: string }[];
  height?: number;
}

export function MultiBarChart({ data, bars, height = 300 }: MultiBarChartProps) {
  return (
    <ResponsiveContainer width="100%" height={height}>
      <BarChart data={data} barGap={4}>
        <CartesianGrid strokeDasharray="3 3" stroke={COLORS.border} />
        <XAxis dataKey="name" {...commonAxisProps} />
        <YAxis {...commonAxisProps} />
        <Tooltip {...tooltipStyle} />
        <Legend wrapperStyle={{ fontSize: 12, color: COLORS.textSecondary }} />
        {bars.map((b) => (
          <Bar key={b.key} dataKey={b.key} name={b.name} fill={b.color} radius={[4, 4, 0, 0]} />
        ))}
      </BarChart>
    </ResponsiveContainer>
  );
}

interface SimpleLineChartProps {
  data: Record<string, unknown>[];
  dataKey: string;
  color?: string;
  height?: number;
}

export function SimpleLineChart({ data, dataKey, color = COLORS.primary, height = 250 }: SimpleLineChartProps) {
  return (
    <ResponsiveContainer width="100%" height={height}>
      <LineChart data={data}>
        <CartesianGrid strokeDasharray="3 3" stroke={COLORS.border} />
        <XAxis dataKey="date" {...commonAxisProps} />
        <YAxis {...commonAxisProps} />
        <Tooltip {...tooltipStyle} />
        <Line type="monotone" dataKey={dataKey} stroke={color} strokeWidth={2} dot={false} />
      </LineChart>
    </ResponsiveContainer>
  );
}
