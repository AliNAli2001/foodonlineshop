import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

export default function CompaniesCreate() {
  const { t } = useI18n();
  const { data, setData, post, processing, errors } = useForm({
    entries: [{ name_ar: '', name_en: '' }],
  });

  const submit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    post('/admin/companies');
  };

  const addRow = () => {
    setData('entries', [...data.entries, { name_ar: '', name_en: '' }]);
  };

  const removeRow = (index: number) => {
    if (data.entries.length <= 1) return;
    setData('entries', data.entries.filter((_, i) => i !== index));
  };

  const updateEntry = (index: number, key: 'name_ar' | 'name_en', value: string) => {
    setData(
      'entries',
      data.entries.map((entry, i) => (i === index ? { ...entry, [key]: value } : entry)),
    );
  };

  return (
    <AdminLayout title={t('admin.pages.companies.create.title')}>
      <div className="mx-auto max-w-3xl space-y-6">
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5"><h1 className="text-2xl font-bold text-white">{t('admin.pages.companies.create.heading')}</h1></section>
        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          {data.entries.map((entry, index) => (
            <div key={index} className="rounded-xl border border-white/10 bg-white/[0.03] p-3 space-y-3">
              <div className="flex items-center justify-between">
                <p className="text-sm font-semibold text-slate-200">{t('admin.pages.companies.create.heading')} #{index + 1}</p>
                <button type="button" onClick={() => removeRow(index)} className="rounded-lg border border-rose-300/40 bg-rose-50 px-2.5 py-1 text-xs text-rose-700 hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-500/10 dark:text-rose-200 dark:hover:bg-rose-500/20">
                  {t('common.delete')}
                </button>
              </div>
              <Field label={t('admin.pages.companies.form.arabicName')} error={(errors as any)[`entries.${index}.name_ar`]}><input value={entry.name_ar} onChange={(e) => updateEntry(index, 'name_ar', e.target.value)} className={inputClass} required /></Field>
              <Field label={t('admin.pages.companies.form.englishName')} error={(errors as any)[`entries.${index}.name_en`]}><input value={entry.name_en} onChange={(e) => updateEntry(index, 'name_en', e.target.value)} className={inputClass} required /></Field>
            </div>
          ))}
          <button type="button" onClick={addRow} className="rounded-xl border border-cyan-300/40 bg-cyan-50 px-4 py-2 text-sm text-cyan-700 hover:bg-cyan-100 dark:border-cyan-400/30 dark:bg-cyan-400/10 dark:text-cyan-200 dark:hover:bg-cyan-400/20">
            + {t('common.create', 'Create')} Row
          </button>
          <div className="flex gap-3"><button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">{processing ? t('admin.pages.companies.form.saving') : t('admin.pages.companies.create.submit')}</button><Link href="/admin/companies" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">{t('common.cancel')}</Link></div>
        </form>
      </div>
    </AdminLayout>
  );
}

const inputClass = 'w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40';
function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) { return <div><label className="mb-1 block text-sm text-slate-300">{label}</label>{children}{error ? <p className="mt-1 text-xs text-rose-300">{error}</p> : null}</div>; }


