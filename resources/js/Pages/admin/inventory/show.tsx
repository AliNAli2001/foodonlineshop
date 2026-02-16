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
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.inventory.show.heading')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.inventory.create.product')}: {product?.name_en || product?.name_ar}</p>
          </div>
          <div className="flex gap-2">
            <Link href={`/admin/inventory/${batch.id}/edit`} className="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">{t('common.edit')}</Link>
            <Link href={`/admin/inventory/${product.id}/batches`} className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('admin.pages.inventory.show.backToProductInventory')}</Link>
          </div>
        </section>

        <section className="grid gap-6 lg:grid-cols-2">
          <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
            <h2 className="mb-3 text-lg font-semibold text-white">{t('admin.pages.inventory.show.currentBatch')}</h2>
            <Info label={t('admin.pages.inventory.form.batchNumber')} value={batch.batch_number} />
            <Info label={t('admin.pages.inventory.form.availableQuantity')} value={String(batch.available_quantity ?? 0)} />
            <Info label={t('admin.pages.inventory.form.expiryDate')} value={batch.expiry_date ? String(batch.expiry_date).slice(0, 10) : '-'} />
            <Info label={t('admin.pages.inventory.form.costPrice')} value={`$${Number(batch.cost_price ?? 0).toFixed(3)}`} />
            <Info label={t('common.status')} value={batch.is_expired ? t('admin.pages.inventory.status.expired') : t('admin.pages.inventory.status.available')} />
          </article>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <h2 className="border-b border-white/10 px-4 py-3 text-lg font-semibold text-white">{t('admin.pages.inventory.show.recentMovements')}</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]"><tr>{[
                t('admin.pages.inventory.show.columns.type'),
                t('admin.pages.inventory.show.columns.qtyChange'),
                t('admin.pages.inventory.show.columns.reason'),
                t('admin.pages.inventory.form.costPrice'),
                t('admin.pages.inventory.show.columns.expiry'),
                t('admin.pages.inventory.show.columns.batch'),
                t('admin.pages.inventory.show.columns.date'),
              ].map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr><td colSpan={7} className="px-4 py-8 text-center text-sm text-slate-400">{t('admin.pages.inventory.show.emptyMovements')}</td></tr>
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


