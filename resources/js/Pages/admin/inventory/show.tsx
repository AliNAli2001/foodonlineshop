import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type Movement = any;

export default function InventoryShow() {
  const { t } = useI18n();
  const { product, batch, movements } = usePage<any>().props;
  const rows: Movement[] = Array.isArray(movements?.data) ? movements.data : Array.isArray(movements) ? movements : [];

  return (
    <AdminLayout title={t('admin.pages.inventory.show.title')}>
      <div className="mx-auto max-w-6xl space-y-6">
        <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">Inventory Batch</h1>
            <p className="text-sm text-slate-300">Product: {product?.name_en || product?.name_ar}</p>
          </div>
          <div className="flex gap-2">
            <Link href={`/admin/inventory/${batch.id}/edit`} className="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">{t('common.edit')}</Link>
            <Link href={`/admin/inventory/${product.id}/batches`} className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Back to Product Inventory</Link>
          </div>
        </section>

        <section className="grid gap-6 lg:grid-cols-2">
          <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
            <h2 className="mb-3 text-lg font-semibold text-white">Current Batch</h2>
            <Info label="Batch Number" value={batch.batch_number} />
            <Info label="Available Quantity" value={String(batch.available_quantity ?? 0)} />
            <Info label="Expiry Date" value={batch.expiry_date ? String(batch.expiry_date).slice(0, 10) : '-'} />
            <Info label="Cost Price" value={`$${Number(batch.cost_price ?? 0).toFixed(3)}`} />
            <Info label="Status" value={batch.is_expired ? 'Expired' : 'Available'} />
          </article>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <h2 className="border-b border-white/10 px-4 py-3 text-lg font-semibold text-white">Recent Movements</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]"><tr>{['Type', 'Qty Change', 'Reason', 'Cost Price', 'Expiry', 'Batch', 'Date'].map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr><td colSpan={7} className="px-4 py-8 text-center text-sm text-slate-400">No movements yet.</td></tr>
                ) : (
                  rows.map((m, i) => (
                    <tr key={m.id || i} className="border-t border-white/10">
                      <td className="px-4 py-3 text-sm text-slate-200">{m.transaction_type_label || m.transaction_type || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{Number(m.available_change) > 0 ? '+' : ''}{m.available_change}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.reason || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.cost_price ?? '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.expiry_date ?? '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.batch_number ?? '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{m.created_at ?? '-'}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </AdminLayout>
  );
}

function Info({ label, value }: { label: string; value: string }) { return <p className="mb-2 text-sm text-slate-200"><span className="text-slate-400">{label}: </span>{value}</p>; }


