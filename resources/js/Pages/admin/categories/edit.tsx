import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

export default function CategoriesEdit() {
  const { t } = useI18n();
  const { category } = usePage<any>().props;
  const { data, setData, post, processing, errors } = useForm({
    _method: 'put',
    name_ar: category.name_ar ?? '',
    name_en: category.name_en ?? '',
    featured: !!category.featured,
    category_image: null as File | null,
  });

  const submit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    post(`/admin/categories/${category.id}`, { forceFormData: true });
  };

  const currentImage = category.category_image ? `/storage/${category.category_image}` : null;

  return (
    <AdminLayout title={t('admin.pages.categories.edit.title')}>
      <div className="mx-auto max-w-3xl space-y-6">
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5"><h1 className="text-2xl font-bold text-white">{t('admin.pages.categories.edit.heading')}</h1></section>
        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <Field label={t('admin.pages.categories.form.arabicName')} error={errors.name_ar}><input value={data.name_ar} onChange={(e) => setData('name_ar', e.target.value)} className={inputClass} required /></Field>
          <Field label={t('admin.pages.categories.form.englishName')} error={errors.name_en}><input value={data.name_en} onChange={(e) => setData('name_en', e.target.value)} className={inputClass} required /></Field>
          {currentImage && <img src={currentImage} alt={category.name_en} className="h-36 rounded-xl object-cover" />}
          <Field label={t('admin.pages.categories.form.categoryImage')} error={errors.category_image}><input type="file" accept="image/*" onChange={(e) => setData('category_image', e.target.files?.[0] || null)} className={inputClass} /></Field>
          <label className="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" checked={data.featured} onChange={(e) => setData('featured', e.target.checked)} /> {t('admin.pages.categories.form.featured')}</label>
          <div className="flex gap-3"><button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">{processing ? t('admin.pages.categories.form.updating') : t('admin.pages.categories.edit.submit')}</button><Link href="/admin/categories" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">{t('common.cancel')}</Link></div>
        </form>
      </div>
    </AdminLayout>
  );
}

const inputClass = 'w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40';
function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) { return <div><label className="mb-1 block text-sm text-slate-300">{label}</label>{children}{error ? <p className="mt-1 text-xs text-rose-300">{error}</p> : null}</div>; }


