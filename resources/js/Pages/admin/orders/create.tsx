import React, { useMemo, useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

const deliveryMethodOptions = {
    inside_city: [
        { value: 'delivery', label: 'Delivery | ?????' },
        { value: 'hand_delivered', label: 'Hand Delivered | ????? ????' },
    ],
    outside_city: [{ value: 'shipping', label: 'Shipping | ???' }],
};

function productStock(product) {
    return product.stock_available_quantity ?? product.stock?.available_quantity ?? 0;
}

export default function OrdersCreate() {
  const { t } = useI18n();
    const { products = [], clients = [] } = usePage().props;
    const [clientQuery, setClientQuery] = useState('');

    const { data, setData, post, processing, errors } = useForm({
        client_id: '',
        client_name: '',
        client_phone_number: '',
        order_source: '',
        delivery_method: '',
        address_details: '',
        latitude: '',
        longitude: '',
        shipping_notes: '',
        admin_order_client_notes: '',
        products: [{ product_id: '', quantity: 1 }],
    });

    const availableClients = useMemo(() => {
        const q = clientQuery.trim().toLowerCase();
        if (!q) return clients.slice(0, 20);

        return clients.filter((c) => {
            const fullName = `${c.first_name ?? ''} ${c.last_name ?? ''}`.toLowerCase();
            return fullName.includes(q) || (c.phone ?? '').toLowerCase().includes(q) || (c.email ?? '').toLowerCase().includes(q);
        });
    }, [clients, clientQuery]);

    const selectedProductIds = useMemo(() => new Set(data.products.map((p) => String(p.product_id)).filter(Boolean)), [data.products]);

    const orderSourceMethods = data.order_source ? deliveryMethodOptions[data.order_source] ?? [] : [];

    const showAddressFields = data.delivery_method !== 'hand_delivered' && data.delivery_method !== '';
    const showShippingNotes = data.delivery_method === 'shipping';

    const lineItems = useMemo(() => {
        return data.products.map((line) => {
            const product = products.find((p) => String(p.id) === String(line.product_id));
            const price = Number(product?.selling_price ?? 0);
            const quantity = Number(line.quantity ?? 0);
            return {
                name: product?.name_en || product?.name_ar || '-',
                price,
                quantity,
                subtotal: price * quantity,
            };
        });
    }, [data.products, products]);

    const summary = useMemo(() => {
        const totalItems = lineItems.reduce((sum, line) => sum + (line.quantity > 0 && line.price > 0 ? 1 : 0), 0);
        const totalAmount = lineItems.reduce((sum, line) => sum + line.subtotal, 0);
        return { totalItems, totalAmount };
    }, [lineItems]);

    const updateLine = (index, key, value) => {
        const next = [...data.products];
        next[index] = { ...next[index], [key]: value };
        setData('products', next);
    };

    const addLine = () => setData('products', [...data.products, { product_id: '', quantity: 1 }]);

    const removeLine = (index) => {
        if (data.products.length === 1) return;
        setData('products', data.products.filter((_, i) => i !== index));
    };

    const submit = (e) => {
        e.preventDefault();

        const payload = {
            ...data,
            products: data.products.filter((line) => line.product_id && Number(line.quantity) > 0),
        };

        post('/admin/orders', {
            data: payload,
        });
    };

    return (
        <AdminLayout title={t('admin.pages.orders.create.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Create Order | ????? ???</h1>
                        <p className="text-sm text-slate-300">Simple order creation flow with bilingual labels.</p>
                    </div>
                    <Link href="/admin/orders" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-white/10">
                        Back | ????
                    </Link>
                </section>

                {(errors.error || Object.keys(errors).length > 0) && (
                    <div className="rounded-xl border border-rose-300/30 bg-rose-500/10 p-4 text-sm text-rose-200">
                        <p className="font-semibold">Please review form errors | ???? ?????? ???????</p>
                        {errors.error && <p className="mt-1">{errors.error}</p>}
                    </div>
                )}

                <form onSubmit={submit} className="grid gap-6 xl:grid-cols-3">
                    <div className="space-y-6 xl:col-span-2">
                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h2 className="mb-4 text-lg font-semibold text-white">Customer | ??????</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="md:col-span-2">
                                    <label className="mb-1 block text-sm text-slate-300">Search Existing Client | ??? ?? ????</label>
                                    <input
                                        type="text"
                                        value={clientQuery}
                                        onChange={(e) => setClientQuery(e.target.value)}
                                        placeholder="Name / phone / email"
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                    />
                                    <div className="mt-2 max-h-44 overflow-auto rounded-xl border border-white/10 bg-slate-900/40">
                                        {availableClients.map((client) => {
                                            const fullName = `${client.first_name ?? ''} ${client.last_name ?? ''}`.trim();
                                            return (
                                                <button
                                                    key={client.id}
                                                    type="button"
                                                    onClick={() => {
                                                        setData('client_id', String(client.id));
                                                        setData('client_name', fullName);
                                                        setData('client_phone_number', client.phone ?? '');
                                                    }}
                                                    className={`flex w-full items-center justify-between border-b border-white/5 px-3 py-2 text-left text-sm hover:bg-white/5 ${
                                                        String(data.client_id) === String(client.id) ? 'bg-cyan-400/10 text-cyan-200' : 'text-slate-200'
                                                    }`}
                                                >
                                                    <span>{fullName}</span>
                                                    <span className="text-xs text-slate-400">{client.phone ?? '-'}</span>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm text-slate-300">Client Name | ??? ??????</label>
                                    <input
                                        type="text"
                                        value={data.client_name}
                                        onChange={(e) => setData('client_name', e.target.value)}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm text-slate-300">Phone Number | ??? ??????</label>
                                    <input
                                        type="text"
                                        value={data.client_phone_number}
                                        onChange={(e) => setData('client_phone_number', e.target.value)}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                    />
                                </div>
                            </div>
                        </section>

                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h2 className="mb-4 text-lg font-semibold text-white">Source & Delivery | ?????? ????????</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-sm text-slate-300">Order Source | ???? ?????</label>
                                    <select
                                        value={data.order_source}
                                        onChange={(e) => {
                                            const source = e.target.value;
                                            setData('order_source', source);
                                            setData('delivery_method', '');
                                        }}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                        required
                                    >
                                        <option value="">Select | ????</option>
                                        <option value="inside_city">Inside City | ???? ???????</option>
                                        <option value="outside_city">Outside City | ???? ???????</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm text-slate-300">Delivery Method | ????? ???????</label>
                                    <select
                                        value={data.delivery_method}
                                        onChange={(e) => setData('delivery_method', e.target.value)}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                        required
                                    >
                                        <option value="">Select | ????</option>
                                        {orderSourceMethods.map((method) => (
                                            <option key={method.value} value={method.value}>
                                                {method.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {showAddressFields && (
                                    <>
                                        <div className="md:col-span-2">
                                            <label className="mb-1 block text-sm text-slate-300">Address Details | ?????? ???????</label>
                                            <textarea
                                                value={data.address_details}
                                                onChange={(e) => setData('address_details', e.target.value)}
                                                rows={3}
                                                className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-sm text-slate-300">Latitude | ?? ?????</label>
                                            <input
                                                type="number"
                                                step="0.000001"
                                                value={data.latitude}
                                                onChange={(e) => setData('latitude', e.target.value)}
                                                className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                            />
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-sm text-slate-300">Longitude | ?? ?????</label>
                                            <input
                                                type="number"
                                                step="0.000001"
                                                value={data.longitude}
                                                onChange={(e) => setData('longitude', e.target.value)}
                                                className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                            />
                                        </div>
                                    </>
                                )}

                                {showShippingNotes && (
                                    <div className="md:col-span-2">
                                        <label className="mb-1 block text-sm text-slate-300">Shipping Notes | ??????? ?????</label>
                                        <textarea
                                            value={data.shipping_notes}
                                            onChange={(e) => setData('shipping_notes', e.target.value)}
                                            rows={2}
                                            className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                        />
                                    </div>
                                )}

                                <div className="md:col-span-2">
                                    <label className="mb-1 block text-sm text-slate-300">Admin Notes | ??????? ???????</label>
                                    <textarea
                                        value={data.admin_order_client_notes}
                                        onChange={(e) => setData('admin_order_client_notes', e.target.value)}
                                        rows={2}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                    />
                                </div>
                            </div>
                        </section>

                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <div className="mb-4 flex items-center justify-between">
                                <h2 className="text-lg font-semibold text-white">Order Items | ????? ?????</h2>
                                <button type="button" onClick={addLine} className="rounded-xl border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-sm font-medium text-cyan-200 hover:bg-cyan-400/20">
                                    + Add Item | ????? ????
                                </button>
                            </div>

                            <div className="space-y-3">
                                {data.products.map((line, index) => {
                                    const selected = products.find((p) => String(p.id) === String(line.product_id));
                                    const stock = selected ? productStock(selected) : 0;

                                    return (
                                        <div key={index} className="rounded-xl border border-white/10 bg-slate-900/40 p-3">
                                            <div className="grid gap-3 md:grid-cols-12">
                                                <div className="md:col-span-7">
                                                    <label className="mb-1 block text-xs text-slate-400">Product | ??????</label>
                                                    <select
                                                        value={line.product_id}
                                                        onChange={(e) => updateLine(index, 'product_id', e.target.value)}
                                                        className="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                                        required
                                                    >
                                                        <option value="">Select Product | ???? ????</option>
                                                        {products.map((product) => {
                                                            const id = String(product.id);
                                                            const usedElsewhere = selectedProductIds.has(id) && id !== String(line.product_id);
                                                            return (
                                                                <option key={product.id} value={product.id} disabled={usedElsewhere || productStock(product) <= 0}>
                                                                    {product.name_en} / {product.name_ar} - ${Number(product.selling_price).toFixed(2)} (stock: {productStock(product)})
                                                                </option>
                                                            );
                                                        })}
                                                    </select>
                                                </div>

                                                <div className="md:col-span-3">
                                                    <label className="mb-1 block text-xs text-slate-400">Quantity | ??????</label>
                                                    <input
                                                        type="number"
                                                        min="1"
                                                        max={stock || undefined}
                                                        value={line.quantity}
                                                        onChange={(e) => updateLine(index, 'quantity', e.target.value)}
                                                        className="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                                        required
                                                    />
                                                </div>

                                                <div className="md:col-span-2">
                                                    <label className="mb-1 block text-xs text-slate-400">Action | ?????</label>
                                                    <button
                                                        type="button"
                                                        onClick={() => removeLine(index)}
                                                        disabled={data.products.length === 1}
                                                        className="w-full rounded-lg border border-rose-300/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-200 hover:bg-rose-500/20 disabled:cursor-not-allowed disabled:opacity-40"
                                                    >
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>

                                            {selected && (
                                                <p className="mt-2 text-xs text-slate-400">
                                                    Unit ${Number(selected.selling_price).toFixed(2)} x {Number(line.quantity || 0)} = ${
                                                        (Number(selected.selling_price) * Number(line.quantity || 0)).toFixed(2)
                                                    }
                                                </p>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        </section>

                        <div className="flex flex-wrap gap-3">
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-70"
                            >
                                {processing ? 'Creating...' : 'Create Order | ????? ?????'}
                            </button>
                            <Link href="/admin/orders" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm font-medium text-slate-100 hover:bg-white/10">
                                Cancel | ?????
                            </Link>
                        </div>
                    </div>

                    <aside className="xl:col-span-1">
                        <div className="sticky top-24 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h3 className="text-lg font-semibold text-white">Summary | ??????</h3>
                            <div className="mt-3 space-y-2 text-sm text-slate-300">
                                <p className="flex items-center justify-between"><span>Items | ???????</span><strong className="text-white">{summary.totalItems}</strong></p>
                                <p className="flex items-center justify-between"><span>Total | ????????</span><strong className="text-white">${summary.totalAmount.toFixed(2)}</strong></p>
                            </div>
                            <p className="mt-4 rounded-lg border border-cyan-300/20 bg-cyan-400/10 p-3 text-xs text-cyan-100">
                                This order is created from admin workflow and will follow system transitions.
                            </p>
                        </div>
                    </aside>
                </form>
            </div>
        </AdminLayout>
    );
}


