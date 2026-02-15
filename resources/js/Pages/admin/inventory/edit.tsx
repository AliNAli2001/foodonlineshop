import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

type Batch = { id: number; available_quantity: number; batch_number: string; cost_price: number; expiry_date?: string };

type Product = { id: number; name_en?: string; name_ar?: string };

export default function InventoryEdit() {
  const { product, batch } = usePage<{ product: Product; batch: Batch }>().props;
  const { data, setData, post, processing, errors } = useForm({
    _method: 'put',
    available_quantity: batch.available_quantity ?? 0,
    batch_number: batch.batch_number ?? '',
    cost_price: batch.cost_price ?? '',
    expiry_date: batch.expiry_date ? String(batch.expiry_date).slice(0, 10) : '',
    reason: '',
  });

  const submit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    post(`/admin/inventory/${batch.id}`);
  };

  return (
    <AdminLayout title="Edit Batch">
      <div className="mx-auto max-w-3xl space-y-6">
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <h1 className="text-2xl font-bold text-white">Edit Inventory Batch</h1>
          <p className="text-sm text-slate-300">Product: {product.name_en || product.name_ar || `#${product.id}`}</p>
        </section>

        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <Field label="Available Quantity" error={errors.available_quantity}><input type="number" min="0" value={data.available_quantity} onChange={(e) => setData('available_quantity', e.target.value)} className={inputClass} required /></Field>
          <Field label="Batch Number" error={errors.batch_number}><input value={data.batch_number} onChange={(e) => setData('batch_number', e.target.value)} className={inputClass} required /></Field>
          <Field label="Cost Price" error={errors.cost_price}><input type="number" step="0.001" min="0" value={data.cost_price} onChange={(e) => setData('cost_price', e.target.value)} className={inputClass} required /></Field>
          <Field label="Expiry Date" error={errors.expiry_date}><input type="date" value={data.expiry_date} onChange={(e) => setData('expiry_date', e.target.value)} className={inputClass} /></Field>
          <Field label="Reason" error={errors.reason}><textarea rows={3} value={data.reason} onChange={(e) => setData('reason', e.target.value)} className={inputClass} required /></Field>
          <p className="text-xs text-slate-400">If cost price changes, linked damaged-goods costs may be recalculated.</p>

          <div className="flex gap-3">
            <button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">{processing ? 'Updating...' : 'Update Batch'}</button>
            <Link href={`/admin/inventory/${batch.id}`} className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">Cancel</Link>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}

const inputClass = 'w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40';
function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) { return <div><label className="mb-1 block text-sm text-slate-300">{label}</label>{children}{error ? <p className="mt-1 text-xs text-rose-300">{error}</p> : null}</div>; }
