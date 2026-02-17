import React, { useMemo, useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

export default function ProductsCreate() {
    const { t } = useI18n();
    const { categories = [], tags = [], companies = [], maxOrderItems = null, generalMinimumAlertQuantity = null } = usePage<any>().props;
    const [companyOptions, setCompanyOptions] = useState<any[]>(companies);
    const [categoryOptions, setCategoryOptions] = useState<any[]>(categories);
    const [tagOptions, setTagOptions] = useState<any[]>(tags);

    const [companySearch, setCompanySearch] = useState('');
    const [categorySearch, setCategorySearch] = useState('');
    const [tagSearch, setTagSearch] = useState('');

    const [newCompanyAr, setNewCompanyAr] = useState('');
    const [newCompanyEn, setNewCompanyEn] = useState('');
    const [newCategoryAr, setNewCategoryAr] = useState('');
    const [newCategoryEn, setNewCategoryEn] = useState('');
    const [newTagAr, setNewTagAr] = useState('');
    const [newTagEn, setNewTagEn] = useState('');

    const [creatingCompany, setCreatingCompany] = useState(false);
    const [creatingCategory, setCreatingCategory] = useState(false);
    const [creatingTag, setCreatingTag] = useState(false);
    const [metaError, setMetaError] = useState('');

    const { data, setData, post, processing, errors } = useForm({
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
        images: [],
    });

    const submit = (e) => {
        e.preventDefault();
        post('/admin/products', { forceFormData: true });
    };

    const toggleTag = (id) => {
        const value = Number(id);
        setData('tags', data.tags.includes(value) ? data.tags.filter((t) => t !== value) : [...data.tags, value]);
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

    const imagesPreviewCount = useMemo(() => (data.images?.length ? data.images.length : 0), [data.images]);
    const selectedTagsCount = useMemo(() => data.tags.length || 0, [data.tags]);

    const csrfToken = () =>
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const createMeta = async (type: 'company' | 'category' | 'tag') => {
        setMetaError('');

        const payloadByType: Record<'company' | 'category' | 'tag', { endpoint: string; body: { name_ar: string; name_en: string; featured?: boolean }; key: 'company' | 'category' | 'tag' }> = {
            company: {
                endpoint: '/admin/companies',
                body: { name_ar: newCompanyAr.trim(), name_en: newCompanyEn.trim() },
                key: 'company',
            },
            category: {
                endpoint: '/admin/categories',
                body: { name_ar: newCategoryAr.trim(), name_en: newCategoryEn.trim(), featured: false },
                key: 'category',
            },
            tag: {
                endpoint: '/admin/tags',
                body: { name_ar: newTagAr.trim(), name_en: newTagEn.trim() },
                key: 'tag',
            },
        };

        const current = payloadByType[type];
        if (!current.body.name_ar || !current.body.name_en) {
            setMetaError(t('admin.pages.products.create.requireBothNames', 'Arabic and English names are required.'));
            return;
        }

        if (type === 'company') setCreatingCompany(true);
        if (type === 'category') setCreatingCategory(true);
        if (type === 'tag') setCreatingTag(true);

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
                setNewCompanyAr('');
                setNewCompanyEn('');
            }

            if (type === 'category') {
                setCategoryOptions((prev) => [created, ...prev]);
                setData('category_id', String(created.id));
                setNewCategoryAr('');
                setNewCategoryEn('');
            }

            if (type === 'tag') {
                setTagOptions((prev) => [created, ...prev]);
                const id = Number(created.id);
                if (!data.tags.includes(id)) setData('tags', [...data.tags, id]);
                setNewTagAr('');
                setNewTagEn('');
            }
        } catch (err: any) {
            setMetaError(err?.message || t('admin.pages.products.create.createFailed', 'Failed to create item.'));
        } finally {
            setCreatingCompany(false);
            setCreatingCategory(false);
            setCreatingTag(false);
        }
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

                <form onSubmit={submit} className="space-y-6 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label={t('admin.pages.products.create.arabicName')} error={errors.name_ar}><input value={data.name_ar} onChange={(e) => setData('name_ar', e.target.value)} className={inputClass} required /></Field>
                        <Field label={t('admin.pages.products.create.englishName')} error={errors.name_en}><input value={data.name_en} onChange={(e) => setData('name_en', e.target.value)} className={inputClass} required /></Field>
                        <Field label={t('admin.pages.products.create.arabicDescription')} error={errors.description_ar}><textarea value={data.description_ar} onChange={(e) => setData('description_ar', e.target.value)} className={inputClass} rows={3} /></Field>
                        <Field label={t('admin.pages.products.create.englishDescription')} error={errors.description_en}><textarea value={data.description_en} onChange={(e) => setData('description_en', e.target.value)} className={inputClass} rows={3} /></Field>
                        <Field label={t('admin.pages.products.create.price')} error={errors.selling_price}><input type="number" step="0.001" value={data.selling_price} onChange={(e) => setData('selling_price', e.target.value)} className={inputClass} required /></Field>
                        <Field label={t('admin.pages.products.create.maxOrderItem')} error={errors.max_order_item}><input type="number" min="1" value={data.max_order_item} onChange={(e) => setData('max_order_item', e.target.value)} className={inputClass} /></Field>
                        <Field label={t('admin.pages.products.create.minimumAlertQuantity')} error={errors.minimum_alert_quantity}><input type="number" min="0" value={data.minimum_alert_quantity} onChange={(e) => setData('minimum_alert_quantity', e.target.value)} className={inputClass} /></Field>

                        <Field label={t('admin.pages.products.create.company')} error={errors.company_id}>
                            <input
                                value={companySearch}
                                onChange={(e) => setCompanySearch(e.target.value)}
                                placeholder={t('admin.pages.products.create.searchCompany', 'Search company...')}
                                className={`${inputClass} mb-2`}
                            />
                            <select value={data.company_id} onChange={(e) => setData('company_id', e.target.value)} className={inputClass}>
                                <option value="">{t('admin.pages.products.create.selectCompany')}</option>
                                {filteredCompanies.map((c) => <option key={c.id} value={c.id}>{c.name_en || c.name_ar}</option>)}
                            </select>
                            <div className="mt-2 grid gap-2 sm:grid-cols-2">
                                <input value={newCompanyAr} onChange={(e) => setNewCompanyAr(e.target.value)} placeholder={t('admin.pages.products.create.newArabicName', 'New Arabic name')} className={inputClass} />
                                <input value={newCompanyEn} onChange={(e) => setNewCompanyEn(e.target.value)} placeholder={t('admin.pages.products.create.newEnglishName', 'New English name')} className={inputClass} />
                            </div>
                            <button
                                type="button"
                                disabled={creatingCompany}
                                onClick={() => createMeta('company')}
                                className="mt-2 rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs text-cyan-200 hover:bg-cyan-400/20 disabled:opacity-70"
                            >
                                {creatingCompany ? t('admin.pages.products.create.creating', 'Creating...') : t('admin.pages.products.create.addCompanyInline', 'Create company')}
                            </button>
                        </Field>

                        <Field label={t('admin.pages.products.create.category')} error={errors.category_id}>
                            <input
                                value={categorySearch}
                                onChange={(e) => setCategorySearch(e.target.value)}
                                placeholder={t('admin.pages.products.create.searchCategory', 'Search category...')}
                                className={`${inputClass} mb-2`}
                            />
                            <select value={data.category_id} onChange={(e) => setData('category_id', e.target.value)} className={inputClass}>
                                <option value="">{t('admin.pages.products.create.selectCategory')}</option>
                                {filteredCategories.map((c) => <option key={c.id} value={c.id}>{c.name_en || c.name_ar}</option>)}
                            </select>
                            <div className="mt-2 grid gap-2 sm:grid-cols-2">
                                <input value={newCategoryAr} onChange={(e) => setNewCategoryAr(e.target.value)} placeholder={t('admin.pages.products.create.newArabicName', 'New Arabic name')} className={inputClass} />
                                <input value={newCategoryEn} onChange={(e) => setNewCategoryEn(e.target.value)} placeholder={t('admin.pages.products.create.newEnglishName', 'New English name')} className={inputClass} />
                            </div>
                            <button
                                type="button"
                                disabled={creatingCategory}
                                onClick={() => createMeta('category')}
                                className="mt-2 rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs text-cyan-200 hover:bg-cyan-400/20 disabled:opacity-70"
                            >
                                {creatingCategory ? t('admin.pages.products.create.creating', 'Creating...') : t('admin.pages.products.create.addCategoryInline', 'Create category')}
                            </button>
                        </Field>
                    </div>

                    <Field label={t('admin.pages.products.create.tags')} error={errors.tags}>
                        <input
                            value={tagSearch}
                            onChange={(e) => setTagSearch(e.target.value)}
                            placeholder={t('admin.pages.products.create.searchTag', 'Search tags...')}
                            className={`${inputClass} mb-2`}
                        />
                        <div className="mb-2 text-xs text-slate-400">
                            {t('admin.pages.products.create.selectedTags', 'Selected tags')}: {selectedTagsCount}
                        </div>
                        <div className="grid max-h-48 gap-2 overflow-auto pr-1 sm:grid-cols-2 lg:grid-cols-3">
                            {filteredTags.map((tag) => (
                                <label key={tag.id} className="flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-200">
                                    <input type="checkbox" checked={data.tags.includes(Number(tag.id))} onChange={() => toggleTag(tag.id)} />
                                    {tag.name_en || tag.name_ar}
                                </label>
                            ))}
                        </div>
                        <div className="mt-2 grid gap-2 sm:grid-cols-2">
                            <input value={newTagAr} onChange={(e) => setNewTagAr(e.target.value)} placeholder={t('admin.pages.products.create.newArabicName', 'New Arabic name')} className={inputClass} />
                            <input value={newTagEn} onChange={(e) => setNewTagEn(e.target.value)} placeholder={t('admin.pages.products.create.newEnglishName', 'New English name')} className={inputClass} />
                        </div>
                        <div className="mt-2 flex flex-wrap gap-2">
                            <button
                                type="button"
                                disabled={creatingTag}
                                onClick={() => createMeta('tag')}
                                className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs text-cyan-200 hover:bg-cyan-400/20 disabled:opacity-70"
                            >
                                {creatingTag ? t('admin.pages.products.create.creating', 'Creating...') : t('admin.pages.products.create.addTagInline', 'Create tag')}
                            </button>
                            <button
                                type="button"
                                onClick={() => setData('tags', [])}
                                className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
                            >
                                {t('admin.pages.products.create.clearSelectedTags', 'Clear selected')}
                            </button>
                        </div>
                    </Field>
                    {metaError ? <p className="text-sm text-rose-300">{metaError}</p> : null}

                    <Field label={t('admin.pages.products.create.productImages')} error={errors.images}>
                        <input type="file" multiple accept="image/*" onChange={(e) => setData('images', Array.from(e.target.files || []))} className={inputClass} />
                        {imagesPreviewCount > 0 && <p className="mt-1 text-xs text-slate-400">{imagesPreviewCount} {t('admin.pages.products.create.filesSelected')}</p>}
                    </Field>

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

                    <label className="flex items-center gap-2 text-sm text-slate-200">
                        <input type="checkbox" checked={data.featured} onChange={(e) => setData('featured', e.target.checked)} />
                        {t('admin.pages.products.create.featuredProduct')}
                    </label>

                    <div className="flex gap-3">
                        <button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">
                            {processing ? t('admin.pages.products.create.saving') : t('admin.pages.products.create.submit')}
                        </button>
                        <Link href="/admin/products" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">{t('common.cancel')}</Link>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

const inputClass = 'w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40';

function Field({ label, error, children }) {
    return (
        <div>
            <label className="mb-1 block text-sm text-slate-300">{label}</label>
            {children}
            {error ? <p className="mt-1 text-xs text-rose-300">{error}</p> : null}
        </div>
    );
}


