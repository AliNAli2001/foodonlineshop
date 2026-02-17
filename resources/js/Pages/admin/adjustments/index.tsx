import React, { useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type Adjustment = {
  id: number;
  quantity?: number | string | null;
  adjustment_type: 'gain' | 'loss' | string;
  reason: string;
  date?: string | null;
  created_at?: string | null;
  adjustable_id?: number | null;
  adjustable_type?: string | null;
};

function formatDate(value?: string | null) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString();
}

export default function AdjustmentsIndex() {
  const { t } = useI18n();
  const page = usePage<any>();
  const adjustments = page.props.adjustments;
  const filters = page.props.filters || {};
  const rows: Adjustment[] = Array.isArray(adjustments?.data)
    ? adjustments.data
    : Array.isArray(adjustments)
      ? adjustments
      : [];
  const [infoOpen, setInfoOpen] = useState(false);

  const info = useMemo(() => {
    const gains = rows.filter((r) => r.adjustment_type === 'gain').length;
    const losses = rows.filter((r) => r.adjustment_type === 'loss').length;
    return { count: rows.length, gains, losses };
  }, [rows]);

  const applyFilters = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const params = Object.fromEntries(form.entries());
    router.get('/admin/adjustments', params, { preserveState: true, preserveScroll: true });
  };

  const removeRow = (id: number) => {
    if (!window.confirm(t('admin.pages.adjustments.index.deleteConfirm'))) return;
    router.delete(`/admin/adjustments/${id}`);
  };

  return (
    <AdminLayout title={t('admin.pages.adjustments.index.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.adjustments.index.heading')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.adjustments.index.subtitle')}</p>
          </div>
          <Link href="/admin/adjustments/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
            {t('admin.pages.adjustments.index.addAdjustment')}
          </Link>
        </section>

        <form onSubmit={applyFilters} className="grid gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-4 md:grid-cols-4">
          <input type="date" name="start_date" defaultValue={filters.start_date || ''} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
          <input type="date" name="end_date" defaultValue={filters.end_date || ''} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
          <button className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
            {t('admin.pages.statistics.common.filter', 'Filter')}
          </button>
          <Link href="/admin/adjustments" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-center text-sm text-slate-200 hover:bg-white/10">
            {t('admin.pages.products.index.filters.reset', 'Reset')}
          </Link>
        </form>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
          <div className="flex items-center justify-between gap-2">
            <h2 className="text-sm font-semibold text-white">{t('admin.pages.adjustments.index.heading')}</h2>
            <button
              type="button"
              onClick={() => setInfoOpen((prev) => !prev)}
              className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
            >
              {infoOpen ? t('admin.pages.inventory.index.filters.hideFilters', 'Hide') : t('admin.pages.inventory.index.filters.showFilters', 'Show')}
            </button>
          </div>
          {infoOpen && (
            <div className="mt-3 grid gap-3 sm:grid-cols-3">
              <article className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                <p className="text-xs text-slate-400">{t('admin.pages.adjustments.index.cards.records', 'Records')}</p>
                <p className="mt-1 text-2xl font-bold text-white">{info.count}</p>
              </article>
              <article className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                <p className="text-xs text-slate-400">{t('admin.pages.adjustments.form.gain')}</p>
                <p className="mt-1 text-2xl font-bold text-emerald-300">{info.gains}</p>
              </article>
              <article className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                <p className="text-xs text-slate-400">{t('admin.pages.adjustments.form.loss')}</p>
                <p className="mt-1 text-2xl font-bold text-rose-300">{info.losses}</p>
              </article>
            </div>
          )}
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]">
                <tr>
                  {[
                    t('admin.pages.adjustments.index.columns.id'),
                    t('admin.pages.adjustments.form.amount'),
                    t('admin.pages.adjustments.form.adjustmentType'),
                    t('admin.pages.adjustments.form.reason'),
                    t('admin.pages.adjustments.form.date'),
                    t('common.actions'),
                  ].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="px-4 py-8 text-center text-sm text-slate-400">{t('admin.pages.adjustments.index.empty')}</td>
                  </tr>
                ) : (
                  rows.map((a) => {
                    const isDamagedGoodsLinked = (a.adjustable_type || '').includes('DamagedGoods');
                    return (
                      <tr key={a.id} className="border-t border-white/10">
                        <td className="px-4 py-3 text-sm text-slate-200">{a.id}</td>
                        <td className="px-4 py-3 text-sm text-slate-200">{a.quantity ?? '-'}</td>
                        <td className="px-4 py-3 text-sm">
                          {a.adjustment_type === 'gain' ? (
                            <span className="rounded-full bg-emerald-400/20 px-2 py-0.5 text-xs text-emerald-200 ring-1 ring-emerald-300/30">{t('admin.pages.adjustments.form.gain')}</span>
                          ) : (
                            <span className="rounded-full bg-rose-400/20 px-2 py-0.5 text-xs text-rose-200 ring-1 ring-rose-300/30">{t('admin.pages.adjustments.form.loss')}</span>
                          )}
                        </td>
                        <td className="px-4 py-3 text-sm text-slate-200">{a.reason}</td>
                        <td className="px-4 py-3 text-sm text-slate-200">{formatDate(a.date || a.created_at)}</td>
                        <td className="px-4 py-3">
                          <div className="flex flex-wrap items-center gap-2">
                            <Link href={`/admin/adjustments/${a.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('common.view')}</Link>
                            {isDamagedGoodsLinked ? (
                              a.adjustable_id ? (
                                <Link href={`/admin/damaged-goods/${a.adjustable_id}`} className="rounded-lg border border-indigo-300/30 bg-indigo-500/10 px-2.5 py-1 text-xs text-indigo-200 hover:bg-indigo-500/20">{t('admin.pages.adjustments.index.damagedGoods')}</Link>
                              ) : null
                            ) : (
                              <>
                                <Link href={`/admin/adjustments/${a.id}/edit`} className="rounded-lg border border-amber-300/30 bg-amber-400/10 px-2.5 py-1 text-xs text-amber-200 hover:bg-amber-400/20">{t('common.edit')}</Link>
                                <button onClick={() => removeRow(a.id)} className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2.5 py-1 text-xs text-rose-200 hover:bg-rose-500/20">{t('common.delete')}</button>
                              </>
                            )}
                          </div>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>

          {adjustments?.links && (
            <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">
              {adjustments.links.map((link: any, i: number) => (
                <Link
                  key={`${link.label}-${i}`}
                  href={link.url || '#'}
                  preserveScroll
                  className={`rounded-lg px-3 py-1.5 text-sm ${
                    link.active
                      ? 'bg-cyan-400 text-slate-950'
                      : link.url
                        ? 'bg-white/5 text-slate-200 hover:bg-white/10'
                        : 'cursor-not-allowed bg-white/5 text-slate-500'
                  }`}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              ))}
            </div>
          )}
        </section>
      </div>
    </AdminLayout>
  );
}


