import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import { useI18n } from '../../../../i18n';

export default function InventoryProductShow() {
  const { t } = useI18n();
  const { product, batches, movements } = usePage<any>().props;
  const batchRows = Array.isArray(batches) ? batches : Array.isArray(batches?.data) ? batches.data : [];
  const movementRows = Array.isArray(movements?.data) ? movements.data : Array.isArray(movements) ? movements : [];

  return (
    <AdminLayout title={t('admin.pages.inventory.product.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{product?.name_en || product?.name_ar}</h1>
            <p className="text-sm text-slate-300">Total stock: {product?.stock_available_quantity ?? 0}</p>
          </div>
          <div className="flex gap-2">
            <Link href={`/admin/inventory/${product.id}/create`} className="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">+ Add Batch</Link>
            <Link href="/admin/inventory" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('common.back')}</Link>
          </div>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <h2 className="border-b border-white/10 px-4 py-3 text-lg font-semibold text-white">Batches</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]"><tr>{['Batch Number', 'Expiry Date', 'Cost Price', 'Stock', 'Status', 'Actions'].map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
              <tbody>
                {batchRows.length === 0 ? (
                  <tr><td colSpan={6} className="px-4 py-8 text-center text-sm text-slate-400">No batches for this product.</td></tr>
                ) : (
                  batchRows.map((b: any) => (
                    <tr key={b.id} className="border-t border-white/10">
                      <td className="px-4 py-3 text-sm text-slate-200">{b.batch_number || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{b.expiry_date ? String(b.expiry_date).slice(0, 10) : '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{b.cost_price}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{b.available_quantity}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{b.is_expired ? 'Expired' : 'Available'}</td>
                      <td className="px-4 py-3"><div className="flex gap-2"><Link href={`/admin/inventory/${b.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('common.view')}</Link><Link href={`/admin/inventory/${b.id}/edit`} className="rounded-lg border border-amber-300/30 bg-amber-400/10 px-2.5 py-1 text-xs text-amber-200 hover:bg-amber-400/20">{t('common.edit')}</Link></div></td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <h2 className="border-b border-white/10 px-4 py-3 text-lg font-semibold text-white">Recent Movements</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]"><tr>{['Type', 'Qty Change', 'Reason', 'Date', 'Batch', 'Expiry', 'Cost'].map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
              <tbody>
                {movementRows.length === 0 ? (
                  <tr><td colSpan={7} className="px-4 py-8 text-center text-sm text-slate-400">No movements yet.</td></tr>
                ) : (
                  movementRows.map((m: any, i: number) => (
                    <tr key={m.id || i} className="border-t border-white/10">
                      <td className="px-4 py-3 text-sm text-slate-200">{m.transaction_type_label || m.transaction_type || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{Number(m.available_change) > 0 ? '+' : ''}{m.available_change}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.reason || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.created_at || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.batch_number || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.expiry_date || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.cost_price || '-'}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
          {movements?.links && (
            <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">
              {movements.links.map((link: any, idx: number) => (
                <Link key={`${link.label}-${idx}`} href={link.url || '#'} preserveScroll className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-cyan-400 text-slate-950' : link.url ? 'bg-white/5 text-slate-200 hover:bg-white/10' : 'cursor-not-allowed bg-white/5 text-slate-500'}`} dangerouslySetInnerHTML={{ __html: link.label }} />
              ))}
            </div>
          )}
        </section>
      </div>
    </AdminLayout>
  );
}


