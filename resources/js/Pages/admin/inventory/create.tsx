import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type Product = { id: number; name_en?: string; name_ar?: string };

export default function InventoryCreate() {
  const { t } = useI18n();
  const { product } = usePage<{ product: Product }>().props;
  const { data, setData, post, processing, errors } = useForm({
    available_quantity: '',
    expiry_date: '',
    batch_number: '',
    cost_price: '',
    reason: '',
  });

  const submit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    post(`/admin/inventory/${product.id}`);
  };

  return (
    <AdminLayout title={t('admin.pages.inventory.create.title')}>
      <div className="mx-auto max-w-3xl space-y-6">
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <h1 className="text-2xl font-bold text-white">Add Inventory Batch</h1>
          <p className="text-sm text-slate-300">Product: {product.name_en || product.name_ar || `#${product.id}`}</p>
        </section>

        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <Field label="Available Quantity" error={errors.available_quantity}><input type="number" min="1" value={data.available_quantity} onChange={(e) => setData('available_quantity', e.target.value)} className={inputClass} required /></Field>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Expiry Date" error={errors.expiry_date}><input type="date" value={data.expiry_date} onChange={(e) => setData('expiry_date', e.target.value)} className={inputClass} /></Field>
            <Field label="Batch Number" error={errors.batch_number}><input value={data.batch_number} onChange={(e) => setData('batch_number', e.target.value)} className={inputClass} required /></Field>
          </div>
          <Field label="Cost Price" error={errors.cost_price}><input type="number" step="0.001" min="0" value={data.cost_price} onChange={(e) => setData('cost_price', e.target.value)} className={inputClass} required /></Field>
          <Field label="Reason (optional)" error={errors.reason}><textarea rows={3} value={data.reason} onChange={(e) => setData('reason', e.target.value)} className={inputClass} /></Field>

          <div className="flex gap-3">
            <button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">{processing ? 'Saving...' : 'Create Batch'}</button>
            <Link href={`/admin/inventory/${product.id}/batches`} className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">{t('common.cancel')}</Link>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}

const inputClass = 'w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40';

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
  return <div><label className="mb-1 block text-sm text-slate-300">{label}</label>{children}{error ? <p className="mt-1 text-xs text-rose-300">{error}</p> : null}</div>;
}


