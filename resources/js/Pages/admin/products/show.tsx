import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

function stockState(product) {
    const stock = Number(product.stock_available_quantity ?? 0);
    const min = Number(product.minimum_alert_quantity ?? 0);
    return min > 0 && stock < min ? 'Low stock' : 'Healthy';
}

export default function ProductsShow() {
    const { product, previousProduct, nextProduct } = usePage().props;

    return (
        <AdminLayout title={`Product #${product.id}`}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{product.name_en || product.name_ar}</h1>
                        <p className="text-sm text-slate-300">Product details and stock overview.</p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Link href={`/admin/inventory/${product.id}/batches`} className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">Inventory</Link>
                        <Link href={`/admin/products/${product.id}/edit`} className="rounded-xl bg-cyan-400 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">Edit</Link>
                        <Link href="/admin/products" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">Back</Link>
                    </div>
                </section>

                <div className="grid gap-6 lg:grid-cols-3">
                    <section className="space-y-6 lg:col-span-2">
                        <div className="grid gap-4 md:grid-cols-2">
                            <InfoCard title="Arabic Information">
                                <Info label="Name" value={product.name_ar || '-'} />
                                <Info label="Description" value={product.description_ar || '-'} />
                                <Info label="Company" value={product.company?.name_ar || product.company?.name_en || '-'} />
                                <Info label="Category" value={product.category?.name_ar || product.category?.name_en || '-'} />
                            </InfoCard>

                            <InfoCard title="English Information">
                                <Info label="Name" value={product.name_en || '-'} />
                                <Info label="Description" value={product.description_en || '-'} />
                                <Info label="Company" value={product.company?.name_en || product.company?.name_ar || '-'} />
                                <Info label="Category" value={product.category?.name_en || product.category?.name_ar || '-'} />
                            </InfoCard>
                        </div>

                        <InfoCard title="Pricing & Stock">
                            <div className="grid gap-2 sm:grid-cols-2 text-sm">
                                <Info label="Price" value={`$${Number(product.selling_price ?? 0).toFixed(2)}`} />
                                <Info label="Available Stock" value={String(product.stock_available_quantity ?? 0)} />
                                <Info label="Min Alert Qty" value={String(product.minimum_alert_quantity ?? '-')} />
                                <Info label="Stock Status" value={stockState(product)} />
                                <Info label="Max Order Item" value={String(product.max_order_item ?? '-')} />
                                <Info label="Featured" value={product.featured ? 'Yes' : 'No'} />
                            </div>
                        </InfoCard>

                        <InfoCard title="Tags">
                            {(product.tags || []).length > 0 ? (
                                <div className="flex flex-wrap gap-2">
                                    {product.tags.map((tag) => (
                                        <span key={tag.id} className="rounded-full border border-cyan-300/30 bg-cyan-400/10 px-3 py-1 text-xs text-cyan-200">
                                            {tag.name_en || tag.name_ar}
                                        </span>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-slate-400">No tags assigned.</p>
                            )}
                        </InfoCard>

                        <InfoCard title="Product Images">
                            {(product.images || []).length > 0 ? (
                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    {product.images.map((img) => {
                                        const url = img.full_url || (img.image_url ? `/storage/${img.image_url}` : '');
                                        return (
                                            <div key={img.id} className="rounded-lg border border-white/10 bg-slate-900/40 p-2">
                                                <img src={url} alt="product" className="h-40 w-full rounded-lg object-cover" />
                                                {img.is_primary ? <span className="mt-2 inline-flex rounded-full bg-cyan-400/20 px-2 py-0.5 text-xs text-cyan-200">Primary</span> : null}
                                            </div>
                                        );
                                    })}
                                </div>
                            ) : (
                                <p className="text-sm text-slate-400">No images available.</p>
                            )}
                        </InfoCard>
                    </section>

                    <aside className="space-y-4">
                        <InfoCard title="Navigate">
                            <div className="space-y-2">
                                {previousProduct ? (
                                    <Link href={`/admin/products/${previousProduct.id}`} className="block rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
                                        Previous Product
                                    </Link>
                                ) : (
                                    <div className="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-500">No previous product</div>
                                )}

                                {nextProduct ? (
                                    <Link href={`/admin/products/${nextProduct.id}`} className="block rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
                                        Next Product
                                    </Link>
                                ) : (
                                    <div className="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-500">No next product</div>
                                )}
                            </div>
                        </InfoCard>
                    </aside>
                </div>
            </div>
        </AdminLayout>
    );
}

function Info({ label, value }) {
    return (
        <p className="text-sm text-slate-200">
            <span className="text-slate-400">{label}: </span>
            {value}
        </p>
    );
}

function InfoCard({ title, children }) {
    return (
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
            <h2 className="mb-3 text-lg font-semibold text-white">{title}</h2>
            {children}
        </section>
    );
}
