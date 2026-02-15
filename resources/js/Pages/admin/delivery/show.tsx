import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type Delivery = {
  id: number;
  first_name: string;
  last_name: string;
  full_name?: string;
  phone: string;
  phone_plus?: string | null;
  email?: string | null;
  status: string;
  info?: string | null;
};

type Order = {
  id: number;
  client_id?: number | null;
  client_name?: string | null;
  total_amount?: number | string | null;
  status?: string | null;
  order_date?: string | null;
  created_at?: string | null;
  client?: { first_name?: string; last_name?: string };
};

function formatDate(value?: string | null) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString();
}

function customerName(order: Order) {
  if (order.client_id) {
    const full = `${order.client?.first_name || ''} ${order.client?.last_name || ''}`.trim();
    return full || '-';
  }
  return order.client_name || 'Manual Order';
}

export default function DeliveryShow() {
  const { t } = useI18n();
  const { delivery, orders } = usePage<{ delivery: Delivery; orders: any }>().props;
  const rows: Order[] = Array.isArray(orders?.data) ? orders.data : [];

  return (
    <AdminLayout title={t('admin.pages.delivery.show.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{delivery.full_name || `${delivery.first_name} ${delivery.last_name}`}</h1>
            <p className="text-sm text-slate-300">Delivery profile and assigned orders.</p>
          </div>
          <div className="flex gap-2">
            <Link href={`/admin/delivery/${delivery.id}/edit`} className="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">{t('common.edit')}</Link>
            <Link href="/admin/delivery" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('common.back')}</Link>
          </div>
        </section>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 text-sm text-slate-200">
          <div className="grid gap-3 md:grid-cols-2">
            <Info label="Phone" value={delivery.phone} />
            <Info label="Phone Plus" value={delivery.phone_plus || '-'} />
            <Info label="Email" value={delivery.email || '-'} />
            <Info label="Status" value={delivery.status} />
          </div>
          {delivery.info ? (
            <div className="mt-4 rounded-xl border border-white/10 bg-slate-900/40 p-4">
              <p className="mb-1 text-xs uppercase tracking-[0.12em] text-slate-400">Additional Info</p>
              <p>{delivery.info}</p>
            </div>
          ) : null}
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="border-b border-white/10 px-4 py-3">
            <h2 className="text-lg font-semibold text-white">Assigned Orders</h2>
          </div>

          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]">
                <tr>
                  {['Order', 'Customer', 'Total', 'Status', 'Date', 'Action'].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="px-4 py-8 text-center text-sm text-slate-400">No assigned orders.</td>
                  </tr>
                ) : (
                  rows.map((order) => (
                    <tr key={order.id} className="border-t border-white/10">
                      <td className="px-4 py-3 text-sm text-slate-200">#{order.id}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{customerName(order)}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">${Number(order.total_amount ?? 0).toFixed(2)}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{order.status || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{formatDate(order.order_date || order.created_at)}</td>
                      <td className="px-4 py-3">
                        <Link href={`/admin/orders/${order.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('common.view')}</Link>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {orders?.links && (
            <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">
              {orders.links.map((link: any, i: number) => (
                <Link
                  key={`${link.label}-${i}`}
                  href={link.url || '#'}
                  preserveScroll
                  className={`rounded-lg px-3 py-1.5 text-sm ${
                    link.active
                      ? 'bg-cyan-400 text-slate-950'
                      : link.url
                        ? 'bg-white/5 text-slate-200 hover:bg-white/10'
                        : 'cursor-not-allowed bg-white/5 text-slate-500'
                  }`}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              ))}
            </div>
          )}
        </section>
      </div>
    </AdminLayout>
  );
}

function Info({ label, value }: { label: string; value: string }) {
  return (
    <p>
      <span className="text-slate-400">{label}: </span>
      {value}
    </p>
  );
}


