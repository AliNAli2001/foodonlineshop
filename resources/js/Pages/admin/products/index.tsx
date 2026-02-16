import React, { useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

function queryFromUrl(url: string) {
    const raw = url?.includes('?') ? url.split('?')[1] : '';
    return new URLSearchParams(raw || '');
}

function statusPill(product: any) {
    const stock = Number(product.stock_available_quantity ?? product.stock?.available_quantity ?? 0);
    const min = Number(product.minimum_alert_quantity ?? 0);
    const low = min > 0 && stock < min;
    return low
        ? 'inline-flex rounded-full bg-rose-400/20 px-2 py-0.5 text-xs font-semibold text-rose-200 ring-1 ring-rose-300/30'
        : 'inline-flex rounded-full bg-emerald-400/20 px-2 py-0.5 text-xs font-semibold text-emerald-200 ring-1 ring-emerald-300/30';
}

const PHOTO_PLACEHOLDER =
    "data:image/svg+xml;utf8," +
    encodeURIComponent(
        "<svg xmlns='http://www.w3.org/2000/svg' width='112' height='112'><rect width='100%' height='100%' rx='10' fill='%231e293b'/><g fill='%2394a3b8'><circle cx='36' cy='42' r='9'/><rect x='20' y='58' width='72' height='24' rx='6'/></g></svg>",
    );

function resolveProductImage(product: any) {
    const candidates = [
        product?.primary_image?.full_url,
        product?.primaryImage?.full_url,
        product?.primary_image?.image_url,
        product?.primaryImage?.image_url,
        product?.images?.[0]?.full_url,
        product?.images?.[0]?.image_url,
    ];

    for (const candidate of candidates) {
        if (!candidate || typeof candidate !== 'string') continue;
        if (candidate.startsWith('http://') || candidate.startsWith('https://') || candidate.startsWith('data:') || candidate.startsWith('/')) {
            return candidate;
        }
        return `/storage/${candidate.replace(/^\/+/, '')}`;
    }

    return PHOTO_PLACEHOLDER;
}

export default function ProductsIndex() {
  const { t } = useI18n();
    const page = usePage<any>();
    const products = page.props.products;
    const params = queryFromUrl(page.url || '');
    const productRows = Array.isArray(products?.data) ? products.data : Array.isArray(products) ? products : [];
    const [filtersOpen, setFiltersOpen] = useState(false);

    const hasActiveFilters = useMemo(() => {
        const tracked = ['search', 'sort', 'low_stock', 'min_price', 'max_price', 'min_stock', 'max_stock'];
        if (tracked.some((key) => (params.get(key) || '').toString().trim() !== '')) return true;
        const order = (params.get('order') || 'desc').toLowerCase();
        return order !== 'desc';
    }, [page.url]);

    const applyFilters = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        const query: Record<string, FormDataEntryValue> = {};

        for (const [key, value] of formData.entries()) {
            if (value !== '' && value !== null) query[key] = value;
        }

        router.get('/admin/products', query, { preserveState: true, preserveScroll: true });
    };

    const removeProduct = (id: number) => {
        if (!window.confirm(t('admin.pages.products.index.deleteConfirm', 'Delete this product?'))) return;
        router.delete(`/admin/products/${id}`);
    };

    return (
        <AdminLayout title={t('admin.pages.products.index.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.products.index.title')}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.products.index.subtitle')}</p>
                    </div>
                    <Link href="/admin/products/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
                        + {t('admin.pages.products.index.addProduct')}
                    </Link>
                </section>

                <form onSubmit={applyFilters} className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <button
                            type="button"
                            onClick={() => setFiltersOpen((prev) => !prev)}
                            className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10"
                        >
                            {filtersOpen ? t('admin.pages.products.index.filters.hideFilters') : t('admin.pages.products.index.filters.showFilters')}
                        </button>
                        {hasActiveFilters && (
                            <Link href="/admin/products" className="rounded-xl border border-rose-300/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-200 hover:bg-rose-500/20">
                                {t('admin.pages.products.index.filters.clearActive')}
                            </Link>
                        )}
                    </div>

                    {filtersOpen && (
                        <>
                            <div className="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <input name="search" defaultValue={params.get('search') || ''} placeholder={t('admin.pages.products.index.filters.searchPlaceholder')} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                                <select name="sort" defaultValue={params.get('sort') || ''} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white">
                                    <option value="">{t('admin.pages.products.index.filters.sortBy')}</option>
                                    <option value="stock">{t('admin.pages.products.index.filters.stock')}</option>
                                    <option value="price">{t('admin.pages.products.index.filters.price')}</option>
                                    <option value="created_at">{t('admin.pages.products.index.filters.createdDate')}</option>
                                </select>
                                <select name="order" defaultValue={params.get('order') || 'desc'} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white">
                                    <option value="desc">{t('admin.pages.products.index.filters.descending')}</option>
                                    <option value="asc">{t('admin.pages.products.index.filters.ascending')}</option>
                                </select>
                                <label className="flex items-center gap-2 rounded-xl border border-white/10 bg-slate-900/40 px-3 py-2 text-sm text-slate-200">
                                    <input type="checkbox" name="low_stock" value="1" defaultChecked={params.has('low_stock')} />
                                    {t('admin.pages.products.index.filters.lowStockOnly')}
                                </label>
                                <input name="min_price" type="number" step="0.01" defaultValue={params.get('min_price') || ''} placeholder={t('admin.pages.products.index.filters.minPrice')} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                                <input name="max_price" type="number" step="0.01" defaultValue={params.get('max_price') || ''} placeholder={t('admin.pages.products.index.filters.maxPrice')} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                                <input name="min_stock" type="number" defaultValue={params.get('min_stock') || ''} placeholder={t('admin.pages.products.index.filters.minStock')} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                                <input name="max_stock" type="number" defaultValue={params.get('max_stock') || ''} placeholder={t('admin.pages.products.index.filters.maxStock')} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                            </div>
                            <div className="mt-3 flex gap-2">
                                <button className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">{t('admin.pages.products.index.filters.apply')}</button>
                                <Link href="/admin/products" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('admin.pages.products.index.filters.reset')}</Link>
                            </div>
                        </>
                    )}
                </form>

                <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
                    <div className="overflow-x-auto">
                        <table className="min-w-full">
                            <thead className="bg-white/[0.03]"><tr>{[t('admin.pages.products.index.columns.id'), t('admin.pages.products.index.columns.image'), t('admin.pages.products.index.columns.name'), t('admin.pages.products.index.columns.price'), t('admin.pages.products.index.columns.stock'), t('common.actions')].map((h) => <th key={h} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
                            <tbody>
                                {productRows.length === 0 ? (
                                    <tr><td colSpan={6} className="px-4 py-8 text-center text-sm text-slate-400">{t('admin.pages.products.index.empty')}</td></tr>
                                ) : (
                                    productRows.map((product: any) => {
                                        const img = resolveProductImage(product);
                                        const stock = Number(product.stock_available_quantity ?? product.stock?.available_quantity ?? 0);
                                        return (
                                            <tr key={product.id} className="border-t border-white/10">
                                                <td className="px-4 py-3 text-sm text-slate-200">{product.id}</td>
                                                <td className="px-4 py-3"><img src={img} alt={product.name_en || product.name_ar || 'product'} onError={(e) => { (e.currentTarget as HTMLImageElement).src = PHOTO_PLACEHOLDER; }} className="h-14 w-14 rounded-lg object-cover ring-1 ring-white/10" /></td>
                                                <td className="px-4 py-3 text-sm text-slate-100">{product.name_en || product.name_ar}</td>
                                                <td className="px-4 py-3 text-sm text-slate-200">${Number(product.selling_price).toFixed(2)}</td>
                                                <td className="px-4 py-3 text-sm"><span className={statusPill(product)}>{stock}</span></td>
                                                <td className="px-4 py-3"><div className="flex flex-wrap gap-2"><Link href={`/admin/products/${product.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('common.view')}</Link><Link href={`/admin/products/${product.id}/edit`} className="rounded-lg border border-amber-300/30 bg-amber-400/10 px-2.5 py-1 text-xs text-amber-200 hover:bg-amber-400/20">{t('common.edit')}</Link><button onClick={() => removeProduct(product.id)} className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2.5 py-1 text-xs text-rose-200 hover:bg-rose-500/20">{t('common.delete')}</button></div></td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>
                    {products?.links && (<div className="flex flex-wrap gap-2 border-t border-white/10 p-4">{products.links.map((link: any, idx: number) => (<Link key={`${link.label}-${idx}`} href={link.url || '#'} preserveScroll className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-cyan-400 text-slate-950' : link.url ? 'bg-white/5 text-slate-200 hover:bg-white/10' : 'cursor-not-allowed bg-white/5 text-slate-500'}`} dangerouslySetInnerHTML={{ __html: link.label }} />))}</div>)}
                </section>
            </div>
        </AdminLayout>
    );
}


