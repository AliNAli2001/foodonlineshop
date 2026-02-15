import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

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
  const { damagedGoods } = usePage<{ damagedGoods: DamagedGoods }>().props;
  const batch = damagedGoods?.inventory_batch || damagedGoods?.inventoryBatch;
  const adjustment = damagedGoods?.adjustment;
  const totalLoss = batch?.cost_price ? Number(batch.cost_price) * Number(damagedGoods.quantity || 0) : null;

  return (
    <AdminLayout title="Damaged Goods Details">
      <div className="mx-auto max-w-5xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">Damaged Goods #{damagedGoods.id}</h1>
            <p className="text-sm text-slate-300">View item, batch, and linked loss details.</p>
          </div>
          <Link href="/admin/damaged-goods" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
            Back
          </Link>
        </section>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 text-sm text-slate-200">
          <div className="grid gap-3 md:grid-cols-2">
            <Info label="Product" value={damagedGoods.product?.name_en || damagedGoods.product?.name_ar || '-'} />
            <Info label="Quantity" value={String(damagedGoods.quantity)} />
            <Info label="Reason" value={damagedGoods.reason || '-'} />
            <Info label="Created At" value={formatDate(damagedGoods.created_at)} />
            <Info label="Updated At" value={formatDate(damagedGoods.updated_at)} />
            <Info label="Batch" value={batch ? `${batch.batch_number || '-'} (ID: ${batch.id})` : 'N/A'} />
            <Info label="Batch Expiry" value={batch?.expiry_date || '-'} />
            <Info label="Total Loss" value={totalLoss != null ? String(totalLoss) : '-'} />
          </div>

          <div className="mt-4 flex flex-wrap gap-2">
            <Link href={`/admin/inventory/${damagedGoods.product_id}`} className="inline-flex rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs text-cyan-200 hover:bg-cyan-400/20">
              Open Product Inventory
            </Link>
            {adjustment ? (
              <Link href={`/admin/adjustments/${adjustment.id}`} className="inline-flex rounded-lg border border-indigo-300/30 bg-indigo-500/10 px-3 py-1.5 text-xs text-indigo-200 hover:bg-indigo-500/20">
                Open Linked Adjustment
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
