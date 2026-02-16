import React, { useMemo, useState } from 'react';
import { Link, router, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

const statusClass: Record<string, string> = {
    pending: 'bg-amber-400/20 text-amber-200 ring-amber-300/30',
    confirmed: 'bg-sky-400/20 text-sky-200 ring-sky-300/30',
    shipped: 'bg-blue-400/20 text-blue-200 ring-blue-300/30',
    delivered: 'bg-indigo-400/20 text-indigo-200 ring-indigo-300/30',
    done: 'bg-emerald-400/20 text-emerald-200 ring-emerald-300/30',
    canceled: 'bg-rose-400/20 text-rose-200 ring-rose-300/30',
    returned: 'bg-slate-300/20 text-slate-200 ring-slate-300/30',
    rejected: 'bg-rose-400/20 text-rose-200 ring-rose-300/30',
};

function normalizePhone(value?: string | null): string {
    if (!value) return '';
    return value.replace(/[^\d+]/g, '');
}

function whatsappUrl(phone?: string | null, message?: string): string {
    const normalized = normalizePhone(phone).replace('+', '');
    if (!normalized) return '#';
    const text = encodeURIComponent(message || '');
    return `https://wa.me/${normalized}${text ? `?text=${text}` : ''}`;
}

function formatDateTime(value?: string | null): string {
    if (!value) return '-';
    const normalized = value.replace(/(\.\d{3})\d+Z$/, '$1Z');
    const date = new Date(normalized);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
    });
}

export default function OrdersShow() {
    const { t } = useI18n();
    const { order, deliveryPersons = [], availableTransitions = [] } = usePage<any>().props;
    const [rejectOpen, setRejectOpen] = useState(false);
    const [assignMenuOpen, setAssignMenuOpen] = useState(false);
    const [deliverySearch, setDeliverySearch] = useState('');

    const rejectForm = useForm({ reason: '' });
    const assignForm = useForm({ status: 'delivered', delivery_id: '' });

    const orderTotal = Number(order.total_amount ?? 0).toFixed(2);
    const canDeliverWithAssign = order.delivery_method === 'delivery' && !order.delivery_id;
    const items = order.items ?? [];
    const customerPhone = order.client?.phone || order.client_phone_number || '';
    const deliveryPhone = order.delivery?.phone || '';

    const sourceLabel = (source: string) => {
        if (source === 'inside_city') return t('admin.pages.orders.index.sourceInside');
        if (source === 'outside_city') return t('admin.pages.orders.index.sourceOutside');
        return source || '-';
    };

    const deliveryLabel = (method: string) => {
        const key = `admin.pages.orders.deliveryMethods.${method === 'hand_delivered' ? 'handDelivered' : method}`;
        return t(key, method || '-');
    };

    const statusLabel = (status: string) => {
        return t(`admin.pages.orders.status.${status}`, status || '-');
    };

    const submitTransition = (status: string) => {
        router.post(`/admin/orders/${order.id}/update-status`, { status });
    };

    const submitConfirm = () => router.post(`/admin/orders/${order.id}/confirm`);

    const submitReject = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        rejectForm.post(`/admin/orders/${order.id}/reject`, {
            onSuccess: () => {
                setRejectOpen(false);
                rejectForm.reset();
            },
        });
    };

    const submitAssignDelivered = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        assignForm.post(`/admin/orders/${order.id}/update-status`, {
            onSuccess: () => {
                setAssignMenuOpen(false);
                setDeliverySearch('');
            },
        });
    };

    const filteredDeliveryPersons = useMemo(() => {
        const query = deliverySearch.trim().toLowerCase();
        if (!query) return deliveryPersons;
        return deliveryPersons.filter((d: any) => {
            const full = `${d.first_name ?? ''} ${d.last_name ?? ''}`.toLowerCase();
            const phone = String(d.phone ?? '').toLowerCase();
            return full.includes(query) || phone.includes(query);
        });
    }, [deliveryPersons, deliverySearch]);

    const selectedDelivery = useMemo(
        () => deliveryPersons.find((d: any) => String(d.id) === String(assignForm.data.delivery_id)),
        [deliveryPersons, assignForm.data.delivery_id],
    );

    const transitionButtons = useMemo(() => {
        return availableTransitions.map((status: string) => {
            if (status === 'confirmed' && order.status === 'pending') {
                return (
                    <button key={status} type="button" onClick={submitConfirm} className="rounded-xl bg-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-300">
                        {t('admin.pages.orders.show.confirm')}
                    </button>
                );
            }

            if ((status === 'canceled' || status === 'rejected') && order.status === 'pending') {
                return (
                    <button key={status} type="button" onClick={() => setRejectOpen(true)} className="rounded-xl border border-rose-300/30 bg-rose-500/10 px-4 py-2 text-sm font-medium text-rose-200 hover:bg-rose-500/20">
                        {t('admin.pages.orders.show.reject')}
                    </button>
                );
            }

            if (status === 'delivered' && canDeliverWithAssign) return null;

            return (
                <button
                    key={status}
                    type="button"
                    onClick={() => submitTransition(status)}
                    className="rounded-xl border border-cyan-300/30 bg-cyan-400/10 px-4 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-400/20"
                >
                    {statusLabel(status)}
                </button>
            );
        });
    }, [availableTransitions, order.status, canDeliverWithAssign]);

    const preparedMessages = useMemo(() => {
        const byStatus: Record<string, Array<{ key: string; labelKey: string; textKey: string }>> = {
            pending: [{ key: 'pending', labelKey: 'admin.pages.orders.show.messages.templates.pendingLabel', textKey: 'admin.pages.orders.show.messages.templates.pendingText' }],
            confirmed: [{ key: 'confirmed', labelKey: 'admin.pages.orders.show.messages.templates.confirmedLabel', textKey: 'admin.pages.orders.show.messages.templates.confirmedText' }],
            shipped: [{ key: 'shipped', labelKey: 'admin.pages.orders.show.messages.templates.shippedLabel', textKey: 'admin.pages.orders.show.messages.templates.shippedText' }],
            delivered: [{ key: 'delivered', labelKey: 'admin.pages.orders.show.messages.templates.deliveredLabel', textKey: 'admin.pages.orders.show.messages.templates.deliveredText' }],
            done: [{ key: 'done', labelKey: 'admin.pages.orders.show.messages.templates.doneLabel', textKey: 'admin.pages.orders.show.messages.templates.doneText' }],
            canceled: [{ key: 'canceled', labelKey: 'admin.pages.orders.show.messages.templates.canceledLabel', textKey: 'admin.pages.orders.show.messages.templates.canceledText' }],
            rejected: [{ key: 'rejected', labelKey: 'admin.pages.orders.show.messages.templates.rejectedLabel', textKey: 'admin.pages.orders.show.messages.templates.rejectedText' }],
            returned: [{ key: 'returned', labelKey: 'admin.pages.orders.show.messages.templates.returnedLabel', textKey: 'admin.pages.orders.show.messages.templates.returnedText' }],
        };

        const rows = byStatus[order.status] || [{ key: 'generic', labelKey: 'admin.pages.orders.show.messages.templates.genericLabel', textKey: 'admin.pages.orders.show.messages.templates.genericText' }];
        return rows.map((row) => ({
            key: row.key,
            label: t(row.labelKey),
            text: t(row.textKey),
        }));
    }, [order.status, t]);

    return (
        <AdminLayout title={`${t('admin.pages.orders.show.title')} #${order.id}`}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">{t('admin.pages.orders.show.title')} #{order.id}</h1>
                        <p className="text-sm text-slate-300">{t('admin.pages.orders.show.subtitle')}</p>
                    </div>
                    <Link href="/admin/orders" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-white/10">
                        {t('admin.pages.orders.show.backToOrders')}
                    </Link>
                </section>

                <div className="grid gap-6 xl:grid-cols-3">
                    <div className="space-y-6 xl:col-span-2">
                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h2 className="mb-4 text-lg font-semibold text-white">{t('admin.pages.orders.show.orderInformation')}</h2>
                            <div className="grid gap-3 text-sm text-slate-200 md:grid-cols-2">
                                <p><span className="text-slate-400">{t('admin.pages.orders.show.customer')}: </span>{order.client_id ? `${order.client?.first_name ?? ''} ${order.client?.last_name ?? ''}` : (order.client_name || '-')}</p>
                                <p><span className="text-slate-400">{t('admin.pages.orders.show.phone')}: </span>{order.client?.phone || order.client_phone_number || '-'}</p>
                                <p><span className="text-slate-400">{t('admin.pages.orders.show.date')}: </span>{formatDateTime(order.order_date || order.created_at)}</p>
                                <p><span className="text-slate-400">{t('admin.pages.orders.show.source')}: </span>{sourceLabel(order.order_source)}</p>
                                <p><span className="text-slate-400">{t('admin.pages.orders.show.deliveryMethod')}: </span>{deliveryLabel(order.delivery_method)}</p>
                                <p><span className="text-slate-400">{t('common.status')}: </span><span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${statusClass[order.status] ?? 'bg-slate-300/20 text-slate-200 ring-slate-300/30'}`}>{statusLabel(order.status)}</span></p>
                                <p className="md:col-span-2"><span className="text-slate-400">{t('admin.pages.orders.show.address')}: </span>{order.address_details || '-'}</p>
                                {order.shipping_notes && <p className="md:col-span-2"><span className="text-slate-400">{t('admin.pages.orders.show.shippingNotes')}: </span>{order.shipping_notes}</p>}
                                {order.admin_order_client_notes && <p className="md:col-span-2"><span className="text-slate-400">{t('admin.pages.orders.show.adminNotes')}: </span>{order.admin_order_client_notes}</p>}
                            </div>
                        </section>

                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h2 className="mb-4 text-lg font-semibold text-white">{t('admin.pages.orders.show.orderItems')}</h2>
                            <div className="overflow-x-auto">
                                <table className="min-w-full">
                                    <thead>
                                        <tr>
                                            {[t('admin.pages.orders.create.product'), t('admin.pages.orders.show.unitPrice'), t('admin.pages.orders.create.quantity'), t('admin.pages.orders.show.subtotal'), t('admin.pages.orders.show.batches')].map((h) => (
                                                <th key={h} className="pb-3 pr-4 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">{h}</th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {items.map((item: any) => {
                                            const batches = item.batches ?? [];
                                            return (
                                                <tr key={item.id} className="border-t border-white/10">
                                                    <td className="py-3 pr-4 text-sm text-slate-200">{item.product?.name_en || item.product?.name_ar || '-'}</td>
                                                    <td className="py-3 pr-4 text-sm text-slate-200">${Number(item.unit_price ?? 0).toFixed(2)}</td>
                                                    <td className="py-3 pr-4 text-sm text-slate-200">{item.quantity}</td>
                                                    <td className="py-3 pr-4 text-sm text-slate-200">${(Number(item.unit_price ?? 0) * Number(item.quantity ?? 0)).toFixed(2)}</td>
                                                    <td className="py-3 pr-4 text-sm text-slate-300">
                                                        {batches.length === 0
                                                            ? '-'
                                                            : batches.map((b: any, i: number) => {
                                                                  const inv = b.inventory_batch || b.inventoryBatch;
                                                                  return (
                                                                      <div key={i} className="mb-1 rounded-lg border border-white/10 bg-white/[0.03] px-2 py-1 text-xs">
                                                                          {t('admin.pages.orders.show.batch')}: {inv?.batch_number ?? '-'} | {t('admin.pages.orders.create.quantity')}: {b.quantity}
                                                                      </div>
                                                                  );
                                                              })}
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h2 className="mb-4 text-lg font-semibold text-white">{t('admin.pages.orders.show.statusActions')}</h2>
                            {availableTransitions.length === 0 ? (
                                <p className="text-sm text-slate-400">{t('admin.pages.orders.show.noMoreActions')}</p>
                            ) : (
                                <div className="flex flex-wrap gap-2">{transitionButtons}</div>
                            )}

                            {canDeliverWithAssign && availableTransitions.includes('delivered') && (
                                <div className="mt-4 rounded-xl border border-white/10 bg-slate-900/40 p-3">
                                    <p className="mb-2 text-sm text-slate-200">{t('admin.pages.orders.show.assignBeforeDelivered')}</p>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            onClick={() => setAssignMenuOpen(true)}
                                            className="rounded-lg border border-cyan-300/30 bg-cyan-500/10 px-3 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-500/20"
                                        >
                                            {t('admin.pages.orders.show.openDeliveryMenu')}
                                        </button>
                                        {selectedDelivery && (
                                            <p className="text-xs text-slate-300">
                                                {t('admin.pages.orders.show.selectedDelivery')}: {selectedDelivery.first_name} {selectedDelivery.last_name} ({selectedDelivery.phone})
                                            </p>
                                        )}
                                    </div>
                                </div>
                            )}
                        </section>
                    </div>

                    <aside className="space-y-4 xl:col-span-1">
                        <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h3 className="text-lg font-semibold text-white">{t('admin.pages.orders.show.summary')}</h3>
                            <p className="mt-2 flex items-center justify-between text-sm text-slate-300">
                                <span>{t('admin.pages.orders.show.totalAmount')}</span>
                                <strong className="text-white">${orderTotal}</strong>
                            </p>
                            {order.delivery_id && (
                                <div className="mt-3 rounded-lg border border-white/10 bg-white/[0.03] p-3 text-xs text-slate-300">
                                    {t('admin.pages.orders.show.deliveryPerson')}: {order.delivery?.first_name} {order.delivery?.last_name}
                                    <br />
                                    {t('admin.pages.orders.show.phone')}: {order.delivery?.phone}
                                </div>
                            )}
                        </div>

                        <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h3 className="text-lg font-semibold text-white">{t('admin.pages.orders.show.messages.title')}</h3>
                            <p className="mt-1 text-xs text-slate-400">{t('admin.pages.orders.show.messages.subtitle')}</p>
                            <p className="mt-2 inline-flex rounded-full border border-white/10 bg-white/[0.03] px-2.5 py-1 text-[11px] text-slate-300">
                                {t('admin.pages.orders.show.messages.forStatus')}: {statusLabel(order.status)}
                            </p>

                            <div className="mt-3 space-y-2">
                                {preparedMessages.map((msg) => (
                                    <div key={msg.key} className="rounded-lg border border-white/10 bg-slate-900/40 p-3">
                                        <p className="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">{msg.label}</p>
                                        <p className="mb-3 text-xs text-slate-400">{msg.text}</p>
                                        <div className="flex flex-wrap gap-2">
                                            <a
                                                href={whatsappUrl(customerPhone, msg.text)}
                                                target="_blank"
                                                rel="noreferrer"
                                                className={`inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs ${customerPhone ? 'border-green-300/30 bg-green-500/10 text-green-200 hover:bg-green-500/20' : 'pointer-events-none border-white/10 bg-white/5 text-slate-500'}`}
                                            >
                                                <SendIcon />
                                                {t('admin.pages.orders.show.messages.sendToCustomer')}
                                            </a>
                                            <a
                                                href={whatsappUrl(deliveryPhone, msg.text)}
                                                target="_blank"
                                                rel="noreferrer"
                                                className={`inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs ${deliveryPhone ? 'border-cyan-300/30 bg-cyan-500/10 text-cyan-200 hover:bg-cyan-500/20' : 'pointer-events-none border-white/10 bg-white/5 text-slate-500'}`}
                                            >
                                                <SendIcon />
                                                {t('admin.pages.orders.show.messages.sendToDelivery')}
                                            </a>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {assignMenuOpen && (
                            <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
                                <form onSubmit={submitAssignDelivered} className="w-full max-w-2xl rounded-2xl border border-white/10 bg-slate-900 p-5 shadow-2xl">
                                    <div className="mb-4 flex items-start justify-between gap-3">
                                        <div>
                                            <h4 className="text-lg font-semibold text-white">{t('admin.pages.orders.show.deliveryMenuTitle')}</h4>
                                            <p className="text-xs text-slate-400">{t('admin.pages.orders.show.deliveryMenuSubtitle')}</p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => setAssignMenuOpen(false)}
                                            className="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-slate-300 hover:bg-white/10"
                                        >
                                            {t('common.cancel')}
                                        </button>
                                    </div>

                                    <input
                                        type="text"
                                        value={deliverySearch}
                                        onChange={(e) => setDeliverySearch(e.target.value)}
                                        placeholder={t('admin.pages.orders.show.searchDeliveryPlaceholder')}
                                        className="mb-3 w-full rounded-xl border border-white/15 bg-slate-800 px-3 py-2 text-sm text-white outline-none focus:border-cyan-300/40"
                                    />

                                    <div className="max-h-72 space-y-2 overflow-auto rounded-xl border border-white/10 bg-slate-950/60 p-2">
                                        {filteredDeliveryPersons.length === 0 ? (
                                            <p className="px-2 py-3 text-sm text-slate-400">{t('admin.pages.orders.show.noDeliveryMatch')}</p>
                                        ) : (
                                            filteredDeliveryPersons.map((d: any) => {
                                                const active = String(assignForm.data.delivery_id) === String(d.id);
                                                return (
                                                    <button
                                                        key={d.id}
                                                        type="button"
                                                        onClick={() => assignForm.setData('delivery_id', String(d.id))}
                                                        className={`w-full rounded-xl border px-3 py-2 text-left transition ${
                                                            active
                                                                ? 'border-cyan-300/40 bg-cyan-500/10'
                                                                : 'border-white/10 bg-white/[0.03] hover:bg-white/[0.06]'
                                                        }`}
                                                    >
                                                        <p className="text-sm font-medium text-slate-100">{d.first_name} {d.last_name}</p>
                                                        <p className="text-xs text-slate-400">{d.phone}</p>
                                                    </button>
                                                );
                                            })
                                        )}
                                    </div>

                                    <div className="mt-4 flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            onClick={() => setAssignMenuOpen(false)}
                                            className="rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 hover:bg-white/10"
                                        >
                                            {t('common.cancel')}
                                        </button>
                                        <button
                                            type="submit"
                                            disabled={!assignForm.data.delivery_id || assignForm.processing}
                                            className="rounded-lg bg-cyan-400 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-60"
                                        >
                                            {t('admin.pages.orders.show.assignAndDelivered')}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        )}

                        {rejectOpen && (
                            <div className="rounded-2xl border border-rose-300/30 bg-rose-500/10 p-4">
                                <h4 className="text-sm font-semibold text-rose-200">{t('admin.pages.orders.show.rejectModalTitle')}</h4>
                                <form onSubmit={submitReject} className="mt-2 space-y-2">
                                    <textarea
                                        value={rejectForm.data.reason}
                                        onChange={(e) => rejectForm.setData('reason', e.target.value)}
                                        rows={3}
                                        required
                                        placeholder={t('admin.pages.orders.show.reason')}
                                        className="w-full rounded-lg border border-rose-300/30 bg-slate-900/70 px-3 py-2 text-sm text-white"
                                    />
                                    <div className="flex gap-2">
                                        <button type="submit" className="rounded-lg bg-rose-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-rose-400">
                                            {t('admin.pages.orders.show.submitReject')}
                                        </button>
                                        <button type="button" onClick={() => setRejectOpen(false)} className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-sm text-slate-200">
                                            {t('common.cancel')}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        )}
                    </aside>
                </div>
            </div>
        </AdminLayout>
    );
}

function SendIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="currentColor" className="h-3.5 w-3.5">
            <path d="M3 11.5 20.5 3c.8-.4 1.6.4 1.2 1.2L13 21.7c-.4.8-1.5.7-1.8-.2L9.3 14.7 2.5 12.8c-.9-.2-1-.9-.2-1.3l11.4-5.1-9.6 7.2 5.6 1.6 1.7 5.5 7-14.2L3 11.5Z" />
        </svg>
    );
}
