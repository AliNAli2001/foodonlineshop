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
  const { t, isRtl } = useI18n();
    const page = usePage<any>();
    const products = page.props.products;
    const tags = Array.isArray(page.props.tags) ? page.props.tags : [];
    const params = queryFromUrl(page.url || '');
    const productRows = Array.isArray(products?.data) ? products.data : Array.isArray(products) ? products : [];
    const [filtersOpen, setFiltersOpen] = useState(false);

    const hasActiveFilters = useMemo(() => {
        const tracked = ['search', 'sort', 'low_stock', 'min_price', 'max_price', 'min_stock', 'max_stock'];
        if (tracked.some((key) => (params.get(key) || '').toString().trim() !== '')) return true;
        if (params.getAll('tags').length > 0) return true;
        const order = (params.get('order') || 'desc').toLowerCase();
        return order !== 'desc';
    }, [page.url]);

    const applyFilters = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        const query: Record<string, FormDataEntryValue | FormDataEntryValue[]> = {};

        for (const [key, value] of formData.entries()) {
            if (value === '' || value === null) continue;
            if (key in query) {
                const current = query[key];
                query[key] = Array.isArray(current) ? [...current, value] : [current, value];
                continue;
            }
            query[key] = value;
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

                <div className={`fixed top-1/2 z-[60] -translate-y-1/2 ${isRtl ? 'left-3' : 'right-3'}`}>
                    <div className="flex flex-col items-center gap-2">
                        <button
                            type="button"
                            onClick={() => setFiltersOpen((prev) => !prev)}
                            className="rounded-full border border-cyan-300/30 bg-slate-900/95 p-3 text-cyan-200 shadow-lg backdrop-blur transition hover:bg-slate-800"
                            aria-label={filtersOpen ? t('admin.pages.products.index.filters.hideFilters') : t('admin.pages.products.index.filters.showFilters')}
                            title={filtersOpen ? t('admin.pages.products.index.filters.hideFilters') : t('admin.pages.products.index.filters.showFilters')}
                        >
                            {filtersOpen ? (
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            ) : (
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                            )}
                        </button>
                        {hasActiveFilters && (
                            <Link
                                href="/admin/products"
                                className="rounded-xl border border-rose-300/30 bg-rose-500/10 px-3 py-2 text-xs font-medium text-rose-200 shadow-lg transition hover:bg-rose-500/20"
                            >
                                {t('admin.pages.products.index.filters.clearActive')}
                            </Link>
                        )}
                    </div>
                </div>

                <div
                    className={`fixed inset-0 z-40 bg-slate-950/65 transition-opacity duration-300 ${
                        filtersOpen ? 'opacity-100' : 'pointer-events-none opacity-0'
                    }`}
                    onClick={() => setFiltersOpen(false)}
                />

                <aside
                    className={`fixed inset-y-0 z-50 w-full max-w-md border-white/10 bg-slate-900/95 p-5 shadow-2xl backdrop-blur transition-transform duration-300 sm:w-[28rem] ${
                        isRtl ? 'left-0 border-r' : 'right-0 border-l'
                    } ${filtersOpen ? 'translate-x-0' : isRtl ? '-translate-x-full' : 'translate-x-full'}`}
                >
                    <form onSubmit={applyFilters} className="flex h-full flex-col">
                        <div className="flex items-center justify-between gap-2 border-b border-white/10 pb-3">
                            <div>
                                <h3 className="text-base font-semibold text-white">{t('admin.pages.products.index.filters.showFilters')}</h3>
                                <p className="text-xs text-slate-400">{t('admin.pages.products.index.subtitle')}</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setFiltersOpen(false)}
                                className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:bg-white/10"
                            >
                                {t('common.close', 'Close')}
                            </button>
                        </div>

                        <div className="mt-4 grid gap-3 overflow-y-auto pr-1">
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

                            <section className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                                <h4 className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">{t('admin.pages.products.index.filters.tags')}</h4>
                                <div className="mt-2 max-h-44 space-y-2 overflow-y-auto pr-1">
                                    {tags.length === 0 ? (
                                        <p className="text-xs text-slate-500">-</p>
                                    ) : (
                                        tags.map((tag: any) => (
                                            <label key={tag.id} className="flex items-center gap-2 text-sm text-slate-200">
                                                <input type="checkbox" name="tags" value={tag.id} defaultChecked={params.getAll('tags').includes(String(tag.id))} />
                                                <span>{tag.name_ar || tag.name_en || `#${tag.id}`}</span>
                                            </label>
                                        ))
                                    )}
                                </div>
                            </section>
                        </div>

                        <div className={`mt-4 flex gap-2 border-t border-white/10 pt-4 ${isRtl ? 'flex-row-reverse' : ''}`}>
                            <button className="w-full rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">{t('admin.pages.products.index.filters.apply')}</button>
                            <Link href="/admin/products" className="w-full rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-center text-sm text-slate-200 hover:bg-white/10">{t('admin.pages.products.index.filters.reset')}</Link>
                        </div>
                    </form>
                </aside>

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
                                                <td className="px-4 py-3"><img src={img} alt={product.name_ar || product.name_en || 'product'} onError={(e) => { (e.currentTarget as HTMLImageElement).src = PHOTO_PLACEHOLDER; }} className="h-14 w-14 rounded-lg object-cover ring-1 ring-white/10" /></td>
                                                <td className="px-4 py-3 text-sm text-slate-100">{product.name_ar || product.name_en || '-'}</td>
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
