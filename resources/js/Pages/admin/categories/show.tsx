import React, { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

export default function CategoriesShow() {
  const { t } = useI18n();
  const { category, products, previousCategory, nextCategory } = usePage<any>().props;
  const rows = Array.isArray(products?.data) ? products.data : [];
  const image = category?.category_image ? `/storage/${category.category_image}` : null;
  const [activeImageUrl, setActiveImageUrl] = useState<string | null>(null);

  useEffect(() => {
    if (!activeImageUrl) return;
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setActiveImageUrl(null);
    };
    window.addEventListener('keydown', onKeyDown);
    return () => window.removeEventListener('keydown', onKeyDown);
  }, [activeImageUrl]);

  return (
    <AdminLayout title={t('admin.pages.categories.show.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div><h1 className="text-2xl font-bold text-white">{category.name_en}</h1><p className="text-sm text-slate-300">{t('admin.pages.categories.show.subtitle')}</p></div>
          <div className="flex gap-2"><Link href={`/admin/categories/${category.id}/edit`} className="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">{t('common.edit')}</Link><Link href="/admin/categories" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('common.back')}</Link></div>
        </section>

        <section className="grid gap-6 lg:grid-cols-2">
          <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
            <h2 className="mb-3 text-lg font-semibold text-white">{t('admin.pages.categories.show.categoryInformation')}</h2>
            <p className="text-sm text-slate-200"><span className="text-slate-400">{t('admin.pages.categories.form.arabicName')}: </span>{category.name_ar}</p>
            <p className="text-sm text-slate-200"><span className="text-slate-400">{t('admin.pages.categories.form.englishName')}: </span>{category.name_en}</p>
            <p className="text-sm text-slate-200"><span className="text-slate-400">{t('admin.pages.categories.form.featured')}: </span>{category.featured ? t('common.yes') : t('common.no')}</p>
            {image && (
              <button type="button" onClick={() => setActiveImageUrl(image)} className="mt-3 block w-fit">
                <img src={image} alt={category.name_en} className="h-48 rounded-xl object-cover cursor-zoom-in transition hover:opacity-90" />
              </button>
            )}
          </article>

          <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
            <h2 className="mb-3 text-lg font-semibold text-white">{t('admin.pages.categories.show.navigate', 'Navigate')}</h2>
            <div className="space-y-2">
              {previousCategory ? (
                <Link href={`/admin/categories/${previousCategory.id}`} className="block rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
                  {t('admin.pages.categories.show.previousCategory', 'Previous Category')}
                </Link>
              ) : (
                <div className="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-500">{t('admin.pages.categories.show.noPreviousCategory', 'No previous category')}</div>
              )}

              {nextCategory ? (
                <Link href={`/admin/categories/${nextCategory.id}`} className="block rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
                  {t('admin.pages.categories.show.nextCategory', 'Next Category')}
                </Link>
              ) : (
                <div className="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-500">{t('admin.pages.categories.show.noNextCategory', 'No next category')}</div>
              )}
            </div>
          </article>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="flex items-center justify-between border-b border-white/10 px-4 py-3"><h2 className="text-lg font-semibold text-white">{t('admin.pages.categories.show.products')} ({products?.total || rows.length})</h2><Link href="/admin/products/create" className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('admin.pages.categories.show.addProduct')}</Link></div>
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]"><tr>{[
                t('admin.pages.categories.show.columns.name'),
                t('admin.pages.categories.show.columns.price'),
                t('admin.pages.categories.show.columns.stock'),
                t('admin.pages.categories.show.columns.featured'),
                t('common.actions'),
              ].map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
              <tbody>
                {rows.length === 0 ? <tr><td colSpan={5} className="px-4 py-8 text-center text-sm text-slate-400">{t('admin.pages.categories.show.emptyProducts')}</td></tr> : rows.map((p: any) => (
                  <tr key={p.id} className="border-t border-white/10">
                    <td className="px-4 py-3 text-sm text-slate-200">{p.name_en || p.name_ar}</td>
                    <td className="px-4 py-3 text-sm text-slate-200">${Number(p.selling_price ?? p.price ?? 0).toFixed(2)}</td>
                    <td className="px-4 py-3 text-sm text-slate-200">{p.stock_available_quantity ?? p.inventory?.stock_quantity ?? 0}</td>
                    <td className="px-4 py-3 text-sm text-slate-200">{p.featured ? t('common.yes') : t('common.no')}</td>
                    <td className="px-4 py-3"><div className="flex gap-2"><Link href={`/admin/products/${p.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('common.view')}</Link><Link href={`/admin/products/${p.id}/edit`} className="rounded-lg border border-amber-300/30 bg-amber-400/10 px-2.5 py-1 text-xs text-amber-200 hover:bg-amber-400/20">{t('common.edit')}</Link></div></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {products?.links && <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">{products.links.map((link: any, i: number) => <Link key={`${link.label}-${i}`} href={link.url || '#'} preserveScroll className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-cyan-400 text-slate-950' : link.url ? 'bg-white/5 text-slate-200 hover:bg-white/10' : 'cursor-not-allowed bg-white/5 text-slate-500'}`} dangerouslySetInnerHTML={{ __html: link.label }} />)}</div>}
        </section>
      </div>

      {activeImageUrl && (
        <div className="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/90 p-4" onClick={() => setActiveImageUrl(null)}>
          <button
            type="button"
            onClick={(e) => {
              e.stopPropagation();
              setActiveImageUrl(null);
            }}
            className="absolute right-4 top-4 z-[91] inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/30 bg-slate-900/85 text-xl font-bold text-white shadow-lg hover:bg-slate-800"
            aria-label={t('common.close', 'Close')}
            title={t('common.close', 'Close')}
          >
            x
          </button>
          <img src={activeImageUrl} alt="category enlarged" className="max-h-[90vh] max-w-[90vw] rounded-xl object-contain" onClick={(e) => e.stopPropagation()} />
        </div>
      )}
    </AdminLayout>
  );
}


