import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type Delivery = {
  id: number;
  first_name: string;
  last_name: string;
  phone: string;
  phone_plus?: string | null;
  email?: string | null;
  info?: string | null;
  status: 'available' | 'busy' | 'inactive' | string;
};

type Status = 'available' | 'busy' | 'inactive';

export default function DeliveryEdit() {
  const { t } = useI18n();
  const { delivery } = usePage<{ delivery: Delivery }>().props;

  const { data, setData, post, processing, errors } = useForm({
    _method: 'put',
    first_name: delivery.first_name || '',
    last_name: delivery.last_name || '',
    phone: delivery.phone || '',
    phone_plus: delivery.phone_plus || '',
    email: delivery.email || '',
    info: delivery.info || '',
    status: (delivery.status || 'available') as Status,
  });

  const submit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    post(`/admin/delivery/${delivery.id}`);
  };

  return (
    <AdminLayout title={t('admin.pages.delivery.edit.title')}>
      <div className="mx-auto max-w-3xl space-y-6">
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <h1 className="text-2xl font-bold text-white">Edit Delivery Person</h1>
        </section>

        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div className="grid gap-4 md:grid-cols-2">
            <Field label="First Name" error={errors.first_name}>
              <input value={data.first_name} onChange={(e) => setData('first_name', e.target.value)} className={inputClass} required />
            </Field>
            <Field label="Last Name" error={errors.last_name}>
              <input value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} className={inputClass} required />
            </Field>
          </div>

          <Field label="Phone" error={errors.phone}>
            <input value={data.phone} onChange={(e) => setData('phone', e.target.value)} className={inputClass} required />
          </Field>

          <Field label="Phone Plus" error={errors.phone_plus}>
            <input value={data.phone_plus} onChange={(e) => setData('phone_plus', e.target.value)} className={inputClass} />
          </Field>

          <Field label="Email" error={errors.email}>
            <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className={inputClass} />
          </Field>

          <Field label="Status" error={errors.status}>
            <select value={data.status} onChange={(e) => setData('status', e.target.value as Status)} className={inputClass} required>
              <option value="available">Available</option>
              <option value="busy">Busy</option>
              <option value="inactive">Inactive</option>
            </select>
          </Field>

          <Field label="Additional Info" error={errors.info}>
            <textarea rows={4} value={data.info} onChange={(e) => setData('info', e.target.value)} className={inputClass} />
          </Field>

          <div className="flex gap-3">
            <button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">
              {processing ? 'Updating...' : 'Update'}
            </button>
            <Link href="/admin/delivery" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">
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


