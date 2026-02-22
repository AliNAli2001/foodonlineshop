import React, { useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type DamagedRow = {
  id: number;
  product?: { name_en?: string; name_ar?: string };
  quantity: number;
  reason: string;
  created_at?: string;
};

function formatDate(value?: string | null) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString();
}

export default function DamagedGoodsIndex() {
  const { t } = useI18n();
  const page = usePage<any>();
  const damagedGoods = page.props.damagedGoods;
  const filters = page.props.filters || {};
  const rows: DamagedRow[] = Array.isArray(damagedGoods?.data)
    ? damagedGoods.data
    : Array.isArray(damagedGoods)
      ? damagedGoods
      : [];
  const [infoOpen, setInfoOpen] = useState(false);

  const info = useMemo(() => {
    const totalQty = rows.reduce((sum, r) => sum + Number(r.quantity || 0), 0);
    return {
      count: rows.length,
      totalQty,
    };
  }, [rows]);

  const applyFilters = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const params = Object.fromEntries(form.entries());
    router.get('/admin/damaged-goods', params, { preserveState: true, preserveScroll: true });
  };

  const removeRow = (id: number) => {
    if (!window.confirm(t('admin.pages.damagedGoods.index.deleteConfirm'))) return;
    router.delete(`/admin/damaged-goods/${id}`);
  };

  return (
    <AdminLayout title={t('admin.pages.damagedGoods.index.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.damagedGoods.index.heading')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.damagedGoods.index.subtitle')}</p>
          </div>
          <div className="flex gap-2">
            <Link href="/admin/damaged-goods/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
              {t('admin.pages.damagedGoods.index.addRecord')}
            </Link>
            <Link href="/admin/dashboard" className="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-white/15 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
              {t('common.back')}
            </Link>
          </div>
        </section>

        <form onSubmit={applyFilters} className="grid gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-4 md:grid-cols-4">
          <input type="date" name="start_date" defaultValue={filters.start_date || ''} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
          <input type="date" name="end_date" defaultValue={filters.end_date || ''} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
          <button className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
            {t('admin.pages.statistics.common.filter', 'Filter')}
          </button>
          <Link href="/admin/damaged-goods" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-center text-sm text-slate-200 hover:bg-white/10">
            {t('admin.pages.products.index.filters.reset', 'Reset')}
          </Link>
        </form>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
          <div className="flex items-center justify-between gap-2">
            <h2 className="text-sm font-semibold text-white">{t('admin.pages.damagedGoods.index.heading')}</h2>
            <button
              type="button"
              onClick={() => setInfoOpen((prev) => !prev)}
              className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
            >
              {infoOpen ? t('admin.pages.inventory.index.filters.hideFilters', 'Hide') : t('admin.pages.inventory.index.filters.showFilters', 'Show')}
            </button>
          </div>
          {infoOpen && (
            <div className="mt-3 grid gap-3 sm:grid-cols-2">
              <article className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                <p className="text-xs text-slate-400">{t('admin.pages.damagedGoods.index.cards.records', 'Records')}</p>
                <p className="mt-1 text-2xl font-bold text-white">{info.count}</p>
              </article>
              <article className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                <p className="text-xs text-slate-400">{t('admin.pages.damagedGoods.index.cards.totalQuantity', 'Total Quantity')}</p>
                <p className="mt-1 text-2xl font-bold text-white">{info.totalQty}</p>
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
                    t('admin.pages.damagedGoods.index.columns.product'),
                    t('admin.pages.damagedGoods.index.columns.quantity'),
                    t('admin.pages.damagedGoods.index.columns.reason'),
                    t('admin.pages.damagedGoods.index.columns.date'),
                    t('common.actions'),
                  ].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-4 py-8 text-center text-sm text-slate-400">{t('admin.pages.damagedGoods.index.empty')}</td>
                  </tr>
                ) : (
                  rows.map((r) => (
                    <tr key={r.id} className="border-t border-white/10">
                      <td className="px-4 py-3 text-sm text-slate-200">{r.product?.name_en || r.product?.name_ar || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{r.quantity}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{r.reason}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{formatDate(r.created_at)}</td>
                      <td className="px-4 py-3">
                        <div className="flex gap-2">
                          <Link href={`/admin/damaged-goods/${r.id}`} className="rounded-lg border border-cyan-400/40 bg-cyan-50 px-2.5 py-1 text-xs text-cyan-700 hover:bg-cyan-100 dark:border-cyan-300/30 dark:bg-cyan-400/10 dark:text-cyan-200 dark:hover:bg-cyan-400/20">{t('common.view')}</Link>
                          <button onClick={() => removeRow(r.id)} className="rounded-lg border border-rose-400/40 bg-rose-50 px-2.5 py-1 text-xs text-rose-700 hover:bg-rose-100 dark:border-rose-300/30 dark:bg-rose-500/10 dark:text-rose-200 dark:hover:bg-rose-500/20">{t('common.delete')}</button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {damagedGoods?.links && (
            <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">
              {damagedGoods.links.map((link: any, i: number) => (
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


