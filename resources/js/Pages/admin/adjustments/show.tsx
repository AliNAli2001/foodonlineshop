import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type Adjustment = {
  id: number;
  quantity?: number | string | null;
  adjustment_type: 'gain' | 'loss' | string;
  reason: string;
  date?: string | null;
  created_at?: string | null;
  updated_at?: string | null;
  adjustable_id?: number | null;
  adjustable_type?: string | null;
};

function formatDate(value?: string | null) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString();
}

export default function AdjustmentsShow() {
  const { t } = useI18n();
  const { adjustment } = usePage<{ adjustment: Adjustment }>().props;
  const isDamagedGoodsLinked = (adjustment.adjustable_type || '').includes('DamagedGoods');

  return (
    <AdminLayout title={t('admin.pages.adjustments.show.title')}>
      <div className="mx-auto max-w-5xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.adjustments.show.heading')} #{adjustment.id}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.adjustments.show.subtitle')}</p>
          </div>
          <div className="flex gap-2">
            {!isDamagedGoodsLinked && (
              <Link href={`/admin/adjustments/${adjustment.id}/edit`} className="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">
                {t('common.edit')}
              </Link>
            )}
            <Link href="/admin/adjustments" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
              {t('common.back')}
            </Link>
          </div>
        </section>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 text-sm text-slate-200">
          <div className="grid gap-3 md:grid-cols-2">
            <Info label={t('admin.pages.adjustments.index.columns.id')} value={String(adjustment.id)} />
            <Info label={t('admin.pages.adjustments.form.amount')} value={adjustment.quantity != null ? String(adjustment.quantity) : '-'} />
            <Info label={t('admin.pages.adjustments.form.adjustmentType')} value={adjustment.adjustment_type === 'gain' ? t('admin.pages.adjustments.form.gain') : t('admin.pages.adjustments.form.loss')} />
            <Info label={t('admin.pages.adjustments.form.date')} value={formatDate(adjustment.date)} />
            <Info label={t('admin.pages.adjustments.show.createdAt')} value={formatDate(adjustment.created_at)} />
            <Info label={t('admin.pages.adjustments.show.updatedAt')} value={formatDate(adjustment.updated_at)} />
          </div>

          <div className="mt-4 rounded-xl border border-white/10 bg-slate-900/40 p-4">
            <p className="mb-1 text-xs uppercase tracking-[0.12em] text-slate-400">{t('admin.pages.adjustments.form.reason')}</p>
            <p>{adjustment.reason || '-'}</p>
          </div>

          {isDamagedGoodsLinked && adjustment.adjustable_id ? (
            <div className="mt-4">
              <Link href={`/admin/damaged-goods/${adjustment.adjustable_id}`} className="inline-flex rounded-lg border border-indigo-300/30 bg-indigo-500/10 px-3 py-1.5 text-xs text-indigo-200 hover:bg-indigo-500/20">
                {t('admin.pages.adjustments.show.openLinkedDamagedGoods')}
              </Link>
            </div>
          ) : null}
        </section>
      </div>
    </AdminLayout>
  );
}

function Info({ label, value }: { label: string; value: string }) {
  return (
    <p>
      <span className="text-slate-400">{label}: </span>
      {value}
    </p>
  );
}


