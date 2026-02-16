import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type DamagedGoods = {
  id: number;
  product_id: number;
  quantity: number;
  reason: string;
  created_at?: string;
  updated_at?: string;
  product?: { name_en?: string; name_ar?: string };
  inventory_batch?: any;
  inventoryBatch?: any;
  adjustment?: { id: number };
};

function formatDate(value?: string | null) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString();
}

export default function DamagedGoodsShow() {
  const { t } = useI18n();
  const { damagedGoods } = usePage<{ damagedGoods: DamagedGoods }>().props;
  const batch = damagedGoods?.inventory_batch || damagedGoods?.inventoryBatch;
  const adjustment = damagedGoods?.adjustment;
  const totalLoss = batch?.cost_price ? Number(batch.cost_price) * Number(damagedGoods.quantity || 0) : null;

  return (
    <AdminLayout title={t('admin.pages.damagedGoods.show.title')}>
      <div className="mx-auto max-w-5xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.damagedGoods.show.heading')} #{damagedGoods.id}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.damagedGoods.show.subtitle')}</p>
          </div>
          <Link href="/admin/damaged-goods" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
            {t('common.back')}
          </Link>
        </section>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 text-sm text-slate-200">
          <div className="grid gap-3 md:grid-cols-2">
            <Info label={t('admin.pages.damagedGoods.form.product')} value={damagedGoods.product?.name_en || damagedGoods.product?.name_ar || '-'} />
            <Info label={t('admin.pages.damagedGoods.form.quantity')} value={String(damagedGoods.quantity)} />
            <Info label={t('admin.pages.damagedGoods.form.reason')} value={damagedGoods.reason || '-'} />
            <Info label={t('admin.pages.damagedGoods.show.createdAt')} value={formatDate(damagedGoods.created_at)} />
            <Info label={t('admin.pages.damagedGoods.show.updatedAt')} value={formatDate(damagedGoods.updated_at)} />
            <Info label={t('admin.pages.damagedGoods.show.batch')} value={batch ? `${batch.batch_number || '-'} (ID: ${batch.id})` : t('admin.pages.damagedGoods.show.na')} />
            <Info label={t('admin.pages.damagedGoods.show.batchExpiry')} value={batch?.expiry_date || '-'} />
            <Info label={t('admin.pages.damagedGoods.show.totalLoss')} value={totalLoss != null ? String(totalLoss) : '-'} />
          </div>

          <div className="mt-4 flex flex-wrap gap-2">
            <Link href={`/admin/inventory/${damagedGoods.product_id}`} className="inline-flex rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs text-cyan-200 hover:bg-cyan-400/20">
              {t('admin.pages.damagedGoods.show.openProductInventory')}
            </Link>
            {adjustment ? (
              <Link href={`/admin/adjustments/${adjustment.id}`} className="inline-flex rounded-lg border border-indigo-300/30 bg-indigo-500/10 px-3 py-1.5 text-xs text-indigo-200 hover:bg-indigo-500/20">
                {t('admin.pages.damagedGoods.show.openLinkedAdjustment')}
              </Link>
            ) : null}
          </div>
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


