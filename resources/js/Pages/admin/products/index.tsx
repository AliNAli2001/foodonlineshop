import React from 'react';
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

export default function ProductsIndex() {
  const { t } = useI18n();
    const page = usePage<any>();
    const products = page.props.products;
    const params = queryFromUrl(page.url || '');
    const productRows = Array.isArray(products?.data) ? products.data : Array.isArray(products) ? products : [];

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
        if (!window.confirm('Delete this product?')) return;
        router.delete(`/admin/products/${id}`);
    };

    return (
        <AdminLayout title={t('admin.pages.products.index.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Products</h1>
                        <p className="text-sm text-slate-300">Browse, filter, and manage catalog items.</p>
                    </div>
                    <Link href="/admin/products/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
                        + Add Product
                    </Link>
                </section>

                <form onSubmit={applyFilters} className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <input name="search" defaultValue={params.get('search') || ''} placeholder="Search by id or name" className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                        <select name="sort" defaultValue={params.get('sort') || ''} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white">
                            <option value="">Sort by</option>
                            <option value="stock">Stock</option>
                            <option value="price">Price</option>
                            <option value="created_at">Created Date</option>
                        </select>
                        <select name="order" defaultValue={params.get('order') || 'desc'} className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white">
                            <option value="desc">Descending</option>
                            <option value="asc">Ascending</option>
                        </select>
                        <label className="flex items-center gap-2 rounded-xl border border-white/10 bg-slate-900/40 px-3 py-2 text-sm text-slate-200">
                            <input type="checkbox" name="low_stock" value="1" defaultChecked={params.has('low_stock')} />
                            Low stock only
                        </label>
                        <input name="min_price" type="number" step="0.01" defaultValue={params.get('min_price') || ''} placeholder="Min price" className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                        <input name="max_price" type="number" step="0.01" defaultValue={params.get('max_price') || ''} placeholder="Max price" className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                        <input name="min_stock" type="number" defaultValue={params.get('min_stock') || ''} placeholder="Min stock" className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                        <input name="max_stock" type="number" defaultValue={params.get('max_stock') || ''} placeholder="Max stock" className="rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white" />
                    </div>
                    <div className="mt-3 flex gap-2">
                        <button className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">Apply Filters</button>
                        <Link href="/admin/products" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Reset</Link>
                    </div>
                </form>

                <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
                    <div className="overflow-x-auto">
                        <table className="min-w-full">
                            <thead className="bg-white/[0.03]"><tr>{['ID', 'Image', 'Name', 'Price', 'Stock', 'Actions'].map((h) => <th key={h} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
                            <tbody>
                                {productRows.length === 0 ? (
                                    <tr><td colSpan={6} className="px-4 py-8 text-center text-sm text-slate-400">No products found.</td></tr>
                                ) : (
                                    productRows.map((product: any) => {
                                        const img = product.primary_image?.full_url || product.primaryImage?.full_url;
                                        const stock = Number(product.stock_available_quantity ?? product.stock?.available_quantity ?? 0);
                                        return (
                                            <tr key={product.id} className="border-t border-white/10">
                                                <td className="px-4 py-3 text-sm text-slate-200">{product.id}</td>
                                                <td className="px-4 py-3">{img ? <img src={img} alt={product.name_en} className="h-14 w-14 rounded-lg object-cover" /> : <div className="h-14 w-14 rounded-lg bg-white/5" />}</td>
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


