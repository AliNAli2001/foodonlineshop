import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';
import 'leaflet/dist/leaflet.css';

type ClientResult = {
    id: number;
    text: string;
    first_name?: string;
    last_name?: string;
    phone?: string;
};

type ProductResult = {
    id: number;
    text: string;
    price?: number;
    available_stock?: number;
    disabled?: boolean;
};

type OrderLine = {
    row_id: number;
    product_id: string;
    product_text: string;
    quantity: number;
    unit_price: number;
    available_stock: number;
    search: string;
};

type OrderCreateFormData = {
    client_id: string;
    client_name: string;
    client_phone_number: string;
    order_source: string;
    delivery_method: string;
    address_details: string;
    latitude: string;
    longitude: string;
    shipping_notes: string;
    admin_order_client_notes: string;
    products: OrderLine[];
};

const deliveryMethodOptions: Record<string, { value: string; labelKey: string }[]> = {
    inside_city: [
        { value: 'delivery', labelKey: 'admin.pages.orders.deliveryMethods.delivery' },
        { value: 'hand_delivered', labelKey: 'admin.pages.orders.deliveryMethods.handDelivered' },
    ],
    outside_city: [{ value: 'shipping', labelKey: 'admin.pages.orders.deliveryMethods.shipping' }],
};

function LocationPicker({
    latitude,
    longitude,
    onChange,
    label,
}: {
    latitude: string;
    longitude: string;
    onChange: (lat: string, lng: string) => void;
    label: string;
}) {
    const mapRef = useRef<any>(null);
    const markerRef = useRef<any>(null);
    const containerRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        let cancelled = false;

        const init = async () => {
            const L = await import('leaflet');
            if (cancelled || !containerRef.current || mapRef.current) return;

            const lat = Number(latitude || 33.5138);
            const lng = Number(longitude || 36.2765);

            const map = L.map(containerRef.current).setView([lat, lng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            const marker = L.circleMarker([lat, lng], {
                radius: 8,
                color: '#22d3ee',
                fillColor: '#22d3ee',
                fillOpacity: 0.5,
            }).addTo(map);

            map.on('click', (event: any) => {
                const nextLat = event.latlng.lat.toFixed(6);
                const nextLng = event.latlng.lng.toFixed(6);
                marker.setLatLng(event.latlng);
                onChange(nextLat, nextLng);
            });

            mapRef.current = map;
            markerRef.current = marker;
        };

        void init();

        return () => {
            cancelled = true;
            if (mapRef.current) {
                mapRef.current.remove();
                mapRef.current = null;
            }
        };
    }, []);

    useEffect(() => {
        if (!mapRef.current || !markerRef.current) return;
        const lat = Number(latitude);
        const lng = Number(longitude);
        if (Number.isFinite(lat) && Number.isFinite(lng)) {
            const latlng = { lat, lng };
            markerRef.current.setLatLng(latlng);
            mapRef.current.setView(latlng, Math.max(12, mapRef.current.getZoom()));
        }
    }, [latitude, longitude]);

    return (
        <div>
            <label className="mb-1 block text-sm text-slate-300">{label}</label>
            <div ref={containerRef} className="h-72 w-full rounded-xl border border-white/15" />
        </div>
    );
}

export default function OrdersCreate() {
    const { t } = useI18n();
    const nextRowIdRef = useRef(2);
    const productSearchTimers = useRef<Record<number, number>>({});
    const clientSearchTimer = useRef<number | null>(null);

    const [clientResults, setClientResults] = useState<ClientResult[]>([]);
    const [clientSearch, setClientSearch] = useState('');
    const [productResults, setProductResults] = useState<Record<number, ProductResult[]>>({});
    const [step, setStep] = useState<1 | 2 | 3>(1);
    const [stepError, setStepError] = useState('');
    const [productSubmitError, setProductSubmitError] = useState('');

    const { data, setData, post, processing, errors } = useForm<OrderCreateFormData>({
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
        products: [
            {
                row_id: 1,
                product_id: '',
                product_text: '',
                quantity: 1,
                unit_price: 0,
                available_stock: 0,
                search: '',
            } as OrderLine,
        ],
    });

    const orderSourceMethods = data.order_source ? deliveryMethodOptions[data.order_source] ?? [] : [];
    const showAddressFields = data.delivery_method !== '' && data.delivery_method !== 'hand_delivered';
    const showShippingNotes = data.delivery_method === 'shipping';

    const summary = useMemo(() => {
        let totalAmount = 0;
        let totalItems = 0;
        data.products.forEach((line: OrderLine) => {
            const qty = Number(line.quantity || 0);
            const price = Number(line.unit_price || 0);
            if (line.product_id && qty > 0) {
                totalItems += 1;
                totalAmount += qty * price;
            }
        });
        return { totalItems, totalAmount };
    }, [data.products]);

    useEffect(() => {
        if (clientSearchTimer.current) {
            window.clearTimeout(clientSearchTimer.current);
        }

        if (!clientSearch.trim()) {
            setClientResults([]);
            return;
        }

        clientSearchTimer.current = window.setTimeout(async () => {
            try {
                const response = await fetch(`/admin/orders/autocomplete/clients?q=${encodeURIComponent(clientSearch.trim())}`);
                const json = await response.json();
                setClientResults(Array.isArray(json.results) ? json.results : []);
            } catch {
                setClientResults([]);
            }
        }, 300);

        return () => {
            if (clientSearchTimer.current) {
                window.clearTimeout(clientSearchTimer.current);
            }
        };
    }, [clientSearch]);

    const updateLine = (rowId: number, patch: Partial<OrderLine>) => {
        setData(
            'products',
            data.products.map((line: OrderLine) => (line.row_id === rowId ? { ...line, ...patch } : line)),
        );
    };

    const fetchProducts = async (rowId: number, search: string) => {
        const selected = data.products
            .filter((line: OrderLine) => line.row_id !== rowId && line.product_id)
            .map((line: OrderLine) => line.product_id);

        const params = new URLSearchParams();
        params.set('q', search);
        selected.forEach((id) => params.append('exclude[]', id));

        try {
            const response = await fetch(`/admin/orders/autocomplete/products?${params.toString()}`);
            const json = await response.json();
            setProductResults((prev) => ({
                ...prev,
                [rowId]: Array.isArray(json.results) ? json.results : [],
            }));
        } catch {
            setProductResults((prev) => ({ ...prev, [rowId]: [] }));
        }
    };

    const onLineSearchChange = (rowId: number, value: string) => {
        updateLine(rowId, { search: value });

        if (productSearchTimers.current[rowId]) {
            window.clearTimeout(productSearchTimers.current[rowId]);
        }

        if (!value.trim()) {
            setProductResults((prev) => ({ ...prev, [rowId]: [] }));
            return;
        }

        productSearchTimers.current[rowId] = window.setTimeout(() => {
            void fetchProducts(rowId, value.trim());
        }, 300);
    };

    const selectProduct = (rowId: number, product: ProductResult) => {
        if (product.disabled) return;
        const currentLine = data.products.find((line: OrderLine) => line.row_id === rowId);
        const maxStock = Number(product.available_stock || 0);
        const currentQty = Number(currentLine?.quantity || 1);
        const safeQty = maxStock > 0 ? Math.min(Math.max(currentQty, 1), maxStock) : Math.max(currentQty, 1);

        updateLine(rowId, {
            product_id: String(product.id),
            product_text: product.text,
            quantity: safeQty,
            unit_price: Number(product.price || 0),
            available_stock: Number(product.available_stock || 0),
            search: '',
        });
        setProductResults((prev) => ({ ...prev, [rowId]: [] }));
    };

    const addLine = () => {
        const next = nextRowIdRef.current++;
        setData('products', [
            ...data.products,
            {
                row_id: next,
                product_id: '',
                product_text: '',
                quantity: 1,
                unit_price: 0,
                available_stock: 0,
                search: '',
            },
        ]);
    };

    const removeLine = (rowId: number) => {
        if (data.products.length === 1) return;
        setData(
            'products',
            data.products.filter((line: OrderLine) => line.row_id !== rowId),
        );
        setProductResults((prev) => {
            const next = { ...prev };
            delete next[rowId];
            return next;
        });
    };

    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        const selectedLines = data.products.filter((line: OrderLine) => line.product_id);
        if (selectedLines.length === 0) {
            setProductSubmitError(t('admin.pages.orders.create.validationNoProducts'));
            return;
        }

        const hasIncompleteLine = data.products.some((line: OrderLine) => !line.product_id);
        if (hasIncompleteLine) {
            setProductSubmitError(t('admin.pages.orders.create.validationIncompleteProduct'));
            return;
        }

        const hasInvalidQty = selectedLines.some((line: OrderLine) => {
            const qty = Number(line.quantity || 0);
            if (qty <= 0) return true;
            if (line.available_stock > 0 && qty > line.available_stock) return true;
            return false;
        });
        if (hasInvalidQty) {
            setProductSubmitError(t('admin.pages.orders.create.validationInvalidQuantity'));
            return;
        }

        setProductSubmitError('');

        const payload = {
            ...data,
            products: data.products
                .filter((line: OrderLine) => line.product_id && Number(line.quantity) > 0)
                .map((line: OrderLine) => ({
                    product_id: line.product_id,
                    quantity: Number(line.quantity),
                })),
        };

        post('/admin/orders', { data: payload });
    };

    const hasSelectedItems = data.products.some((line: OrderLine) => line.product_id && Number(line.quantity) > 0);
    const formErrors = errors as Record<string, string | undefined>;
    const serverError = formErrors.error;

    const isStepValid = (currentStep: 1 | 2 | 3) => {
        if (currentStep === 1) {
            return Boolean(data.client_name.trim() && data.client_phone_number.trim());
        }

        if (currentStep === 2) {
            if (!data.order_source || !data.delivery_method) return false;
            if (showAddressFields && !data.address_details.trim()) return false;
            return true;
        }

        return hasSelectedItems;
    };

    const goNextStep = () => {
        if (!isStepValid(step)) {
            setStepError(t('admin.pages.orders.create.stepper.validation'));
            return;
        }

        setStepError('');
        setStep((prev) => (prev < 3 ? ((prev + 1) as 1 | 2 | 3) : prev));
    };

    const goPrevStep = () => {
        setStepError('');
        setStep((prev) => (prev > 1 ? ((prev - 1) as 1 | 2 | 3) : prev));
    };

    const setStepDirect = (target: 1 | 2 | 3) => {
        if (target < step) {
            setStepError('');
            setStep(target);
            return;
        }

        if (target > step && !isStepValid(step)) {
            setStepError(t('admin.pages.orders.create.stepper.validation'));
            return;
        }

        setStepError('');
        setStep(target);
    };

    const changeLineQuantity = (rowId: number, nextQty: number) => {
        const line = data.products.find((row: OrderLine) => row.row_id === rowId);
        if (!line) return;

        const minimum = 1;
        const maximum = line.available_stock > 0 ? line.available_stock : Number.POSITIVE_INFINITY;
        const safeQty = Math.max(minimum, Math.min(nextQty, maximum));
        updateLine(rowId, { quantity: safeQty });
    };

    return (
        <AdminLayout title={t('admin.pages.orders.create.title')}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.orders.create.title')}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.orders.create.subtitle')}</p>
                    </div>
                    <Link href="/admin/orders" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-white/10">
                        {t('common.back')}
                    </Link>
                </section>

                {(serverError || Object.keys(errors).length > 0) && (
                    <div className="rounded-xl border border-rose-300/30 bg-rose-500/10 p-4 text-sm text-rose-200">
                        <p className="font-semibold">{t('admin.pages.orders.create.validationTitle')}</p>
                        {serverError && <p className="mt-1">{serverError}</p>}
                    </div>
                )}

                <form onSubmit={submit} className="grid gap-6 xl:grid-cols-3">
                    <div className="space-y-6 xl:col-span-2">
                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <div className="grid gap-2 sm:grid-cols-3">
                                {[
                                    { id: 1 as const, label: t('admin.pages.orders.create.stepper.customer') },
                                    { id: 2 as const, label: t('admin.pages.orders.create.stepper.delivery') },
                                    { id: 3 as const, label: t('admin.pages.orders.create.stepper.items') },
                                ].map((item) => {
                                    const active = step === item.id;
                                    const done = step > item.id;
                                    return (
                                        <button
                                            key={item.id}
                                            type="button"
                                            onClick={() => setStepDirect(item.id)}
                                            className={`flex items-center gap-2 rounded-xl border px-3 py-2 text-sm ${
                                                active
                                                    ? 'border-cyan-300/40 bg-cyan-400/10 text-cyan-200'
                                                    : done
                                                        ? 'border-emerald-300/40 bg-emerald-500/10 text-emerald-200'
                                                        : 'border-white/10 bg-white/[0.02] text-slate-300'
                                            }`}
                                        >
                                            <span className="inline-flex h-6 w-6 items-center justify-center rounded-full border border-current/30 text-xs">
                                                {item.id}
                                            </span>
                                            <span>{item.label}</span>
                                        </button>
                                    );
                                })}
                            </div>
                            {stepError && <p className="mt-3 text-sm text-rose-300">{stepError}</p>}
                        </section>

                        {step === 1 && (
                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h2 className="mb-4 text-lg font-semibold text-white">{t('admin.pages.orders.create.customerSection')}</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="md:col-span-2">
                                    <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.searchClient')}</label>
                                    <input
                                        type="text"
                                        value={clientSearch}
                                        onChange={(e) => setClientSearch(e.target.value)}
                                        placeholder={t('admin.pages.orders.create.searchClientPlaceholder')}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                    />
                                    {clientResults.length > 0 && (
                                        <div className="mt-2 max-h-44 overflow-auto rounded-xl border border-white/10 bg-slate-900/40">
                                            {clientResults.map((client) => {
                                                const fullName = `${client.first_name ?? ''} ${client.last_name ?? ''}`.trim();
                                                return (
                                                    <button
                                                        key={client.id}
                                                        type="button"
                                                        onClick={() => {
                                                            setData('client_id', String(client.id));
                                                            setData('client_name', fullName);
                                                            setData('client_phone_number', client.phone ?? '');
                                                            setClientSearch('');
                                                            setClientResults([]);
                                                        }}
                                                        className="flex w-full items-center justify-between border-b border-white/5 px-3 py-2 text-left text-sm text-slate-200 hover:bg-white/5"
                                                    >
                                                        <span>{client.text}</span>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    )}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.clientName')}</label>
                                    <input
                                        type="text"
                                        value={data.client_name}
                                        onChange={(e) => setData('client_name', e.target.value)}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.clientPhone')}</label>
                                    <input
                                        type="text"
                                        value={data.client_phone_number}
                                        onChange={(e) => setData('client_phone_number', e.target.value)}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                    />
                                </div>
                            </div>
                        </section>
                        )}

                        {step === 2 && (
                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h2 className="mb-4 text-lg font-semibold text-white">{t('admin.pages.orders.create.deliverySection')}</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.orderSource')}</label>
                                    <select
                                        value={data.order_source}
                                        onChange={(e) => {
                                            setData('order_source', e.target.value);
                                            setData('delivery_method', '');
                                        }}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                        required
                                    >
                                        <option value="">{t('admin.pages.orders.create.select')}</option>
                                        <option value="inside_city">{t('admin.pages.orders.index.sourceInside')}</option>
                                        <option value="outside_city">{t('admin.pages.orders.index.sourceOutside')}</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.deliveryMethod')}</label>
                                    <select
                                        value={data.delivery_method}
                                        onChange={(e) => setData('delivery_method', e.target.value)}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                        required
                                    >
                                        <option value="">{t('admin.pages.orders.create.select')}</option>
                                        {orderSourceMethods.map((method) => (
                                            <option key={method.value} value={method.value}>
                                                {t(method.labelKey)}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {showAddressFields && (
                                    <>
                                        <div className="md:col-span-2">
                                            <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.addressDetails')}</label>
                                            <textarea
                                                value={data.address_details}
                                                onChange={(e) => setData('address_details', e.target.value)}
                                                rows={3}
                                                className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.latitude')}</label>
                                            <input
                                                type="number"
                                                step="0.000001"
                                                value={data.latitude}
                                                onChange={(e) => setData('latitude', e.target.value)}
                                                className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                            />
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.longitude')}</label>
                                            <input
                                                type="number"
                                                step="0.000001"
                                                value={data.longitude}
                                                onChange={(e) => setData('longitude', e.target.value)}
                                                className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                            />
                                        </div>
                                        <div className="md:col-span-2">
                                            <LocationPicker
                                                latitude={data.latitude}
                                                longitude={data.longitude}
                                                onChange={(lat, lng) => {
                                                    setData('latitude', lat);
                                                    setData('longitude', lng);
                                                }}
                                                label={t('admin.pages.orders.create.mapLabel')}
                                            />
                                        </div>
                                    </>
                                )}

                                {showShippingNotes && (
                                    <div className="md:col-span-2">
                                        <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.shippingNotes')}</label>
                                        <textarea
                                            value={data.shipping_notes}
                                            onChange={(e) => setData('shipping_notes', e.target.value)}
                                            rows={2}
                                            className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                        />
                                    </div>
                                )}

                                <div className="md:col-span-2">
                                    <label className="mb-1 block text-sm text-slate-300">{t('admin.pages.orders.create.adminNotes')}</label>
                                    <textarea
                                        value={data.admin_order_client_notes}
                                        onChange={(e) => setData('admin_order_client_notes', e.target.value)}
                                        rows={2}
                                        className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                    />
                                </div>
                            </div>
                        </section>
                        )}

                        {step === 3 && (
                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <div className="mb-4 flex items-center justify-between">
                                <h2 className="text-lg font-semibold text-white">{t('admin.pages.orders.create.itemsSection')}</h2>
                                <button type="button" onClick={addLine} className="rounded-xl border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-sm font-medium text-cyan-200 hover:bg-cyan-400/20">
                                    + {t('admin.pages.orders.create.addItem')}
                                </button>
                            </div>

                            <div className="space-y-3">
                                {data.products.map((line: OrderLine) => (
                                    <div key={line.row_id} className="rounded-xl border border-white/10 bg-slate-900/40 p-3">
                                        <div className="grid gap-3 md:grid-cols-12">
                                            <div className="md:col-span-7">
                                                <label className="mb-1 block text-xs text-slate-400">{t('admin.pages.orders.create.product')}</label>
                                                <input
                                                    type="text"
                                                    value={line.search || line.product_text}
                                                    onChange={(e) => onLineSearchChange(line.row_id, e.target.value)}
                                                    placeholder={t('admin.pages.orders.create.searchProductPlaceholder')}
                                                    className="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                                />
                                                {productResults[line.row_id]?.length ? (
                                                    <div className="mt-2 max-h-44 overflow-auto rounded-lg border border-white/10 bg-slate-900/80">
                                                        {productResults[line.row_id].map((result) => (
                                                            <button
                                                                key={result.id}
                                                                type="button"
                                                                disabled={!!result.disabled}
                                                                onClick={() => selectProduct(line.row_id, result)}
                                                                className={`flex w-full items-center justify-between border-b border-white/5 px-3 py-2 text-left text-xs ${
                                                                    result.disabled ? 'cursor-not-allowed text-slate-500' : 'text-slate-200 hover:bg-white/5'
                                                                }`}
                                                            >
                                                                <span>{result.text}</span>
                                                            </button>
                                                        ))}
                                                    </div>
                                                ) : null}
                                            </div>

                                            <div className="md:col-span-3">
                                                <label className="mb-1 block text-xs text-slate-400">{t('admin.pages.orders.create.quantity')}</label>
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => changeLineQuantity(line.row_id, Number(line.quantity) - 1)}
                                                        className="rounded-lg border border-white/15 bg-white/5 px-2.5 py-2 text-sm text-slate-100 hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-40"
                                                        disabled={Number(line.quantity) <= 1}
                                                    >
                                                        -
                                                    </button>
                                                    <input
                                                        type="number"
                                                        min="1"
                                                        max={line.available_stock || undefined}
                                                        value={line.quantity}
                                                        onChange={(e) => changeLineQuantity(line.row_id, Number(e.target.value || 1))}
                                                        className="w-full rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-center text-sm text-white outline-none focus:border-cyan-300/40"
                                                        required
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => changeLineQuantity(line.row_id, Number(line.quantity) + 1)}
                                                        className="rounded-lg border border-cyan-300/30 bg-cyan-500/10 px-2.5 py-2 text-sm text-cyan-200 hover:bg-cyan-500/20 disabled:cursor-not-allowed disabled:opacity-40"
                                                        disabled={!line.product_id || (line.available_stock > 0 && Number(line.quantity) >= line.available_stock)}
                                                    >
                                                        +
                                                    </button>
                                                </div>
                                                {!line.product_id && (
                                                    <p className="mt-1 text-[11px] text-amber-300">{t('admin.pages.orders.create.selectProductFirst')}</p>
                                                )}
                                                {line.product_id && line.available_stock > 0 && Number(line.quantity) >= line.available_stock && (
                                                    <p className="mt-1 text-[11px] text-amber-300">{t('admin.pages.orders.create.reachedMaxStock')}</p>
                                                )}
                                            </div>

                                            <div className="md:col-span-2">
                                                <label className="mb-1 block text-xs text-slate-400">{t('common.actions')}</label>
                                                <button
                                                    type="button"
                                                    onClick={() => removeLine(line.row_id)}
                                                    disabled={data.products.length === 1}
                                                    className="w-full rounded-lg border border-rose-300/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-200 hover:bg-rose-500/20 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    {t('admin.pages.orders.create.remove')}
                                                </button>
                                            </div>
                                        </div>

                                        {line.product_id ? (
                                            <p className="mt-2 text-xs text-slate-400">
                                                {t('admin.pages.orders.create.unitPrice')}: ${Number(line.unit_price).toFixed(2)} | {t('admin.pages.orders.create.availableStock')}: {line.available_stock}
                                            </p>
                                        ) : null}
                                    </div>
                                ))}
                            </div>
                        </section>
                        )}

                        <div className="flex flex-wrap gap-3">
                            {step > 1 && (
                            <button type="button" onClick={goPrevStep} className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm font-medium text-slate-100 hover:bg-white/10">
                                    {t('admin.pages.orders.create.stepper.previous')}
                                </button>
                            )}
                            {step < 3 ? (
                                <button type="button" onClick={goNextStep} className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
                                    {t('admin.pages.orders.create.stepper.next')}
                                </button>
                            ) : (
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    {processing ? t('admin.pages.orders.create.creating') : t('admin.pages.orders.create.submit')}
                                </button>
                            )}
                            <Link href="/admin/orders" className="rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm font-medium text-slate-100 hover:bg-white/10">
                                {t('common.cancel')}
                            </Link>
                        </div>
                        {productSubmitError && step === 3 && <p className="text-sm text-rose-300">{productSubmitError}</p>}
                    </div>

                    <aside className="xl:col-span-1">
                        <div className="sticky top-24 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h3 className="text-lg font-semibold text-white">{t('admin.pages.orders.create.summary')}</h3>
                            <div className="mt-3 space-y-2 text-sm text-slate-300">
                                <p className="flex items-center justify-between"><span>{t('admin.pages.orders.create.summaryItems')}</span><strong className="text-white">{summary.totalItems}</strong></p>
                                <p className="flex items-center justify-between"><span>{t('admin.pages.orders.create.summaryTotal')}</span><strong className="text-white">${summary.totalAmount.toFixed(2)}</strong></p>
                            </div>
                            <p className="mt-4 rounded-lg border border-cyan-300/20 bg-cyan-400/10 p-3 text-xs text-cyan-100">
                                {t('admin.pages.orders.create.summaryNote')}
                            </p>
                        </div>
                    </aside>
                </form>
            </div>
        </AdminLayout>
    );
}
