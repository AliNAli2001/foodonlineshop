import React, { useEffect, useMemo, useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

const STEP_TOTAL = 4;

export default function ProductsEdit() {
    const { t } = useI18n();
    const { product, categories = [], tags = [], companies = [] } = usePage<any>().props;
    const [step, setStep] = useState(1);

    const { data, setData, post, processing, errors } = useForm({
        _method: 'put',
        name_ar: product.name_ar ?? '',
        name_en: product.name_en ?? '',
        description_ar: product.description_ar ?? '',
        description_en: product.description_en ?? '',
        selling_price: product.selling_price ?? '',
        max_order_item: product.max_order_item ?? '',
        minimum_alert_quantity: product.minimum_alert_quantity ?? '',
        featured: !!product.featured,
        category_id: product.category_id ?? '',
        company_id: product.company_id ?? '',
        tags: (product.tags || []).map((t: any) => t.id),
        images: [],
        image_ids_to_delete: [],
        primary_image_id: (product.images || []).find((img: any) => img.is_primary)?.id || '',
    });

    const fieldStepMap: Record<string, number> = {
        name_ar: 1,
        name_en: 1,
        description_ar: 1,
        description_en: 1,
        selling_price: 1,
        max_order_item: 1,
        minimum_alert_quantity: 1,
        featured: 1,
        company_id: 2,
        category_id: 2,
        tags: 2,
        primary_image_id: 3,
        image_ids_to_delete: 3,
        images: 4,
    };

    const stepTitles = useMemo(
        () => [
            t('admin.pages.products.create.steps.basic', 'Basic Info'),
            t('admin.pages.products.create.steps.meta', 'Company, Category, Tags'),
            t('admin.pages.products.edit.steps.currentImages', 'Current Images'),
            t('admin.pages.products.edit.steps.uploadSubmit', 'Upload & Submit'),
        ],
        [t],
    );

    useEffect(() => {
        const firstErrorField = Object.keys(errors || {})[0];
        if (!firstErrorField) return;
        const nextStep = fieldStepMap[firstErrorField];
        if (nextStep && nextStep !== step) setStep(nextStep);
    }, [errors]);

    const toggleTag = (id: number) => {
        const value = Number(id);
        setData('tags', data.tags.includes(value) ? data.tags.filter((t: number) => t !== value) : [...data.tags, value]);
    };

    const toggleDeleteImage = (id: number) => {
        const value = Number(id);
        setData(
            'image_ids_to_delete',
            data.image_ids_to_delete.includes(value)
                ? data.image_ids_to_delete.filter((x: number) => x !== value)
                : [...data.image_ids_to_delete, value],
        );
    };

    const isStepOneValid = () => {
        if (!String(data.name_ar || '').trim()) return false;
        if (!String(data.name_en || '').trim()) return false;
        const price = Number(data.selling_price);
        return Number.isFinite(price) && price > 0;
    };

    const canGoNext = () => (step === 1 ? isStepOneValid() : true);

    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (step < STEP_TOTAL) {
            setStep((prev) => Math.min(STEP_TOTAL, prev + 1));
            return;
        }
        post(`/admin/products/${product.id}`, { forceFormData: true });
    };

    return (
        <AdminLayout title={t('admin.pages.products.edit.title')}>
            <div className="mx-auto max-w-5xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.products.edit.title')} #{product.id}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.products.edit.subtitle')}</p>
                    </div>
                    <Link href={`/admin/products/${product.id}`} className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('admin.pages.products.edit.viewProduct')}</Link>
                </section>

                <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                    <div className="grid gap-2 sm:grid-cols-4">
                        {stepTitles.map((title, idx) => {
                            const itemStep = idx + 1;
                            const active = itemStep === step;
                            const done = itemStep < step;
                            return (
                                <button
                                    key={title}
                                    type="button"
                                    onClick={() => setStep(itemStep)}
                                    className={`rounded-xl border px-3 py-2 text-left text-xs ${active ? 'border-cyan-300/40 bg-cyan-400/20 text-cyan-100' : done ? 'border-emerald-300/30 bg-emerald-400/10 text-emerald-100' : 'border-white/10 bg-white/[0.03] text-slate-300 hover:bg-white/[0.06]'}`}
                                >
                                    <span className="block text-[10px] uppercase tracking-[0.12em]">{t('admin.pages.products.create.step', 'Step')} {itemStep}</span>
                                    <span className="block truncate">{title}</span>
                                </button>
                            );
                        })}
                    </div>
                </section>

                <form onSubmit={submit} className="space-y-6 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    {step === 1 && (
                        <div className="grid gap-4 md:grid-cols-2">
                            <Field label={t('admin.pages.products.create.arabicName')} error={errors.name_ar}><input value={data.name_ar} onChange={(e) => setData('name_ar', e.target.value)} className={inputClass} required /></Field>
                            <Field label={t('admin.pages.products.create.englishName')} error={errors.name_en}><input value={data.name_en} onChange={(e) => setData('name_en', e.target.value)} className={inputClass} required /></Field>
                            <Field label={t('admin.pages.products.create.arabicDescription')} error={errors.description_ar}><textarea value={data.description_ar} onChange={(e) => setData('description_ar', e.target.value)} className={inputClass} rows={3} /></Field>
                            <Field label={t('admin.pages.products.create.englishDescription')} error={errors.description_en}><textarea value={data.description_en} onChange={(e) => setData('description_en', e.target.value)} className={inputClass} rows={3} /></Field>
                            <Field label={t('admin.pages.products.create.price')} error={errors.selling_price}><input type="number" step="0.001" value={data.selling_price} onChange={(e) => setData('selling_price', e.target.value)} className={inputClass} required /></Field>
                            <Field label={t('admin.pages.products.create.maxOrderItem')} error={errors.max_order_item}><input type="number" min="1" value={data.max_order_item} onChange={(e) => setData('max_order_item', e.target.value)} className={inputClass} /></Field>
                            <Field label={t('admin.pages.products.create.minimumAlertQuantity')} error={errors.minimum_alert_quantity}><input type="number" min="0" value={data.minimum_alert_quantity} onChange={(e) => setData('minimum_alert_quantity', e.target.value)} className={inputClass} /></Field>
                            <div className="md:col-span-2">
                                <label className="flex items-center gap-2 text-sm text-slate-200">
                                    <input type="checkbox" checked={data.featured} onChange={(e) => setData('featured', e.target.checked)} />
                                    {t('admin.pages.products.create.featuredProduct')}
                                </label>
                            </div>
                        </div>
                    )}

                    {step === 2 && (
                        <>
                            <div className="grid gap-4 md:grid-cols-2">
                                <Field label={t('admin.pages.products.create.company')} error={errors.company_id}>
                                    <select value={data.company_id} onChange={(e) => setData('company_id', e.target.value)} className={inputClass}>
                                        <option value="">{t('admin.pages.products.create.selectCompany')}</option>
                                        {companies.map((c: any) => <option key={c.id} value={c.id}>{c.name_en || c.name_ar}</option>)}
                                    </select>
                                </Field>

                                <Field label={t('admin.pages.products.create.category')} error={errors.category_id}>
                                    <select value={data.category_id} onChange={(e) => setData('category_id', e.target.value)} className={inputClass}>
                                        <option value="">{t('admin.pages.products.create.selectCategory')}</option>
                                        {categories.map((c: any) => <option key={c.id} value={c.id}>{c.name_en || c.name_ar}</option>)}
                                    </select>
                                </Field>
                            </div>

                            <Field label={t('admin.pages.products.create.tags')} error={errors.tags}>
                                <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    {tags.map((tag: any) => (
                                        <label key={tag.id} className="flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-200">
                                            <input type="checkbox" checked={data.tags.includes(Number(tag.id))} onChange={() => toggleTag(tag.id)} />
                                            {tag.name_en || tag.name_ar}
                                        </label>
                                    ))}
                                </div>
                            </Field>
                        </>
                    )}

                    {step === 3 && (
                        <section className="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                            <h3 className="mb-3 text-sm font-semibold text-white">{t('admin.pages.products.edit.currentImages')}</h3>
                            {(product.images || []).length === 0 ? (
                                <p className="text-sm text-slate-400">{t('admin.pages.products.edit.noImages')}</p>
                            ) : (
                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    {product.images.map((img: any) => {
                                        const url = img.full_url || (img.image_url ? `/storage/${img.image_url}` : '');
                                        return (
                                            <div key={img.id} className="rounded-lg border border-white/10 bg-slate-900/40 p-2">
                                                <img src={url} alt="product" className="h-40 w-full rounded-lg object-cover" />
                                                <label className="mt-2 flex items-center gap-2 text-xs text-slate-200">
                                                    <input type="radio" name="primary_image_id" checked={String(data.primary_image_id) === String(img.id)} onChange={() => setData('primary_image_id', img.id)} />
                                                    {t('admin.pages.products.edit.primaryImage')}
                                                </label>
                                                <label className="mt-1 flex items-center gap-2 text-xs text-rose-200">
                                                    <input type="checkbox" checked={data.image_ids_to_delete.includes(Number(img.id))} onChange={() => toggleDeleteImage(img.id)} />
                                                    {t('admin.pages.products.edit.markForDelete')}
                                                </label>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </section>
                    )}

                    {step === 4 && (
                        <Field label={t('admin.pages.products.edit.uploadNewImages')} error={errors.images}>
                            <input type="file" multiple accept="image/*" onChange={(e) => setData('images', Array.from(e.target.files || []))} className={inputClass} />
                        </Field>
                    )}

                    <div className="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-4">
                        <div className="text-xs text-slate-400">{t('admin.pages.products.create.step', 'Step')} {step} / {STEP_TOTAL}</div>
                        <div className="flex gap-2">
                            <button
                                type="button"
                                disabled={step === 1}
                                onClick={() => setStep((prev) => Math.max(1, prev - 1))}
                                className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {t('admin.pages.products.create.previous', 'Previous')}
                            </button>
                            {step < STEP_TOTAL ? (
                                <button
                                    type="button"
                                    disabled={!canGoNext()}
                                    onClick={() => setStep((prev) => Math.min(STEP_TOTAL, prev + 1))}
                                    className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {t('admin.pages.products.create.next', 'Next')}
                                </button>
                            ) : (
                                <button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">
                                    {processing ? t('admin.pages.products.edit.updating') : t('admin.pages.products.edit.submit')}
                                </button>
                            )}
                            <Link href={`/admin/products/${product.id}`} className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">{t('common.cancel')}</Link>
                        </div>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

const inputClass = 'w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40';

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <div>
            <label className="mb-1 block text-sm text-slate-300">{label}</label>
            {children}
            {error ? <p className="mt-1 text-xs text-rose-300">{error}</p> : null}
        </div>
    );
}
