import React, { useMemo, useState } from 'react';
import { Link, router, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

const statusClass = {
    pending: 'bg-amber-400/20 text-amber-200 ring-amber-300/30',
    confirmed: 'bg-sky-400/20 text-sky-200 ring-sky-300/30',
    shipped: 'bg-blue-400/20 text-blue-200 ring-blue-300/30',
    delivered: 'bg-indigo-400/20 text-indigo-200 ring-indigo-300/30',
    done: 'bg-emerald-400/20 text-emerald-200 ring-emerald-300/30',
    canceled: 'bg-rose-400/20 text-rose-200 ring-rose-300/30',
    returned: 'bg-slate-300/20 text-slate-200 ring-slate-300/30',
    rejected: 'bg-rose-400/20 text-rose-200 ring-rose-300/30',
};

const deliveryMethodLabel = {
    delivery: 'Delivery | ?????',
    shipping: 'Shipping | ???',
    hand_delivered: 'Hand Delivered | ????? ????',
};

const sourceLabel = {
    inside_city: 'Inside City | ???? ???????',
    outside_city: 'Outside City | ???? ???????',
};

function statusLabel(status) {
    if (!status) return '-';
    return status.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

export default function OrdersShow() {
    const { order, deliveryPersons = [], availableTransitions = [] } = usePage().props;
    const [rejectOpen, setRejectOpen] = useState(false);

    const rejectForm = useForm({ reason: '' });
    const assignForm = useForm({ status: 'delivered', delivery_id: '' });

    const orderTotal = Number(order.total_amount ?? 0).toFixed(2);

    const canDeliverWithAssign = order.delivery_method === 'delivery' && !order.delivery_id;

    const items = order.items ?? [];

    const submitTransition = (status) => {
        router.post(`/admin/orders/${order.id}/update-status`, { status });
    };

    const submitConfirm = () => router.post(`/admin/orders/${order.id}/confirm`);

    const submitReject = (e) => {
        e.preventDefault();
        rejectForm.post(`/admin/orders/${order.id}/reject`, {
            onSuccess: () => {
                setRejectOpen(false);
                rejectForm.reset();
            },
        });
    };

    const submitAssignDelivered = (e) => {
        e.preventDefault();
        assignForm.post(`/admin/orders/${order.id}/update-status`);
    };

    const transitionButtons = useMemo(() => {
        return availableTransitions.map((status) => {
            if (status === 'confirmed' && order.status === 'pending') {
                return (
                    <button key={status} type="button" onClick={submitConfirm} className="rounded-xl bg-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-300">
                        Confirm | ?????
                    </button>
                );
            }

            if ((status === 'canceled' || status === 'rejected') && order.status === 'pending') {
                return (
                    <button key={status} type="button" onClick={() => setRejectOpen(true)} className="rounded-xl border border-rose-300/30 bg-rose-500/10 px-4 py-2 text-sm font-medium text-rose-200 hover:bg-rose-500/20">
                        Reject | ???
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

    return (
        <AdminLayout title={`Order #${order.id}`}>
            <div className="mx-auto max-w-7xl space-y-6">
                <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <h1 className="text-2xl font-bold text-white">Order #{order.id} | ????? #{order.id}</h1>
                        <p className="text-sm text-slate-300">Details, items, and lifecycle actions.</p>
                    </div>
                    <Link href="/admin/orders" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-white/10">
                        Back to Orders | ???? ???????
                    </Link>
                </section>

                <div className="grid gap-6 xl:grid-cols-3">
                    <div className="space-y-6 xl:col-span-2">
                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h2 className="mb-4 text-lg font-semibold text-white">Order Information | ??????? ?????</h2>
                            <div className="grid gap-3 md:grid-cols-2 text-sm text-slate-200">
                                <p><span className="text-slate-400">Customer | ??????:</span> {order.client_id ? `${order.client?.first_name ?? ''} ${order.client?.last_name ?? ''}` : (order.client_name || '-')}</p>
                                <p><span className="text-slate-400">Phone | ??????:</span> {order.client?.phone || order.client_phone_number || '-'}</p>
                                <p><span className="text-slate-400">Date | ???????:</span> {order.order_date || order.created_at || '-'}</p>
                                <p><span className="text-slate-400">Source | ??????:</span> {sourceLabel[order.order_source] ?? order.order_source}</p>
                                <p><span className="text-slate-400">Delivery | ???????:</span> {deliveryMethodLabel[order.delivery_method] ?? order.delivery_method}</p>
                                <p><span className="text-slate-400">Status | ??????:</span> <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${statusClass[order.status] ?? 'bg-slate-300/20 text-slate-200 ring-slate-300/30'}`}>{statusLabel(order.status)}</span></p>
                                <p className="md:col-span-2"><span className="text-slate-400">Address | ???????:</span> {order.address_details || '-'}</p>
                                {order.shipping_notes && <p className="md:col-span-2"><span className="text-slate-400">Shipping Notes | ??????? ?????:</span> {order.shipping_notes}</p>}
                                {order.admin_order_client_notes && <p className="md:col-span-2"><span className="text-slate-400">Admin Notes | ??????? ???????:</span> {order.admin_order_client_notes}</p>}
                            </div>
                        </section>

                        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h2 className="mb-4 text-lg font-semibold text-white">Order Items | ????? ?????</h2>
                            <div className="overflow-x-auto">
                                <table className="min-w-full">
                                    <thead>
                                        <tr>
                                            {['Product', 'Unit Price', 'Qty', 'Subtotal', 'Batches'].map((h) => (
                                                <th key={h} className="pb-3 pr-4 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">{h}</th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {items.map((item) => {
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
                                                            : batches.map((b, i) => {
                                                                  const inv = b.inventory_batch || b.inventoryBatch;
                                                                  return (
                                                                      <div key={i} className="mb-1 rounded-lg border border-white/10 bg-white/[0.03] px-2 py-1 text-xs">
                                                                          Batch: {inv?.batch_number ?? '-'} | Qty: {b.quantity}
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
                            <h2 className="mb-4 text-lg font-semibold text-white">Status Actions | ??????? ??????</h2>
                            {availableTransitions.length === 0 ? (
                                <p className="text-sm text-slate-400">No more actions for this status.</p>
                            ) : (
                                <div className="flex flex-wrap gap-2">{transitionButtons}</div>
                            )}

                            {canDeliverWithAssign && availableTransitions.includes('delivered') && (
                                <form onSubmit={submitAssignDelivered} className="mt-4 rounded-xl border border-white/10 bg-slate-900/40 p-3">
                                    <p className="mb-2 text-sm text-slate-200">Assign delivery person before delivered | ???? ???? ????? ??? ???????</p>
                                    <div className="flex flex-wrap gap-2">
                                        <select
                                            value={assignForm.data.delivery_id}
                                            onChange={(e) => assignForm.setData('delivery_id', e.target.value)}
                                            className="min-w-64 rounded-lg border border-white/15 bg-slate-900/70 px-3 py-2 text-sm text-white"
                                            required
                                        >
                                            <option value="">Select Delivery</option>
                                            {deliveryPersons.map((d) => (
                                                <option key={d.id} value={d.id}>
                                                    {d.first_name} {d.last_name} - {d.phone}
                                                </option>
                                            ))}
                                        </select>
                                        <button type="submit" className="rounded-lg bg-cyan-400 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
                                            Assign + Delivered
                                        </button>
                                    </div>
                                </form>
                            )}
                        </section>
                    </div>

                    <aside className="space-y-4 xl:col-span-1">
                        <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                            <h3 className="text-lg font-semibold text-white">Summary | ??????</h3>
                            <p className="mt-2 flex items-center justify-between text-sm text-slate-300">
                                <span>Total Amount</span>
                                <strong className="text-white">${orderTotal}</strong>
                            </p>
                            {order.delivery_id && (
                                <div className="mt-3 rounded-lg border border-white/10 bg-white/[0.03] p-3 text-xs text-slate-300">
                                    Delivery: {order.delivery?.first_name} {order.delivery?.last_name}
                                    <br />
                                    Phone: {order.delivery?.phone}
                                </div>
                            )}
                        </div>

                        {rejectOpen && (
                            <div className="rounded-2xl border border-rose-300/30 bg-rose-500/10 p-4">
                                <h4 className="text-sm font-semibold text-rose-200">Reject Order | ??? ?????</h4>
                                <form onSubmit={submitReject} className="mt-2 space-y-2">
                                    <textarea
                                        value={rejectForm.data.reason}
                                        onChange={(e) => rejectForm.setData('reason', e.target.value)}
                                        rows={3}
                                        required
                                        placeholder="Reason | ?????"
                                        className="w-full rounded-lg border border-rose-300/30 bg-slate-900/70 px-3 py-2 text-sm text-white"
                                    />
                                    <div className="flex gap-2">
                                        <button type="submit" className="rounded-lg bg-rose-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-rose-400">
                                            Submit Reject
                                        </button>
                                        <button type="button" onClick={() => setRejectOpen(false)} className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-sm text-slate-200">
                                            Cancel
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
