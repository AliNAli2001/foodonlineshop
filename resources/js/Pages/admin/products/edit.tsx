import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function ProductsEdit() {
    const { product, categories = [], tags = [], companies = [] } = usePage().props;

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
        tags: (product.tags || []).map((t) => t.id),
        images: [],
        image_ids_to_delete: [],
        primary_image_id: (product.images || []).find((img) => img.is_primary)?.id || '',
    });

    const toggleTag = (id) => {
        const value = Number(id);
        setData('tags', data.tags.includes(value) ? data.tags.filter((t) => t !== value) : [...data.tags, value]);
    };

    const toggleDeleteImage = (id) => {
        const value = Number(id);
        setData(
            'image_ids_to_delete',
            data.image_ids_to_delete.includes(value)
                ? data.image_ids_to_delete.filter((x) => x !== value)
                : [...data.image_ids_to_delete, value],
        );
    };

    const submit = (e) => {
        e.preventDefault();
        post(`/admin/products/${product.id}`, { forceFormData: true });
    };

    return (
        <AdminLayout title="Edit Product">
            <div className="mx-auto max-w-5xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Edit Product #{product.id}</h1>
                        <p className="text-sm text-slate-300">Update details, tags, and image management settings.</p>
                    </div>
                    <Link href={`/admin/products/${product.id}`} className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">View</Link>
                </section>

                <form onSubmit={submit} className="space-y-6 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label="Arabic Name" error={errors.name_ar}><input value={data.name_ar} onChange={(e) => setData('name_ar', e.target.value)} className={inputClass} required /></Field>
                        <Field label="English Name" error={errors.name_en}><input value={data.name_en} onChange={(e) => setData('name_en', e.target.value)} className={inputClass} required /></Field>
                        <Field label="Arabic Description" error={errors.description_ar}><textarea value={data.description_ar} onChange={(e) => setData('description_ar', e.target.value)} className={inputClass} rows={3} /></Field>
                        <Field label="English Description" error={errors.description_en}><textarea value={data.description_en} onChange={(e) => setData('description_en', e.target.value)} className={inputClass} rows={3} /></Field>
                        <Field label="Price" error={errors.selling_price}><input type="number" step="0.001" value={data.selling_price} onChange={(e) => setData('selling_price', e.target.value)} className={inputClass} required /></Field>
                        <Field label="Max Order Item" error={errors.max_order_item}><input type="number" min="1" value={data.max_order_item} onChange={(e) => setData('max_order_item', e.target.value)} className={inputClass} /></Field>
                        <Field label="Minimum Alert Quantity" error={errors.minimum_alert_quantity}><input type="number" min="0" value={data.minimum_alert_quantity} onChange={(e) => setData('minimum_alert_quantity', e.target.value)} className={inputClass} /></Field>

                        <Field label="Company" error={errors.company_id}>
                            <select value={data.company_id} onChange={(e) => setData('company_id', e.target.value)} className={inputClass}>
                                <option value="">Select company</option>
                                {companies.map((c) => <option key={c.id} value={c.id}>{c.name_en || c.name_ar}</option>)}
                            </select>
                        </Field>

                        <Field label="Category" error={errors.category_id}>
                            <select value={data.category_id} onChange={(e) => setData('category_id', e.target.value)} className={inputClass}>
                                <option value="">Select category</option>
                                {categories.map((c) => <option key={c.id} value={c.id}>{c.name_en || c.name_ar}</option>)}
                            </select>
                        </Field>
                    </div>

                    <Field label="Tags" error={errors.tags}>
                        <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            {tags.map((tag) => (
                                <label key={tag.id} className="flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-slate-200">
                                    <input type="checkbox" checked={data.tags.includes(Number(tag.id))} onChange={() => toggleTag(tag.id)} />
                                    {tag.name_en || tag.name_ar}
                                </label>
                            ))}
                        </div>
                    </Field>

                    <section className="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                        <h3 className="mb-3 text-sm font-semibold text-white">Current Images</h3>
                        {(product.images || []).length === 0 ? (
                            <p className="text-sm text-slate-400">No images.</p>
                        ) : (
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                {product.images.map((img) => {
                                    const url = img.full_url || (img.image_url ? `/storage/${img.image_url}` : '');
                                    return (
                                        <div key={img.id} className="rounded-lg border border-white/10 bg-slate-900/40 p-2">
                                            <img src={url} alt="product" className="h-40 w-full rounded-lg object-cover" />
                                            <label className="mt-2 flex items-center gap-2 text-xs text-slate-200">
                                                <input type="radio" name="primary_image_id" checked={String(data.primary_image_id) === String(img.id)} onChange={() => setData('primary_image_id', img.id)} />
                                                Primary image
                                            </label>
                                            <label className="mt-1 flex items-center gap-2 text-xs text-rose-200">
                                                <input type="checkbox" checked={data.image_ids_to_delete.includes(Number(img.id))} onChange={() => toggleDeleteImage(img.id)} />
                                                Mark for delete
                                            </label>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </section>

                    <Field label="Upload New Images" error={errors.images}>
                        <input type="file" multiple accept="image/*" onChange={(e) => setData('images', Array.from(e.target.files || []))} className={inputClass} />
                    </Field>

                    <label className="flex items-center gap-2 text-sm text-slate-200">
                        <input type="checkbox" checked={data.featured} onChange={(e) => setData('featured', e.target.checked)} />
                        Featured Product
                    </label>

                    <div className="flex gap-3">
                        <button disabled={processing} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-70">
                            {processing ? 'Updating...' : 'Update Product'}
                        </button>
                        <Link href={`/admin/products/${product.id}`} className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm text-slate-200 hover:bg-white/10">Cancel</Link>
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
