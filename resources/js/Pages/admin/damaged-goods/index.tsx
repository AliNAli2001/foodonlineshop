import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

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
  const page = usePage<any>();
  const damagedGoods = page.props.damagedGoods;
  const rows: DamagedRow[] = Array.isArray(damagedGoods?.data)
    ? damagedGoods.data
    : Array.isArray(damagedGoods)
      ? damagedGoods
      : [];

  const removeRow = (id: number) => {
    if (!window.confirm('Delete this damaged goods record? This will reverse stock and linked loss adjustment.')) return;
    router.delete(`/admin/damaged-goods/${id}`);
  };

  return (
    <AdminLayout title="Damaged Goods">
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">Damaged Goods</h1>
            <p className="text-sm text-slate-300">Track and audit damaged stock records.</p>
          </div>
          <div className="flex gap-2">
            <Link href="/admin/damaged-goods/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
              + Add Record
            </Link>
            <Link href="/admin/dashboard" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
              Back
            </Link>
          </div>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]">
                <tr>
                  {['Product', 'Quantity', 'Reason', 'Date', 'Actions'].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-4 py-8 text-center text-sm text-slate-400">No damaged goods records.</td>
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
                          <Link href={`/admin/damaged-goods/${r.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">View</Link>
                          <button onClick={() => removeRow(r.id)} className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2.5 py-1 text-xs text-rose-200 hover:bg-rose-500/20">Delete</button>
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
