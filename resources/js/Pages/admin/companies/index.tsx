import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

export default function CompaniesIndex() {
  const { t } = useI18n();
  const page = usePage<any>();
  const companies = page.props.companies;
  const rows = Array.isArray(companies?.data) ? companies.data : Array.isArray(companies) ? companies : [];

  const removeCompany = (id: number) => {
    if (!window.confirm(t('admin.pages.companies.index.deleteConfirm'))) return;
    router.delete(`/admin/companies/${id}`);
  };

  return (
    <AdminLayout title={t('admin.pages.companies.index.title')}>
      <div className="mx-auto max-w-6xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.companies.index.heading')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.companies.index.subtitle')}</p>
          </div>
          <Link href="/admin/companies/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">{t('admin.pages.companies.index.addCompany')}</Link>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]"><tr>{[
                t('admin.pages.companies.index.columns.id'),
                t('admin.pages.companies.index.columns.englishName'),
                t('admin.pages.companies.index.columns.arabicName'),
                t('common.actions'),
              ].map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
              <tbody>
                {rows.length === 0 ? <tr><td colSpan={4} className="px-4 py-8 text-center text-sm text-slate-400">{t('admin.pages.companies.index.empty')}</td></tr> : rows.map((c: any) => (
                  <tr key={c.id} className="border-t border-white/10">
                    <td className="px-4 py-3 text-sm text-slate-200">{c.id}</td>
                    <td className="px-4 py-3 text-sm text-slate-200">{c.name_en}</td>
                    <td className="px-4 py-3 text-sm text-slate-200">{c.name_ar}</td>
                    <td className="px-4 py-3"><div className="flex flex-wrap gap-2"><Link href={`/admin/companies/${c.id}`} className="rounded-lg border border-cyan-400/40 bg-cyan-50 px-2.5 py-1 text-xs text-cyan-700 hover:bg-cyan-100 dark:border-cyan-300/30 dark:bg-cyan-400/10 dark:text-cyan-200 dark:hover:bg-cyan-400/20">{t('common.view')}</Link><Link href={`/admin/companies/${c.id}/edit`} className="rounded-lg border border-amber-400/40 bg-amber-50 px-2.5 py-1 text-xs text-amber-700 hover:bg-amber-100 dark:border-amber-300/30 dark:bg-amber-400/10 dark:text-amber-200 dark:hover:bg-amber-400/20">{t('common.edit')}</Link><button onClick={() => removeCompany(c.id)} className="rounded-lg border border-rose-400/40 bg-rose-50 px-2.5 py-1 text-xs text-rose-700 hover:bg-rose-100 dark:border-rose-300/30 dark:bg-rose-500/10 dark:text-rose-200 dark:hover:bg-rose-500/20">{t('common.delete')}</button></div></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {companies?.links && <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">{companies.links.map((link: any, i: number) => <Link key={`${link.label}-${i}`} href={link.url || '#'} preserveScroll className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-cyan-400 text-slate-950' : link.url ? 'bg-white/5 text-slate-200 hover:bg-white/10' : 'cursor-not-allowed bg-white/5 text-slate-500'}`} dangerouslySetInnerHTML={{ __html: link.label }} />)}</div>}
        </section>
      </div>
    </AdminLayout>
  );
}


