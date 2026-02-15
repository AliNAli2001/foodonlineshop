import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

type Adjustment = {
  id: number;
  quantity?: number | string | null;
  adjustment_type: 'gain' | 'loss' | string;
  reason: string;
  date?: string | null;
};

function toDateInput(value?: string | null) {
  if (!value) return new Date().toISOString().slice(0, 10);
  if (value.length >= 10) return value.slice(0, 10);
  return value;
}

export default function AdjustmentsEdit() {
  const { adjustment } = usePage<{ adjustment: Adjustment }>().props;

  const { data, setData, post, processing, errors } = useForm({
    _method: 'put',
    quantity: adjustment.quantity ? String(adjustment.quantity) : '',
    adjustment_type: adjustment.adjustment_type || '',
    reason: adjustment.reason || '',
    date: toDateInput(adjustment.date),
  });

  const submit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    post(`/admin/adjustments/${adjustment.id}`);
  };

  return (
    <AdminLayout title="Edit Adjustment">
      <div className="mx-auto max-w-3xl space-y-6">
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <h1 className="text-2xl font-bold text-white">Edit Adjustment</h1>
        </section>

        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <Field label="Amount" error={errors.quantity}>
            <input
              type="number"
              step="0.01"
              value={data.quantity}
              onChange={(e) => setData('quantity', e.target.value)}
              className={inputClass}
            />
          </Field>

          <Field label="Adjustment Type" error={errors.adjustment_type}>
            <select
              value={data.adjustment_type}
              onChange={(e) => setData('adjustment_type', e.target.value as 'gain' | 'loss')}
              className={inputClass}
              required
            >
              <option value="">Select type</option>
              <option value="gain">Gain</option>
              <option value="loss">Loss</option>
            </select>
          </Field>

          <Field label="Date" error={errors.date}>
            <input
              type="date"
              value={data.date}
              onChange={(e) => setData('date', e.target.value)}
              className={inputClass}
              required
            />
          </Field>

          <Field label="Reason" error={errors.reason}>
            <textarea
              rows={4}
              value={data.reason}
              onChange={(e) => setData('reason', e.target.value)}
              className={inputClass}
              required
            />
          </Field>

          <div className="flex gap-3">
            <button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">
              {processing ? 'Updating...' : 'Update Adjustment'}
            </button>
            <Link href="/admin/adjustments" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">
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
