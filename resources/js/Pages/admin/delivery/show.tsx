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

function normalizePhone(value?: string | null): string {
  if (!value) return '';
  return value.replace(/[^\d+]/g, '');
}

function whatsappLink(phone?: string | null, message?: string): string {
  const normalized = normalizePhone(phone).replace('+', '');
  if (!normalized) return '#';
  const text = encodeURIComponent(message || '');
  return `https://wa.me/${normalized}${text ? `?text=${text}` : ''}`;
}

function formatDate(value?: string | null) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString();
}

function customerName(order: Order, manualOrderLabel: string) {
  if (order.client_id) {
    const full = `${order.client?.first_name || ''} ${order.client?.last_name || ''}`.trim();
    return full || '-';
  }
  return order.client_name || manualOrderLabel;
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
            <p className="text-sm text-slate-300">{t('admin.pages.delivery.show.subtitle')}</p>
          </div>
          <div className="flex gap-2">
            <a
              href={`tel:${normalizePhone(delivery.phone)}`}
              className="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-3 py-2 text-sm font-medium text-emerald-200 hover:bg-emerald-500/20"
            >
              <CallIcon />
              {t('admin.pages.delivery.actions.call')}
            </a>
            <a
              href={whatsappLink(delivery.phone_plus || delivery.phone, t('admin.pages.delivery.actions.defaultMessage'))}
              target="_blank"
              rel="noreferrer"
              className="inline-flex items-center gap-1.5 rounded-xl border border-green-300/30 bg-green-500/10 px-3 py-2 text-sm font-medium text-green-200 hover:bg-green-500/20"
            >
              <WhatsappIcon />
              {t('admin.pages.delivery.actions.whatsapp')}
            </a>
            <Link href={`/admin/delivery/${delivery.id}/edit`} className="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">{t('common.edit')}</Link>
            <Link href="/admin/delivery" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('common.back')}</Link>
          </div>
        </section>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 text-sm text-slate-200">
          <div className="grid gap-3 md:grid-cols-2">
            <Info label={t('admin.pages.delivery.form.phone')} value={delivery.phone} />
            <Info label={t('admin.pages.delivery.form.phonePlus')} value={delivery.phone_plus || '-'} />
            <Info label={t('common.email')} value={delivery.email || '-'} />
            <Info label={t('common.status')} value={delivery.status === 'available' ? t('admin.pages.delivery.status.available') : delivery.status === 'busy' ? t('admin.pages.delivery.status.busy') : t('admin.pages.delivery.status.inactive')} />
          </div>
          {delivery.info ? (
            <div className="mt-4 rounded-xl border border-white/10 bg-slate-900/40 p-4">
              <p className="mb-1 text-xs uppercase tracking-[0.12em] text-slate-400">{t('admin.pages.delivery.form.additionalInfo')}</p>
              <p>{delivery.info}</p>
            </div>
          ) : null}
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="border-b border-white/10 px-4 py-3">
            <h2 className="text-lg font-semibold text-white">{t('admin.pages.delivery.show.assignedOrders')}</h2>
          </div>

          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]">
                <tr>
                  {[
                    t('admin.pages.delivery.show.columns.order'),
                    t('admin.pages.delivery.show.columns.customer'),
                    t('admin.pages.delivery.show.columns.total'),
                    t('common.status'),
                    t('admin.pages.delivery.show.columns.date'),
                    t('common.actions'),
                  ].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="px-4 py-8 text-center text-sm text-slate-400">{t('admin.pages.delivery.show.emptyOrders')}</td>
                  </tr>
                ) : (
                  rows.map((order) => (
                    <tr key={order.id} className="border-t border-white/10">
                      <td className="px-4 py-3 text-sm text-slate-200">#{order.id}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{customerName(order, t('admin.pages.delivery.show.manualOrder'))}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">${Number(order.total_amount ?? 0).toFixed(2)}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{order.status || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{formatDate(order.order_date || order.created_at)}</td>
                      <td className="px-4 py-3">
                        <Link href={`/admin/orders/${order.id}`} className="rounded-lg  border border-cyan-300/40 bg-cyan-50 px-2.5 py-1 text-xs text-cyan-700 hover:bg-cyan-200 dark:border-cyan-400/30 dark:bg-cyan-400/10 dark:text-cyan-200 dark:hover:bg-cyan-400/20">{t('common.view')}</Link>
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

function CallIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className="h-4 w-4">
      <path d="M5 4h4l2 5-2.5 2.5a14 14 0 0 0 4 4L15 13l5 2v4a2 2 0 0 1-2 2C10.3 21 3 13.7 3 6a2 2 0 0 1 2-2Z" />
    </svg>
  );
}

function WhatsappIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" className="h-4 w-4">
      <path d="M20.5 3.5A11 11 0 0 0 3.8 17.3L2 22l4.9-1.7A11 11 0 1 0 20.5 3.5Zm-8.5 17a8.9 8.9 0 0 1-4.5-1.2l-.3-.2-2.9 1 .9-2.8-.2-.3a8.9 8.9 0 1 1 7 3.5Zm4.9-6.7c-.3-.2-1.8-.9-2-.9-.3-.1-.4-.2-.6.2s-.7.9-.8 1c-.2.2-.3.2-.6.1-1.5-.7-2.5-1.2-3.5-2.8-.3-.4.3-.4.8-1.4.1-.2.1-.4 0-.5l-.9-2.1c-.2-.5-.4-.5-.6-.5h-.5c-.2 0-.5.1-.8.4s-1 1-1 2.3 1 2.6 1.2 2.7c.1.2 2 3.1 4.9 4.3 1.8.7 2.5.8 3.4.7.5-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4-.1-.1-.3-.2-.6-.4Z" />
    </svg>
  );
}


