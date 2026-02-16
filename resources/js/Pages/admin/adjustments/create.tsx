import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

export default function AdjustmentsCreate() {
  const { t } = useI18n();
  const { data, setData, post, processing, errors } = useForm({ quantity: '', adjustment_type: '', reason: '', date: new Date().toISOString().slice(0, 10) });
  const submit = (e: React.FormEvent<HTMLFormElement>) => { e.preventDefault(); post('/admin/adjustments'); };

  return (
    <AdminLayout title={t('admin.pages.adjustments.create.title')}>
      <div className="mx-auto max-w-3xl space-y-6">
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5"><h1 className="text-2xl font-bold text-white">{t('admin.pages.adjustments.create.heading')}</h1></section>
        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <Field label={t('admin.pages.adjustments.form.amount')} error={errors.quantity}><input type="number" value={data.quantity} onChange={(e) => setData('quantity', e.target.value)} className={inputClass} required /></Field>
          <Field label={t('admin.pages.adjustments.form.adjustmentType')} error={errors.adjustment_type}><select value={data.adjustment_type} onChange={(e) => setData('adjustment_type', e.target.value)} className={inputClass} required><option value="">{t('admin.pages.adjustments.form.select')}</option><option value="gain">{t('admin.pages.adjustments.form.gain')}</option><option value="loss">{t('admin.pages.adjustments.form.loss')}</option></select></Field>
          <Field label={t('admin.pages.adjustments.form.date')} error={errors.date}><input type="date" value={data.date} onChange={(e) => setData('date', e.target.value)} className={inputClass} required /></Field>
          <Field label={t('admin.pages.adjustments.form.reason')} error={errors.reason}><textarea rows={4} value={data.reason} onChange={(e) => setData('reason', e.target.value)} className={inputClass} required /></Field>
          <div className="flex gap-3"><button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">{processing ? t('admin.pages.adjustments.form.saving') : t('admin.pages.adjustments.create.submit')}</button><Link href="/admin/adjustments" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">{t('common.cancel')}</Link></div>
        </form>
      </div>
    </AdminLayout>
  );
}

const inputClass = 'w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40';
function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) { return <div><label className="mb-1 block text-sm text-slate-300">{label}</label>{children}{error ? <p className="mt-1 text-xs text-rose-300">{error}</p> : null}</div>; }


