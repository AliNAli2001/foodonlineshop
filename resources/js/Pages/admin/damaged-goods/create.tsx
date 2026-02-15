import React, { useMemo } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

type Batch = {
  id: number;
  batch_number?: string | null;
  expiry_date?: string | null;
  available_quantity: number;
};

type Product = {
  id: number;
  name_en?: string;
  name_ar?: string;
  inventory_batches?: Batch[];
  inventoryBatches?: Batch[];
};

export default function DamagedGoodsCreate() {
  const { products = [] } = usePage<{ products: Product[] }>().props;
  const { data, setData, post, processing, errors } = useForm({
    product_id: '',
    inventory_batch_id: '',
    quantity: '',
    reason: '',
  });

  const selectedProduct = useMemo(
    () => products.find((p) => String(p.id) === String(data.product_id)),
    [products, data.product_id],
  );

  const batches = (selectedProduct?.inventory_batches || selectedProduct?.inventoryBatches || []) as Batch[];

  const submit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    post('/admin/damaged-goods');
  };

  return (
    <AdminLayout title="Create Damaged Goods">
      <div className="mx-auto max-w-4xl space-y-6">
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <h1 className="text-2xl font-bold text-white">Register Damaged Goods</h1>
          <p className="mt-1 text-sm text-slate-300">This record will deduct stock from the selected inventory batch.</p>
        </section>

        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <Field label="Product" error={errors.product_id}>
            <select
              value={data.product_id}
              onChange={(e) => {
                setData('product_id', e.target.value);
                setData('inventory_batch_id', '');
              }}
              className={inputClass}
              required
            >
              <option value="">Select product</option>
              {products.map((p) => (
                <option key={p.id} value={p.id}>{p.name_en || p.name_ar}</option>
              ))}
            </select>
          </Field>

          <Field label="Inventory Batch" error={errors.inventory_batch_id}>
            <select
              value={data.inventory_batch_id}
              onChange={(e) => setData('inventory_batch_id', e.target.value)}
              className={inputClass}
              required
              disabled={!data.product_id}
            >
              <option value="">Select batch</option>
              {batches.map((b) => (
                <option key={b.id} value={b.id}>
                  {`Invoice: ${b.batch_number || '-'} | Expiry: ${b.expiry_date ? String(b.expiry_date).slice(0, 10) : '-'} | Available: ${b.available_quantity}`}
                </option>
              ))}
            </select>
          </Field>

          <Field label="Quantity" error={errors.quantity}>
            <input
              type="number"
              min="1"
              value={data.quantity}
              onChange={(e) => setData('quantity', e.target.value)}
              className={inputClass}
              required
            />
          </Field>

          <Field label="Reason" error={errors.reason}>
            <textarea
              rows={3}
              value={data.reason}
              onChange={(e) => setData('reason', e.target.value)}
              className={inputClass}
              required
            />
          </Field>

          <div className="flex gap-3">
            <button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">
              {processing ? 'Saving...' : 'Save Record'}
            </button>
            <Link href="/admin/damaged-goods" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}

const inputClass = 'w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40';

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
  return (
    <div>
      <label className="mb-1 block text-sm text-slate-300">{label}</label>
      {children}
      {error ? <p className="mt-1 text-xs text-rose-300">{error}</p> : null}
    </div>
  );
}
