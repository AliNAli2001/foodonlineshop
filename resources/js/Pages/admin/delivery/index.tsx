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


