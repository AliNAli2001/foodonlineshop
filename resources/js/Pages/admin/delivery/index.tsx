import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type DeliveryRow = {
  id: number;
  first_name: string;
  last_name: string;
  full_name?: string;
  phone: string;
  phone_plus?: string | null;
  status: 'available' | 'busy' | 'inactive' | string;
  orders_count?: number;
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

function statusClass(status: string) {
  if (status === 'available') return 'bg-emerald-400/20 text-emerald-200 ring-emerald-300/30';
  if (status === 'busy') return 'bg-amber-400/20 text-amber-200 ring-amber-300/30';
  return 'bg-slate-300/20 text-slate-200 ring-slate-300/30';
}

export default function DeliveryIndex() {
  const { t } = useI18n();
  const page = usePage<any>();
  const deliveryPersons = page.props.deliveryPersons;
  const rows: DeliveryRow[] = Array.isArray(deliveryPersons?.data)
    ? deliveryPersons.data
    : Array.isArray(deliveryPersons)
      ? deliveryPersons
      : [];

  const removeDelivery = (id: number) => {
    if (!window.confirm(t('admin.pages.delivery.index.deleteConfirm'))) return;
    router.delete(`/admin/delivery/${id}`);
  };

  const copy = async (value: string) => {
    try {
      await navigator.clipboard.writeText(value);
    } catch {
      // Ignore copy errors.
    }
  };

  return (
    <AdminLayout title={t('admin.pages.delivery.index.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.delivery.index.heading')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.delivery.index.subtitle')}</p>
          </div>
          <Link href="/admin/delivery/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
            {t('admin.pages.delivery.index.addDeliveryPerson')}
          </Link>
        </section>

        <section>
          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {rows.length === 0 ? (
              <article className="col-span-full rounded-2xl border border-white/10 bg-white/[0.04] p-8 text-center text-sm text-slate-400">
                {t('admin.pages.delivery.index.empty')}
              </article>
            ) : (
              rows.map((delivery) => (
                <article key={delivery.id} className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                  <div className="mb-4 flex items-start justify-between">
                    <div>
                      <h2 className="text-lg font-semibold text-white">{delivery.full_name || `${delivery.first_name} ${delivery.last_name}`}</h2>
                      <p className="text-xs text-slate-400">{t('admin.pages.delivery.index.id')}: #{delivery.id}</p>
                    </div>
                    <span className={`rounded-full px-2 py-0.5 text-xs ring-1 ${statusClass(delivery.status)}`}>
                      {delivery.status === 'available' ? t('admin.pages.delivery.status.available') : delivery.status === 'busy' ? t('admin.pages.delivery.status.busy') : t('admin.pages.delivery.status.inactive')}
                    </span>
                  </div>

                  <div className="space-y-2 text-sm text-slate-200">
                    <p className="flex items-center justify-between gap-2">
                      <span>{t('admin.pages.delivery.form.phone')}: {delivery.phone}</span>
                      <button onClick={() => copy(delivery.phone)} className="rounded-md border border-white/15 px-2 py-0.5 text-xs text-slate-300 hover:bg-white/10">{t('admin.pages.delivery.index.copy')}</button>
                    </p>
                    <p>{t('admin.pages.delivery.form.phonePlus')}: {delivery.phone_plus || '-'}</p>
                    <p>{t('admin.pages.delivery.index.orders')}: {delivery.orders_count ?? 0}</p>
                  </div>

                  <div className="mt-4 flex flex-wrap gap-2">
                    <a
                      href={`tel:${normalizePhone(delivery.phone)}`}
                      className="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300/30 bg-emerald-500/10 px-2.5 py-1 text-xs text-emerald-200 hover:bg-emerald-500/20"
                    >
                      <CallIcon />
                      {t('admin.pages.delivery.actions.call')}
                    </a>
                    <a
                      href={whatsappLink(delivery.phone_plus || delivery.phone, t('admin.pages.delivery.actions.defaultMessage'))}
                      target="_blank"
                      rel="noreferrer"
                      className="inline-flex items-center gap-1.5 rounded-lg border border-green-300/30 bg-green-500/10 px-2.5 py-1 text-xs text-green-200 hover:bg-green-500/20"
                    >
                      <WhatsappIcon />
                      {t('admin.pages.delivery.actions.whatsapp')}
                    </a>
                    <Link href={`/admin/delivery/${delivery.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('common.view')}</Link>
                    <Link href={`/admin/delivery/${delivery.id}/edit`} className="rounded-lg border border-amber-300/30 bg-amber-400/10 px-2.5 py-1 text-xs text-amber-200 hover:bg-amber-400/20">{t('common.edit')}</Link>
                    <button onClick={() => removeDelivery(delivery.id)} className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2.5 py-1 text-xs text-rose-200 hover:bg-rose-500/20">{t('common.delete')}</button>
                  </div>
                </article>
              ))
            )}
          </div>

          {deliveryPersons?.links && (
            <div className="mt-4 flex flex-wrap gap-2 border-t border-white/10 pt-4">
              {deliveryPersons.links.map((link: any, i: number) => (
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

function CallIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className="h-3.5 w-3.5">
      <path d="M5 4h4l2 5-2.5 2.5a14 14 0 0 0 4 4L15 13l5 2v4a2 2 0 0 1-2 2C10.3 21 3 13.7 3 6a2 2 0 0 1 2-2Z" />
    </svg>
  );
}

function WhatsappIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" className="h-3.5 w-3.5">
      <path d="M20.5 3.5A11 11 0 0 0 3.8 17.3L2 22l4.9-1.7A11 11 0 1 0 20.5 3.5Zm-8.5 17a8.9 8.9 0 0 1-4.5-1.2l-.3-.2-2.9 1 .9-2.8-.2-.3a8.9 8.9 0 1 1 7 3.5Zm4.9-6.7c-.3-.2-1.8-.9-2-.9-.3-.1-.4-.2-.6.2s-.7.9-.8 1c-.2.2-.3.2-.6.1-1.5-.7-2.5-1.2-3.5-2.8-.3-.4.3-.4.8-1.4.1-.2.1-.4 0-.5l-.9-2.1c-.2-.5-.4-.5-.6-.5h-.5c-.2 0-.5.1-.8.4s-1 1-1 2.3 1 2.6 1.2 2.7c.1.2 2 3.1 4.9 4.3 1.8.7 2.5.8 3.4.7.5-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4-.1-.1-.3-.2-.6-.4Z" />
    </svg>
  );
}


