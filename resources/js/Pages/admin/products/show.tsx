import React, { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type ProductTag = { id: number; name_en?: string; name_ar?: string };
type ProductImage = { id: number; full_url?: string; image_url?: string; is_primary?: boolean };
type ProductSummary = { id: number };
type ProductEntity = {
    id: number;
    name_en?: string;
    name_ar?: string;
    description_en?: string;
    description_ar?: string;
    selling_price?: number | string;
    stock_available_quantity?: number | string;
    minimum_alert_quantity?: number | string;
    max_order_item?: number | string;
    featured?: boolean;
    company?: { name_en?: string; name_ar?: string };
    category?: { name_en?: string; name_ar?: string };
    tags?: ProductTag[];
    images?: ProductImage[];
};
type PageProps = { product: ProductEntity; previousProduct?: ProductSummary | null; nextProduct?: ProductSummary | null };

function stockState(product: ProductEntity): 'low' | 'healthy' {
    const stock = Number(product.stock_available_quantity ?? 0);
    const min = Number(product.minimum_alert_quantity ?? 0);
    return min > 0 && stock < min ? 'low' : 'healthy';
}

export default function ProductsShow() {
  const { t } = useI18n();
    const { product, previousProduct, nextProduct } = usePage<PageProps>().props;
    const [activeImageUrl, setActiveImageUrl] = useState<string | null>(null);
    const tags = product.tags ?? [];
    const images = product.images ?? [];

    useEffect(() => {
        if (!activeImageUrl) return;
        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') setActiveImageUrl(null);
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [activeImageUrl]);

    return (
        <AdminLayout title={`${t('admin.pages.products.show.title')} #${product.id}`}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{product.name_en || product.name_ar}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.products.show.subtitle')}</p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Link href={`/admin/inventory/${product.id}/batches`} className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">{t('admin.pages.products.show.inventory')}</Link>
                        <Link href={`/admin/products/${product.id}/edit`} className="rounded-xl bg-cyan-400 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">{t('common.edit')}</Link>
                        <Link href="/admin/products" className="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">{t('common.back')}</Link>
                    </div>
                </section>

                <div className="grid gap-6 lg:grid-cols-3">
                    <section className="space-y-6 lg:col-span-2">
                        <div className="grid gap-4 md:grid-cols-2">
                            <InfoCard title={t('admin.pages.products.show.arabicInformation')}>
                                <Info label={t('admin.pages.products.show.name')} value={product.name_ar || '-'} />
                                <Info label={t('admin.pages.products.show.description')} value={product.description_ar || '-'} />
                                <Info label={t('admin.pages.products.show.company')} value={product.company?.name_ar || product.company?.name_en || '-'} />
                                <Info label={t('admin.pages.products.show.category')} value={product.category?.name_ar || product.category?.name_en || '-'} />
                            </InfoCard>

                            <InfoCard title={t('admin.pages.products.show.englishInformation')}>
                                <Info label={t('admin.pages.products.show.name')} value={product.name_en || '-'} />
                                <Info label={t('admin.pages.products.show.description')} value={product.description_en || '-'} />
                                <Info label={t('admin.pages.products.show.company')} value={product.company?.name_en || product.company?.name_ar || '-'} />
                                <Info label={t('admin.pages.products.show.category')} value={product.category?.name_en || product.category?.name_ar || '-'} />
                            </InfoCard>
                        </div>

                        <InfoCard title={t('admin.pages.products.show.pricingStock')}>
                            <div className="grid gap-2 sm:grid-cols-2 text-sm">
                                <Info label={t('admin.pages.products.show.price')} value={`$${Number(product.selling_price ?? 0).toFixed(2)}`} />
                                <Info label={t('admin.pages.products.show.availableStock')} value={String(product.stock_available_quantity ?? 0)} />
                                <Info label={t('admin.pages.products.show.minAlertQty')} value={String(product.minimum_alert_quantity ?? '-')} />
                                <Info label={t('admin.pages.products.show.stockStatus')} value={stockState(product) === 'low' ? t('admin.pages.products.show.lowStock') : t('admin.pages.products.show.healthy')} />
                                <Info label={t('admin.pages.products.show.maxOrderItem')} value={String(product.max_order_item ?? '-')} />
                                <Info label={t('admin.pages.products.show.featured')} value={product.featured ? t('admin.pages.products.show.yes') : t('admin.pages.products.show.no')} />
                            </div>
                        </InfoCard>

                        <InfoCard title={t('admin.pages.products.show.tags')}>
                            {tags.length > 0 ? (
                                <div className="flex flex-wrap gap-2">
                                    {tags.map((tag: ProductTag) => (
                                        <span key={tag.id} className="rounded-full border border-cyan-300/30 bg-cyan-400/10 px-3 py-1 text-xs text-cyan-200">
                                            {tag.name_en || tag.name_ar}
                                        </span>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-slate-400">{t('admin.pages.products.show.noTagsAssigned')}</p>
                            )}
                        </InfoCard>

                        <InfoCard title={t('admin.pages.products.show.productImages')}>
                            {images.length > 0 ? (
                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    {images.map((img: ProductImage) => {
                                        const url = img.full_url || (img.image_url ? `/storage/${img.image_url}` : '');
                                        return (
                                            <div key={img.id} className="rounded-lg border border-white/10 bg-slate-900/40 p-2">
                                                <button type="button" onClick={() => setActiveImageUrl(url)} className="w-full">
                                                    <img src={url} alt="product" className="h-40 w-full rounded-lg object-cover transition hover:opacity-90 cursor-zoom-in" />
                                                </button>
                                                {img.is_primary ? <span className="mt-2 inline-flex rounded-full bg-cyan-400/20 px-2 py-0.5 text-xs text-cyan-200">{t('admin.pages.products.show.primary')}</span> : null}
                                            </div>
                                        );
                                    })}
                                </div>
                            ) : (
                                <p className="text-sm text-slate-400">{t('admin.pages.products.show.noImagesAvailable')}</p>
                            )}
                        </InfoCard>
                    </section>

                    <aside className="space-y-4">
                        <InfoCard title={t('admin.pages.products.show.navigate')}>
                            <div className="space-y-2">
                                {previousProduct ? (
                                    <Link href={`/admin/products/${previousProduct.id}`} className="block rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
                                        {t('admin.pages.products.show.previousProduct')}
                                    </Link>
                                ) : (
                                    <div className="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-500">{t('admin.pages.products.show.noPreviousProduct')}</div>
                                )}

                                {nextProduct ? (
                                    <Link href={`/admin/products/${nextProduct.id}`} className="block rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">
                                        {t('admin.pages.products.show.nextProduct')}
                                    </Link>
                                ) : (
                                    <div className="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-500">{t('admin.pages.products.show.noNextProduct')}</div>
                                )}
                            </div>
                        </InfoCard>
                    </aside>
                </div>
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
                    <img
                        src={activeImageUrl}
                        alt="product enlarged"
                        className="max-h-[90vh] max-w-[90vw] rounded-xl object-contain"
                        onClick={(e) => e.stopPropagation()}
                    />
                </div>
            )}
        </AdminLayout>
    );
}

function Info({ label, value }: { label: string; value: string }) {
    return (
        <p className="text-sm text-slate-200">
            <span className="text-slate-400">{label}: </span>
            {value}
        </p>
    );
}

function InfoCard({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
            <h2 className="mb-3 text-lg font-semibold text-white">{title}</h2>
            {children}
        </section>
    );
}


