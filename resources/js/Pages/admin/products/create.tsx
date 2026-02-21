import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type MetaType = 'company' | 'category' | 'tag';
type MetaOption = { id: number; name_ar?: string; name_en?: string };
type CreateProductFormData = {
    name_ar: string;
    name_en: string;
    description_ar: string;
    description_en: string;
    selling_price: string;
    max_order_item: string | number;
    minimum_alert_quantity: string | number;
    featured: boolean;
    category_id: string;
    company_id: string;
    tags: number[];
    enable_initial_stock: boolean;
    initial_stock_quantity: string;
    initial_batch_number: string;
    initial_expiry_date: string;
    initial_cost_price: string;
    primary_image_index: number | '';
    images: File[];
};

const STEP_TOTAL = 4;

export default function ProductsCreate() {
    const { t } = useI18n();
    const { categories = [], tags = [], companies = [], maxOrderItems = null, generalMinimumAlertQuantity = null } = usePage<{
        categories?: MetaOption[];
        tags?: MetaOption[];
        companies?: MetaOption[];
        maxOrderItems?: number | null;
        generalMinimumAlertQuantity?: number | null;
    }>().props;
    const [step, setStep] = useState(1);
    const [companyOptions, setCompanyOptions] = useState<MetaOption[]>(companies);
    const [categoryOptions, setCategoryOptions] = useState<MetaOption[]>(categories);
    const [tagOptions, setTagOptions] = useState<MetaOption[]>(tags);
    const [companySearch, setCompanySearch] = useState('');
    const [categorySearch, setCategorySearch] = useState('');
    const [tagSearch, setTagSearch] = useState('');
    const [createModalType, setCreateModalType] = useState<MetaType | null>(null);
    const [newNameAr, setNewNameAr] = useState('');
    const [newNameEn, setNewNameEn] = useState('');
    const [creatingType, setCreatingType] = useState<MetaType | null>(null);
    const [metaError, setMetaError] = useState('');
    const [imagePreviewUrls, setImagePreviewUrls] = useState<string[]>([]);
    const [stepError, setStepError] = useState('');
    const fileInputRef = useRef<HTMLInputElement | null>(null);
    const submitIntentRef = useRef(false);

    const { data, setData, post, processing, errors } = useForm<CreateProductFormData>({
        name_ar: '',
        name_en: '',
        description_ar: '',
        description_en: '',
        selling_price: '',
        max_order_item: maxOrderItems ?? '',
        minimum_alert_quantity: generalMinimumAlertQuantity ?? '',
        featured: false,
        category_id: '',
        company_id: '',
        tags: [],
        enable_initial_stock: false,
        initial_stock_quantity: '',
        initial_batch_number: '',
        initial_expiry_date: '',
        initial_cost_price: '',
        primary_image_index: '',
        images: [],
    });

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

    const imagesPreviewCount = useMemo(() => (data.images?.length ? data.images.length : 0), [data.images]);
    const selectedTagsCount = useMemo(() => data.tags.length || 0, [data.tags]);

    const selectedCompany = useMemo(
        () => companyOptions.find((c) => Number(c.id) === Number(data.company_id)),
        [companyOptions, data.company_id],
    );
    const selectedCategory = useMemo(
        () => categoryOptions.find((c) => Number(c.id) === Number(data.category_id)),
        [categoryOptions, data.category_id],
    );

    const stepTitles = [
        t('admin.pages.products.create.steps.basic', 'Basic Info'),
        t('admin.pages.products.create.steps.meta', 'Company, Category, Tags'),
        t('admin.pages.products.create.steps.images', 'Images'),
        t('admin.pages.products.create.steps.inventory', 'Inventory & Submit'),
    ];

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
        images: 3,
        primary_image_index: 3,
        initial_stock_quantity: 4,
        initial_batch_number: 4,
        initial_expiry_date: 4,
        initial_cost_price: 4,
        enable_initial_stock: 4,
    };

    useEffect(() => {
        const firstErrorField = Object.keys(errors || {})[0];
        if (!firstErrorField) return;
        const nextStep = fieldStepMap[firstErrorField];
        if (nextStep && nextStep !== step) setStep(nextStep);
    }, [errors]);

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    useEffect(() => {
        return () => {
            imagePreviewUrls.forEach((url) => URL.revokeObjectURL(url));
        };
    }, [imagePreviewUrls]);

    const onImagesChange = (files: File[]) => {
        setStepError('');
        imagePreviewUrls.forEach((url) => URL.revokeObjectURL(url));
        const nextUrls = files.map((file) => URL.createObjectURL(file));
        setImagePreviewUrls(nextUrls);
        setData('images', files);

        if (files.length === 0) {
            setData('primary_image_index', '');
            return;
        }

        const current = Number(data.primary_image_index);
        if (Number.isInteger(current) && current >= 0 && current < files.length) {
            setData('primary_image_index', current);
            return;
        }
        setData('primary_image_index', 0);
    };

    const removeImageAt = (index: number) => {
        const nextFiles = (data.images || []).filter((_: File, idx: number) => idx !== index);
        onImagesChange(nextFiles);
    };

    const isStepOneValid = () => {
        if (!String(data.name_ar || '').trim()) return false;
        if (!String(data.name_en || '').trim()) return false;
        const price = Number(data.selling_price);
        return Number.isFinite(price) && price > 0;
    };

    const isInitialStockStepValid = () => {
        if (!data.enable_initial_stock) return true;
        const qty = Number(data.initial_stock_quantity);
        const cost = Number(data.initial_cost_price);
        if (!Number.isFinite(qty) || qty < 1) return false;
        if (!String(data.initial_batch_number || '').trim()) return false;
        if (!Number.isFinite(cost) || cost <= 0) return false;
        return true;
    };

    const validateStep = (stepToValidate: number) => {
        if (stepToValidate === 1 && !isStepOneValid()) {
            return t('admin.pages.products.create.validation.step1', 'Fill required fields in Basic Info before continuing.');
        }
        if (stepToValidate === 4 && !isInitialStockStepValid()) {
            return t('admin.pages.products.create.validation.step4', 'Complete required initial stock fields before submitting.');
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

    const openCreateModal = (type: MetaType) => {
        setMetaError('');
        setNewNameAr('');
        setNewNameEn('');
        setCreateModalType(type);
    };

    const closeCreateModal = () => {
        setCreateModalType(null);
        setMetaError('');
    };

    const createMeta = async (type: MetaType, nameAr: string, nameEn: string) => {
        setMetaError('');

        const payloadByType: Record<MetaType, { endpoint: string; body: { name_ar: string; name_en: string; featured?: boolean }; key: MetaType }> = {
            company: {
                endpoint: '/admin/companies',
                body: { name_ar: nameAr.trim(), name_en: nameEn.trim() },
                key: 'company',
            },
            category: {
                endpoint: '/admin/categories',
                body: { name_ar: nameAr.trim(), name_en: nameEn.trim(), featured: false },
                key: 'category',
            },
            tag: {
                endpoint: '/admin/tags',
                body: { name_ar: nameAr.trim(), name_en: nameEn.trim() },
                key: 'tag',
            },
        };

        const current = payloadByType[type];
        if (!current.body.name_ar || !current.body.name_en) {
            setMetaError(t('admin.pages.products.create.requireBothNames', 'Arabic and English names are required.'));
            return;
        }

        setCreatingType(type);

        try {
            const response = await fetch(current.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(current.body),
            });

            const json = await response.json();
            if (!response.ok) {
                const firstError = json?.message || Object.values(json?.errors || {}).flat()?.[0];
                throw new Error((firstError as string) || 'Unable to create item.');
            }

            const created = json?.[current.key];
            if (!created?.id) throw new Error('Invalid server response.');

            if (type === 'company') {
                setCompanyOptions((prev) => [created, ...prev]);
                setData('company_id', String(created.id));
                setCompanySearch(`${created.name_en || ''} ${created.name_ar || ''}`.trim());
            }

            if (type === 'category') {
                setCategoryOptions((prev) => [created, ...prev]);
                setData('category_id', String(created.id));
                setCategorySearch(`${created.name_en || ''} ${created.name_ar || ''}`.trim());
            }

            if (type === 'tag') {
                setTagOptions((prev) => [created, ...prev]);
                const id = Number(created.id);
                if (!data.tags.includes(id)) setData('tags', [...data.tags, id]);
            }
            closeCreateModal();
        } catch (err: any) {
            setMetaError(err?.message || t('admin.pages.products.create.createFailed', 'Failed to create item.'));
        } finally {
            setCreatingType(null);
        }
    };

    const toggleTag = (id: number) => {
        const value = Number(id);
        setData('tags', data.tags.includes(value) ? data.tags.filter((t: number) => t !== value) : [...data.tags, value]);
    };

    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (!submitIntentRef.current || step !== STEP_TOTAL) {
            submitIntentRef.current = false;
            return;
        }

        const finalValidationMessage = validateStep(4);
        if (finalValidationMessage) {
            submitIntentRef.current = false;
            setStepError(finalValidationMessage);
            return;
        }

        setStepError('');
        submitIntentRef.current = false;
        post('/admin/products', { forceFormData: true });
    };

    return (
        <AdminLayout title={t('admin.pages.products.create.title')}>
            <div className="mx-auto max-w-5xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.products.create.title')}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.products.create.subtitle')}</p>
                    </div>
                    <Link href="/admin/products" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('common.back')}</Link>
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
                        if (e.key === 'Enter' && step < STEP_TOTAL) {
                            e.preventDefault();
                        }
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
                                        <div className="flex gap-2">
                                            <input value={companySearch} onChange={(e) => setCompanySearch(e.target.value)} placeholder={t('admin.pages.products.create.searchCompany', 'Search company...')} className={inputClass} />
                                            <button type="button" onClick={() => openCreateModal('company')} className="shrink-0 rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-2 text-xs text-cyan-200 hover:bg-cyan-400/20">
                                                {t('admin.pages.products.create.addCompanyInline', 'Create company')}
                                            </button>
                                        </div>
                                        {selectedCompany && (
                                            <div className="flex items-center justify-between rounded-lg border border-cyan-300/20 bg-cyan-400/10 px-3 py-2 text-sm text-cyan-100">
                                                <span>{selectedCompany.name_en || selectedCompany.name_ar}</span>
                                                <button type="button" onClick={() => setData('company_id', '')} className="text-xs text-cyan-200 underline">{t('admin.pages.products.create.clearSelection', 'Clear')}</button>
                                            </div>
                                        )}
                                        <div className="max-h-44 space-y-1 overflow-auto rounded-lg border border-white/10 bg-slate-900/40 p-2">
                                            {filteredCompanies.length === 0 ? <p className="px-2 py-1 text-xs text-slate-400">{t('admin.pages.products.create.noResults', 'No results')}</p> : filteredCompanies.slice(0, 30).map((c: MetaOption) => {
                                                const active = Number(data.company_id) === Number(c.id);
                                                return (
                                                    <button key={c.id} type="button" onClick={() => setData('company_id', String(c.id))} className={`w-full rounded-md px-2 py-1.5 text-left text-sm ${active ? 'bg-cyan-400/20 text-cyan-100' : 'text-slate-200 hover:bg-white/10'}`}>
                                                        {c.name_en || c.name_ar}
                                                        {c.name_ar && c.name_en ? ` (${c.name_ar})` : ''}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </Field>

                                <Field label={t('admin.pages.products.create.category')} error={errors.category_id}>
                                    <div className="space-y-2">
                                        <div className="flex gap-2">
                                            <input value={categorySearch} onChange={(e) => setCategorySearch(e.target.value)} placeholder={t('admin.pages.products.create.searchCategory', 'Search category...')} className={inputClass} />
                                            <button type="button" onClick={() => openCreateModal('category')} className="shrink-0 rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-2 text-xs text-cyan-200 hover:bg-cyan-400/20">
                                                {t('admin.pages.products.create.addCategoryInline', 'Create category')}
                                            </button>
                                        </div>
                                        {selectedCategory && (
                                            <div className="flex items-center justify-between rounded-lg border border-cyan-300/20 bg-cyan-400/10 px-3 py-2 text-sm text-cyan-100">
                                                <span>{selectedCategory.name_en || selectedCategory.name_ar}</span>
                                                <button type="button" onClick={() => setData('category_id', '')} className="text-xs text-cyan-200 underline">{t('admin.pages.products.create.clearSelection', 'Clear')}</button>
                                            </div>
                                        )}
                                        <div className="max-h-44 space-y-1 overflow-auto rounded-lg border border-white/10 bg-slate-900/40 p-2">
                                            {filteredCategories.length === 0 ? <p className="px-2 py-1 text-xs text-slate-400">{t('admin.pages.products.create.noResults', 'No results')}</p> : filteredCategories.slice(0, 30).map((c: MetaOption) => {
                                                const active = Number(data.category_id) === Number(c.id);
                                                return (
                                                    <button key={c.id} type="button" onClick={() => setData('category_id', String(c.id))} className={`w-full rounded-md px-2 py-1.5 text-left text-sm ${active ? 'bg-cyan-400/20 text-cyan-100' : 'text-slate-200 hover:bg-white/10'}`}>
                                                        {c.name_en || c.name_ar}
                                                        {c.name_ar && c.name_en ? ` (${c.name_ar})` : ''}
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
                                    <button type="button" onClick={() => openCreateModal('tag')} className="shrink-0 rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-2 text-xs text-cyan-200 hover:bg-cyan-400/20">
                                        {t('admin.pages.products.create.addTagInline', 'Create tag')}
                                    </button>
                                </div>
                                <div className="mb-2 text-xs text-slate-400">{t('admin.pages.products.create.selectedTags', 'Selected tags')}: {selectedTagsCount}</div>
                                <div className="grid max-h-48 gap-2 overflow-auto pr-1 sm:grid-cols-2 lg:grid-cols-3">
                                    {filteredTags.length === 0 ? <p className="col-span-full px-2 py-1 text-xs text-slate-400">{t('admin.pages.products.create.noResults', 'No results')}</p> : filteredTags.map((tag: MetaOption) => (
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
                        <Field label={t('admin.pages.products.create.productImages')} error={errors.images}>
                            <div className="space-y-3">
                                <input
                                    ref={fileInputRef}
                                    type="file"
                                    multiple
                                    accept="image/*"
                                    onChange={(e) => onImagesChange(Array.from(e.target.files || []))}
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
                                {imagesPreviewCount > 0 && (
                                    <div className="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2">
                                        <p className="text-xs text-slate-300">{imagesPreviewCount} {t('admin.pages.products.create.filesSelected')}</p>
                                        <button
                                            type="button"
                                            onClick={() => onImagesChange([])}
                                            className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2.5 py-1 text-xs text-rose-200 hover:bg-rose-500/20"
                                        >
                                            {t('admin.pages.products.create.clearSelection', 'Clear')}
                                        </button>
                                    </div>
                                )}
                            </div>
                            {imagesPreviewCount > 0 && (
                                <div className="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    {imagePreviewUrls.map((url, idx) => (
                                        <label key={`${url}-${idx}`} className="overflow-hidden rounded-xl border border-white/10 bg-white/[0.03] p-2">
                                            <img src={url} alt={`preview-${idx}`} className="h-28 w-full rounded-lg object-cover" />
                                            <div className="mt-2 flex items-center justify-between gap-2 text-xs text-slate-200">
                                                <span className="flex items-center gap-2">
                                                    <input
                                                        type="radio"
                                                        name="primary_image_index"
                                                        checked={Number(data.primary_image_index) === idx}
                                                        onChange={() => setData('primary_image_index', idx)}
                                                    />
                                                    {t('admin.pages.products.create.primaryImage', 'Primary image')}
                                                </span>
                                                <button
                                                    type="button"
                                                    onClick={() => removeImageAt(idx)}
                                                    className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2 py-0.5 text-[11px] text-rose-200 hover:bg-rose-500/20"
                                                >
                                                    {t('common.delete')}
                                                </button>
                                            </div>
                                        </label>
                                    ))}
                                </div>
                            )}
                            {errors.primary_image_index ? <p className="mt-1 text-xs text-rose-300">{errors.primary_image_index}</p> : null}
                        </Field>
                    )}

                    {step === 4 && (
                        <div className="space-y-4">
                            <div className="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                                <label className="flex items-center gap-2 text-sm text-slate-100">
                                    <input
                                        type="checkbox"
                                        checked={data.enable_initial_stock}
                                        onChange={(e) => {
                                            const checked = e.target.checked;
                                            setData('enable_initial_stock', checked);
                                            if (!checked) {
                                                setData('initial_stock_quantity', '');
                                                setData('initial_batch_number', '');
                                                setData('initial_expiry_date', '');
                                                setData('initial_cost_price', '');
                                            }
                                        }}
                                    />
                                    {t('admin.pages.products.create.enableInitialStockBatch')}
                                </label>

                                {data.enable_initial_stock && (
                                    <div className="mt-3 grid gap-3 md:grid-cols-2">
                                        <Field label={t('admin.pages.products.create.initialQuantity')} error={errors.initial_stock_quantity}><input type="number" min="1" value={data.initial_stock_quantity} onChange={(e) => setData('initial_stock_quantity', e.target.value)} className={inputClass} required /></Field>
                                        <Field label={t('admin.pages.products.create.batchNumber')} error={errors.initial_batch_number}><input value={data.initial_batch_number} onChange={(e) => setData('initial_batch_number', e.target.value)} className={inputClass} required /></Field>
                                        <Field label={t('admin.pages.products.create.expiryDate')} error={errors.initial_expiry_date}><input type="date" value={data.initial_expiry_date} onChange={(e) => setData('initial_expiry_date', e.target.value)} className={inputClass} /></Field>
                                        <Field label={t('admin.pages.products.create.costPrice')} error={errors.initial_cost_price}><input type="number" step="0.001" min="0" value={data.initial_cost_price} onChange={(e) => setData('initial_cost_price', e.target.value)} className={inputClass} required /></Field>
                                    </div>
                                )}
                            </div>
                            {metaError ? <p className="text-sm text-rose-300">{metaError}</p> : null}
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
                                    {processing ? t('admin.pages.products.create.saving') : t('admin.pages.products.create.submit')}
                                </button>
                            )}
                            <Link href="/admin/products" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">{t('common.cancel')}</Link>
                        </div>
                    </div>
                    {stepError ? <p className="text-sm text-rose-300">{stepError}</p> : null}
                </form>
            </div>

            {createModalType && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4">
                    <div className="w-full max-w-md rounded-2xl border border-white/10 bg-slate-900 p-5">
                        <h3 className="text-lg font-semibold text-white">
                            {createModalType === 'company'
                                ? t('admin.pages.products.create.addCompanyInline', 'Create company')
                                : createModalType === 'category'
                                    ? t('admin.pages.products.create.addCategoryInline', 'Create category')
                                    : t('admin.pages.products.create.addTagInline', 'Create tag')}
                        </h3>
                        <div className="mt-4 space-y-3">
                            <input value={newNameAr} onChange={(e) => setNewNameAr(e.target.value)} placeholder={t('admin.pages.products.create.newArabicName', 'New Arabic name')} className={inputClass} />
                            <input value={newNameEn} onChange={(e) => setNewNameEn(e.target.value)} placeholder={t('admin.pages.products.create.newEnglishName', 'New English name')} className={inputClass} />
                            {metaError ? <p className="text-sm text-rose-300">{metaError}</p> : null}
                        </div>
                        <div className="mt-4 flex justify-end gap-2">
                            <button type="button" onClick={closeCreateModal} className="rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10">{t('common.cancel')}</button>
                            <button
                                type="button"
                                disabled={creatingType === createModalType}
                                onClick={() => createMeta(createModalType, newNameAr, newNameEn)}
                                className="rounded-lg bg-cyan-400 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70"
                            >
                                {creatingType === createModalType ? t('admin.pages.products.create.creating', 'Creating...') : t('common.create')}
                            </button>
                        </div>
                    </div>
                </div>
            )}
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
