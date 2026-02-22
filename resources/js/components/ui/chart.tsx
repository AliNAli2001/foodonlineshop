import * as React from 'react';
import { Tooltip, type TooltipProps } from 'recharts';

export type ChartConfig = Record<
  string,
  {
    label?: string;
    color?: string;
  }
>;

function cx(...classes: Array<string | undefined | false>) {
  return classes.filter(Boolean).join(' ');
}

function ChartContainer({
  config,
  className,
  children,
}: React.HTMLAttributes<HTMLDivElement> & { config: ChartConfig }) {
  const style = Object.entries(config).reduce<Record<string, string>>((acc, [key, value]) => {
    if (value.color) {
      acc[`--color-${key}`] = value.color;
    }
    return acc;
  }, {});

  return (
    <div className={cx('h-[260px] w-full', className)} style={style as React.CSSProperties}>
      {children}
    </div>
  );
}

const ChartTooltip = Tooltip;

type TooltipPayload = {
  name?: string;
  value?: number | string;
  color?: string;
  dataKey?: string;
  payload?: Record<string, unknown>;
};

function ChartTooltipContent({
  active,
  payload,
  label,
  hideLabel,
  indicator,
  className,
  nameKey,
  dataKey,
  labelFormatter,
}: TooltipProps<number, string> & {
  hideLabel?: boolean;
  indicator?: 'dot' | 'line';
  className?: string;
  nameKey?: string;
  dataKey?: string;
  labelFormatter?: (value: unknown) => string;
}) {
  if (!active || !payload || payload.length === 0) {
    return null;
  }

  const first = payload[0] as TooltipPayload;
  const row = first.payload ?? {};
  const rawLabelValue = dataKey ? row[dataKey] ?? label ?? first.name ?? '' : label ?? first.name ?? '';
  const formatterInput = dataKey ? row : String(rawLabelValue);
  const resolvedLabel = labelFormatter ? labelFormatter(formatterInput) : String(rawLabelValue);
  const valueName = nameKey && first.payload ? String(first.payload[nameKey] ?? first.name ?? '') : String(first.name ?? '');

  return (
    <div
      className={cx(
        'rounded-lg border border-slate-200 bg-white/95 px-3 py-2 text-xs text-slate-800 shadow-lg dark:border-white/10 dark:bg-slate-900/95 dark:text-slate-100',
        className,
      )}
    >
      {!hideLabel ? <div className="mb-1 text-slate-500 dark:text-slate-300">{resolvedLabel}</div> : null}
      <div className="flex items-center gap-2">
        {indicator === 'line' ? (
          <span className="inline-block h-0.5 w-4 rounded" style={{ background: first.color ?? '#22d3ee' }} />
        ) : (
          <span className="inline-block h-2.5 w-2.5 rounded-full" style={{ background: first.color ?? '#22d3ee' }} />
        )}
        <span className="text-slate-500 dark:text-slate-300">{valueName}</span>
        <span className="font-medium">{String(first.value ?? '')}</span>
      </div>
    </div>
  );
}

export { ChartContainer, ChartTooltip, ChartTooltipContent };
