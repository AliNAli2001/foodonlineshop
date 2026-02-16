import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

export default function CategoriesIndex() {
  const { t } = useI18n();
  const page = usePage<any>();
  const categories = page.props.categories;
  const rows = Array.isArray(categories?.data) ? categories.data : Array.isArray(categories) ? categories : [];

  const removeCategory = (id: number) => {
    if (!window.confirm(t('admin.pages.categories.index.deleteConfirm'))) return;
    router.delete(`/admin/categories/${id}`);
  };

  return (
    <AdminLayout title={t('admin.pages.categories.index.title')}>
      <div className="mx-auto max-w-6xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.categories.index.heading')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.categories.index.subtitle')}</p>
          </div>
          <Link href="/admin/categories/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">{t('admin.pages.categories.index.addCategory')}</Link>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]"><tr>{[
                t('admin.pages.categories.index.columns.id'),
                t('admin.pages.categories.index.columns.arabicName'),
                t('admin.pages.categories.index.columns.englishName'),
                t('admin.pages.categories.index.columns.featured'),
                t('common.actions'),
              ].map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
              <tbody>
                {rows.length === 0 ? <tr><td colSpan={5} className="px-4 py-8 text-center text-sm text-slate-400">{t('admin.pages.categories.index.empty')}</td></tr> : rows.map((cat: any) => (
                  <tr key={cat.id} className="border-t border-white/10">
                    <td className="px-4 py-3 text-sm text-slate-200">{cat.id}</td>
                    <td className="px-4 py-3 text-sm text-slate-200">{cat.name_ar}</td>
                    <td className="px-4 py-3 text-sm text-slate-200">{cat.name_en}</td>
                    <td className="px-4 py-3 text-sm">{cat.featured ? <span className="rounded-full bg-emerald-400/20 px-2 py-0.5 text-xs text-emerald-200 ring-1 ring-emerald-300/30">{t('common.yes')}</span> : <span className="rounded-full bg-slate-300/20 px-2 py-0.5 text-xs text-slate-200 ring-1 ring-slate-300/30">{t('common.no')}</span>}</td>
                    <td className="px-4 py-3"><div className="flex flex-wrap gap-2"><Link href={`/admin/categories/${cat.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('common.view')}</Link><Link href={`/admin/categories/${cat.id}/edit`} className="rounded-lg border border-amber-300/30 bg-amber-400/10 px-2.5 py-1 text-xs text-amber-200 hover:bg-amber-400/20">{t('common.edit')}</Link><button onClick={() => removeCategory(cat.id)} className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2.5 py-1 text-xs text-rose-200 hover:bg-rose-500/20">{t('common.delete')}</button></div></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {categories?.links && <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">{categories.links.map((link: any, i: number) => <Link key={`${link.label}-${i}`} href={link.url || '#'} preserveScroll className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-cyan-400 text-slate-950' : link.url ? 'bg-white/5 text-slate-200 hover:bg-white/10' : 'cursor-not-allowed bg-white/5 text-slate-500'}`} dangerouslySetInnerHTML={{ __html: link.label }} />)}</div>}
        </section>
      </div>
    </AdminLayout>
  );
}


