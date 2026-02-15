import React from 'react';
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
  const rows: Adjustment[] = Array.isArray(adjustments?.data)
    ? adjustments.data
    : Array.isArray(adjustments)
      ? adjustments
      : [];

  const removeRow = (id: number) => {
    if (!window.confirm('Delete this adjustment?')) return;
    router.delete(`/admin/adjustments/${id}`);
  };

  return (
    <AdminLayout title={t('admin.pages.adjustments.index.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">Financial Adjustments</h1>
            <p className="text-sm text-slate-300">Track gain and loss entries.</p>
          </div>
          <Link href="/admin/adjustments/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
            + Add Adjustment
          </Link>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]">
                <tr>
                  {['ID', 'Amount', 'Type', 'Reason', 'Date', 'Actions'].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="px-4 py-8 text-center text-sm text-slate-400">No adjustments found.</td>
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
                            <span className="rounded-full bg-emerald-400/20 px-2 py-0.5 text-xs text-emerald-200 ring-1 ring-emerald-300/30">Gain</span>
                          ) : (
                            <span className="rounded-full bg-rose-400/20 px-2 py-0.5 text-xs text-rose-200 ring-1 ring-rose-300/30">Loss</span>
                          )}
                        </td>
                        <td className="px-4 py-3 text-sm text-slate-200">{a.reason}</td>
                        <td className="px-4 py-3 text-sm text-slate-200">{formatDate(a.date || a.created_at)}</td>
                        <td className="px-4 py-3">
                          <div className="flex flex-wrap items-center gap-2">
                            <Link href={`/admin/adjustments/${a.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('common.view')}</Link>
                            {isDamagedGoodsLinked ? (
                              a.adjustable_id ? (
                                <Link href={`/admin/damaged-goods/${a.adjustable_id}`} className="rounded-lg border border-indigo-300/30 bg-indigo-500/10 px-2.5 py-1 text-xs text-indigo-200 hover:bg-indigo-500/20">Damaged Goods</Link>
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


