import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

const STEP_TOTAL = 3;

export default function ProductsEdit() {
    const { t } = useI18n();
    const { product, categories = [], tags = [], companies = [] } = usePage<any>().props;
    const [step, setStep] = useState(1);
    const [companyOptions] = useState<any[]>(companies);
    const [categoryOptions] = useState<any[]>(categories);
    const [tagOptions] = useState<any[]>(tags);
    const [companySearch, setCompanySearch] = useState('');
    const [categorySearch, setCategorySearch] = useState('');
    const [tagSearch, setTagSearch] = useState('');
    const [stepError, setStepError] = useState('');
    const [newImagePreviewUrls, setNewImagePreviewUrls] = useState<string[]>([]);
    const fileInputRef = useRef<HTMLInputElement | null>(null);
    const submitIntentRef = useRef(false);

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
        tags: (product.tags || []).map((tag: any) => tag.id),
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
        images: 3,
    };

    const filteredCompanies = useMemo(() => {
        const q = companySearch.trim().toLowerCase();
        if (!q) return companyOptions;
        return companyOptions.filter((c) => `${c?.name_en || ''} ${c?.name_ar || ''}`.toLowerCase().includes(q));
    }, [companyOptions, companySearch]);

    const filteredCategories = useMemo(() => {
        const q = categorySearch.trim().toLowerCase();
        if (!q) return categoryOptions;
        return categoryOptions.filter((c) => `${c?.name_en || ''} ${c?.name_ar || ''}`.toLowerCase().includes(q));
    }, [categoryOptions, categorySearch]);

    const filteredTags = useMemo(() => {
        const q = tagSearch.trim().toLowerCase();
        if (!q) return tagOptions;
        return tagOptions.filter((tag) => `${tag?.name_en || ''} ${tag?.name_ar || ''}`.toLowerCase().includes(q));
    }, [tagOptions, tagSearch]);

    const selectedTagsCount = useMemo(() => data.tags.length || 0, [data.tags]);
    const selectedCompany = useMemo(
        () => companyOptions.find((c) => Number(c.id) === Number(data.company_id)),
        [companyOptions, data.company_id],
    );
    const selectedCategory = useMemo(
        () => categoryOptions.find((c) => Number(c.id) === Number(data.category_id)),
        [categoryOptions, data.category_id],
    );

    const stepTitles = useMemo(
        () => [
            t('admin.pages.products.create.steps.basic', 'Basic Info'),
            t('admin.pages.products.create.steps.meta', 'Company, Category, Tags'),
            t('admin.pages.products.edit.steps.currentImages', 'Images'),
        ],
        [t],
    );

    useEffect(() => {
        const firstErrorField = Object.keys(errors || {})[0];
        if (!firstErrorField) return;
        const nextStep = fieldStepMap[firstErrorField];
        if (nextStep && nextStep !== step) setStep(nextStep);
    }, [errors]);

    useEffect(() => {
        return () => {
            newImagePreviewUrls.forEach((url) => URL.revokeObjectURL(url));
        };
    }, [newImagePreviewUrls]);

    const onNewImagesChange = (files: File[]) => {
        newImagePreviewUrls.forEach((url) => URL.revokeObjectURL(url));
        const nextUrls = files.map((file) => URL.createObjectURL(file));
        setNewImagePreviewUrls(nextUrls);
        setData('images', files);
    };

    const removeNewImageAt = (index: number) => {
        const nextFiles = (data.images || []).filter((_: File, idx: number) => idx !== index);
        onNewImagesChange(nextFiles);
    };

    const isStepOneValid = () => {
        if (!String(data.name_ar || '').trim()) return false;
        if (!String(data.name_en || '').trim()) return false;
        const price = Number(data.selling_price);
        return Number.isFinite(price) && price > 0;
    };

    const validateStep = (stepToValidate: number) => {
        if (stepToValidate === 1 && !isStepOneValid()) {
            return t('admin.pages.products.create.validation.step1', 'Fill required fields in Basic Info before continuing.');
        }
        return '';
    };

    const canGoNext = () => validateStep(step) === '';

    const goToStep = (targetStep: number) => {
        const normalizedTarget = Math.min(STEP_TOTAL, Math.max(1, targetStep));
        if (normalizedTarget <= step) {
            setStepError('');
            setStep(normalizedTarget);
            return;
        }

        for (let current = step; current < normalizedTarget; current += 1) {
            const validationMessage = validateStep(current);
            if (validationMessage) {
                setStepError(validationMessage);
                setStep(current);
                return;
            }
        }

        setStepError('');
        setStep(normalizedTarget);
    };

    const toggleTag = (id: number) => {
        const value = Number(id);
        setData('tags', data.tags.includes(value) ? data.tags.filter((tagId: number) => tagId !== value) : [...data.tags, value]);
    };

    const toggleDeleteImage = (id: number) => {
        const value = Number(id);
        setData(
            'image_ids_to_delete',
            data.image_ids_to_delete.includes(value)
                ? data.image_ids_to_delete.filter((imgId: number) => imgId !== value)
                : [...data.image_ids_to_delete, value],
        );
    };

    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (!submitIntentRef.current || step !== STEP_TOTAL) {
            submitIntentRef.current = false;
            return;
        }
        submitIntentRef.current = false;
        setStepError('');
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
                    <div className="grid gap-2 sm:grid-cols-3">
                        {stepTitles.map((title, idx) => {
                            const itemStep = idx + 1;
                            const active = itemStep === step;
                            const done = itemStep < step;
                            return (
                                <button
                                    key={title}
                                    type="button"
                                    onClick={() => goToStep(itemStep)}
                                    className={`rounded-xl border px-3 py-2 text-left text-xs ${active ? 'border-cyan-300/40 bg-cyan-400/20 text-cyan-100' : done ? 'border-emerald-300/30 bg-emerald-400/10 text-emerald-100' : 'border-white/10 bg-white/[0.03] text-slate-300 hover:bg-white/[0.06]'}`}
                                >
                                    <span className="block text-[10px] uppercase tracking-[0.12em]">{t('admin.pages.products.create.step', 'Step')} {itemStep}</span>
                                    <span className="block truncate">{title}</span>
                                </button>
                            );
                        })}
                    </div>
                </section>

                <form
                    onSubmit={submit}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' && step < STEP_TOTAL) e.preventDefault();
                    }}
                    className="space-y-6 rounded-2xl border border-white/10 bg-white/[0.04] p-5"
                >
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
                                    <div className="space-y-2">
                                        <input value={companySearch} onChange={(e) => setCompanySearch(e.target.value)} placeholder={t('admin.pages.products.create.searchCompany', 'Search company...')} className={inputClass} />
                                        {selectedCompany && (
                                            <div className="flex items-center justify-between rounded-lg border border-cyan-300/20 bg-cyan-400/10 px-3 py-2 text-sm text-cyan-100">
                                                <span>{selectedCompany.name_en || selectedCompany.name_ar}</span>
                                                <button type="button" onClick={() => setData('company_id', '')} className="text-xs text-cyan-200 underline">{t('admin.pages.products.create.clearSelection', 'Clear')}</button>
                                            </div>
                                        )}
                                        <div className="max-h-44 space-y-1 overflow-auto rounded-lg border border-white/10 bg-slate-900/40 p-2">
                                            {filteredCompanies.length === 0 ? <p className="px-2 py-1 text-xs text-slate-400">{t('admin.pages.products.create.noResults', 'No results')}</p> : filteredCompanies.slice(0, 30).map((company) => {
                                                const active = Number(data.company_id) === Number(company.id);
                                                return (
                                                    <button key={company.id} type="button" onClick={() => setData('company_id', String(company.id))} className={`w-full rounded-md px-2 py-1.5 text-left text-sm ${active ? 'bg-cyan-400/20 text-cyan-100' : 'text-slate-200 hover:bg-white/10'}`}>
                                                        {company.name_en || company.name_ar}
                                                        {company.name_ar && company.name_en ? ` (${company.name_ar})` : ''}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </Field>

                                <Field label={t('admin.pages.products.create.category')} error={errors.category_id}>
                                    <div className="space-y-2">
                                        <input value={categorySearch} onChange={(e) => setCategorySearch(e.target.value)} placeholder={t('admin.pages.products.create.searchCategory', 'Search category...')} className={inputClass} />
                                        {selectedCategory && (
                                            <div className="flex items-center justify-between rounded-lg border border-cyan-300/20 bg-cyan-400/10 px-3 py-2 text-sm text-cyan-100">
                                                <span>{selectedCategory.name_en || selectedCategory.name_ar}</span>
                                                <button type="button" onClick={() => setData('category_id', '')} className="text-xs text-cyan-200 underline">{t('admin.pages.products.create.clearSelection', 'Clear')}</button>
                                            </div>
                                        )}
                                        <div className="max-h-44 space-y-1 overflow-auto rounded-lg border border-white/10 bg-slate-900/40 p-2">
                                            {filteredCategories.length === 0 ? <p className="px-2 py-1 text-xs text-slate-400">{t('admin.pages.products.create.noResults', 'No results')}</p> : filteredCategories.slice(0, 30).map((category) => {
                                                const active = Number(data.category_id) === Number(category.id);
                                                return (
                                                    <button key={category.id} type="button" onClick={() => setData('category_id', String(category.id))} className={`w-full rounded-md px-2 py-1.5 text-left text-sm ${active ? 'bg-cyan-400/20 text-cyan-100' : 'text-slate-200 hover:bg-white/10'}`}>
                                                        {category.name_en || category.name_ar}
                                                        {category.name_ar && category.name_en ? ` (${category.name_ar})` : ''}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </Field>
                            </div>

                            <Field label={t('admin.pages.products.create.tags')} error={errors.tags}>
                                <div className="mb-2 flex gap-2">
                                    <input value={tagSearch} onChange={(e) => setTagSearch(e.target.value)} placeholder={t('admin.pages.products.create.searchTag', 'Search tags...')} className={inputClass} />
                                </div>
                                <div className="mb-2 text-xs text-slate-400">{t('admin.pages.products.create.selectedTags', 'Selected tags')}: {selectedTagsCount}</div>
                                <div className="grid max-h-48 gap-2 overflow-auto pr-1 sm:grid-cols-2 lg:grid-cols-3">
                                    {filteredTags.length === 0 ? <p className="col-span-full px-2 py-1 text-xs text-slate-400">{t('admin.pages.products.create.noResults', 'No results')}</p> : filteredTags.map((tag) => (
                                        <label key={tag.id} className="flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-200">
                                            <input type="checkbox" checked={data.tags.includes(Number(tag.id))} onChange={() => toggleTag(tag.id)} />
                                            {tag.name_en || tag.name_ar}
                                        </label>
                                    ))}
                                </div>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    <button type="button" onClick={() => setData('tags', [])} className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10">{t('admin.pages.products.create.clearSelectedTags', 'Clear selected')}</button>
                                </div>
                            </Field>
                        </>
                    )}

                    {step === 3 && (
                        <div className="space-y-4">
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

                            <Field label={t('admin.pages.products.edit.uploadNewImages')} error={errors.images}>
                                <div className="space-y-3">
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        multiple
                                        accept="image/*"
                                        onChange={(e) => onNewImagesChange(Array.from(e.target.files || []))}
                                        className="hidden"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => fileInputRef.current?.click()}
                                        className="w-full rounded-2xl border border-dashed border-cyan-300/35 bg-cyan-400/5 px-4 py-6 text-center transition hover:bg-cyan-400/10"
                                    >
                                        <p className="text-sm font-semibold text-cyan-100">{t('admin.pages.products.create.uploadCta', 'Upload product images')}</p>
                                        <p className="mt-1 text-xs text-slate-300">{t('admin.pages.products.create.uploadHint', 'PNG, JPG, WEBP. Pick one or multiple files.')}</p>
                                    </button>
                                    {(data.images || []).length > 0 && (
                                        <div className="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2">
                                            <p className="text-xs text-slate-300">{(data.images || []).length} {t('admin.pages.products.create.filesSelected')}</p>
                                            <button type="button" onClick={() => onNewImagesChange([])} className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2.5 py-1 text-xs text-rose-200 hover:bg-rose-500/20">
                                                {t('admin.pages.products.create.clearSelection', 'Clear')}
                                            </button>
                                        </div>
                                    )}
                                </div>

                                {newImagePreviewUrls.length > 0 && (
                                    <div className="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        {newImagePreviewUrls.map((url, idx) => (
                                            <div key={`${url}-${idx}`} className="overflow-hidden rounded-xl border border-white/10 bg-white/[0.03] p-2">
                                                <img src={url} alt={`new-preview-${idx}`} className="h-28 w-full rounded-lg object-cover" />
                                                <div className="mt-2 flex justify-end">
                                                    <button type="button" onClick={() => removeNewImageAt(idx)} className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2 py-0.5 text-[11px] text-rose-200 hover:bg-rose-500/20">
                                                        {t('common.delete')}
                                                    </button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </Field>
                        </div>
                    )}

                    <div className="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-4">
                        <div className="text-xs text-slate-400">{t('admin.pages.products.create.step', 'Step')} {step} / {STEP_TOTAL}</div>
                        <div className="flex gap-2">
                            <button
                                type="button"
                                disabled={step === 1}
                                onClick={() => goToStep(step - 1)}
                                className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {t('admin.pages.products.create.previous', 'Previous')}
                            </button>
                            {step < STEP_TOTAL ? (
                                <button
                                    type="button"
                                    disabled={!canGoNext()}
                                    onClick={() => goToStep(step + 1)}
                                    className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {t('admin.pages.products.create.next', 'Next')}
                                </button>
                            ) : (
                                <button
                                    type="submit"
                                    onClick={() => {
                                        submitIntentRef.current = true;
                                    }}
                                    disabled={processing}
                                    className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70"
                                >
                                    {processing ? t('admin.pages.products.edit.updating') : t('admin.pages.products.edit.submit')}
                                </button>
                            )}
                            <Link href={`/admin/products/${product.id}`} className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">{t('common.cancel')}</Link>
                        </div>
                    </div>
                    {stepError ? <p className="text-sm text-rose-300">{stepError}</p> : null}
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
