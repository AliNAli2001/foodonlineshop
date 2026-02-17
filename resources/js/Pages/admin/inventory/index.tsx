import React, { useMemo, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type Product = {
  id: number;
  name_en?: string;
  name_ar?: string;
  stock_available_quantity?: number;
  stock?: { available_quantity?: number };
  minimum_alert_quantity?: number;
  inventoryBatches?: Array<{ available_quantity?: number; expiry_date?: string | null; status?: string | null; cost_price?: number }>;
};

type PageProps = {
  products: any;
  summary?: {
    totalInventoryCost?: number;
    lowStockProductsCount?: number;
    expiringInMonthItemsCount?: number;
    expiredItemsCount?: number;
    expiredItemsTotalCost?: number;
  };
  url: string;
};

function getRows(products: any): Product[] {
  if (Array.isArray(products?.data)) return products.data;
  if (Array.isArray(products)) return products;
  return [];
}

function stockAmount(product: Product): number {
  if (typeof product.stock_available_quantity === 'number') return product.stock_available_quantity;
  if (typeof product.stock?.available_quantity === 'number') return product.stock.available_quantity;
  if (Array.isArray(product.inventoryBatches)) {
    return product.inventoryBatches.reduce((sum, batch) => sum + Number(batch?.available_quantity ?? 0), 0);
  }
  return 0;
}

function isExpiredBatch(batch: { available_quantity?: number; expiry_date?: string | null; status?: string | null }) {
  const qty = Number(batch?.available_quantity ?? 0);
  if (qty <= 0) return false;
  if ((batch?.status || '').toLowerCase() === 'expired') return true;
  if (!batch?.expiry_date) return false;
  return new Date(batch.expiry_date) < new Date(new Date().toDateString());
}

function isExpiringInMonthBatch(batch: { available_quantity?: number; expiry_date?: string | null; status?: string | null }) {
  const qty = Number(batch?.available_quantity ?? 0);
  if (qty <= 0 || !batch?.expiry_date) return false;
  if ((batch?.status || '').toLowerCase() === 'expired') return false;
  const today = new Date(new Date().toDateString());
  const nextMonth = new Date(today);
  nextMonth.setMonth(nextMonth.getMonth() + 1);
  const expiry = new Date(batch.expiry_date);
  return expiry >= today && expiry <= nextMonth;
}

function isLowStockProduct(product: Product) {
  const stock = stockAmount(product);
  const min = Number(product.minimum_alert_quantity ?? 0);
  return min > 0 && stock < min;
}

export default function InventoryIndex() {
  const { t } = useI18n();
  const page = usePage<PageProps>();
  const products = page.props.products;
  const summary = page.props.summary ?? {};
  const url = page.url;
  const rows = getRows(products);
  const isLowStockRoute = (url || '').includes('/low-stock');
  const [summaryOpen, setSummaryOpen] = useState(false);
  const [issueFiltersOpen, setIssueFiltersOpen] = useState(false);
  const [issueFilter, setIssueFilter] = useState<'all' | 'low_stock' | 'expiring_month' | 'expired'>('all');

  const filteredRows = useMemo(() => {
    return rows.filter((product) => {
      if (issueFilter === 'low_stock') return isLowStockProduct(product);
      if (issueFilter === 'expiring_month') return (product.inventoryBatches || []).some((batch) => isExpiringInMonthBatch(batch));
      if (issueFilter === 'expired') return (product.inventoryBatches || []).some((batch) => isExpiredBatch(batch));
      return true;
    });
  }, [rows, issueFilter]);

  return (
    <AdminLayout title={t('admin.pages.inventory.index.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.inventory.index.heading')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.inventory.index.subtitle')}</p>
          </div>
          <div className="flex flex-wrap gap-2">
            <Link href="/admin/inventory/bulk/create" className="rounded-xl border border-cyan-300/30 bg-cyan-400/10 px-4 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-400/20">
              {t('admin.pages.inventory.index.bulk.title')}
            </Link>
            {isLowStockRoute ? (
              <Link href="/admin/inventory" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">{t('admin.pages.inventory.index.allProducts')}</Link>
            ) : (
              <Link href="/admin/inventory/low-stock" className="rounded-xl border border-amber-300/30 bg-amber-400/10 px-4 py-2 text-sm font-medium text-amber-200 hover:bg-amber-400/20">{t('admin.pages.inventory.index.lowStockProducts')}</Link>
            )}
            <Link href="/admin/dashboard" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('admin.nav.dashboard')}</Link>
          </div>
        </section>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
          <div className="flex flex-wrap items-center justify-between gap-2">
            <h2 className="text-sm font-semibold text-white">{t('admin.pages.inventory.index.cards.title')}</h2>
            <button
              type="button"
              onClick={() => setSummaryOpen((prev) => !prev)}
              className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
            >
              {summaryOpen ? t('admin.pages.inventory.index.filters.hideFilters') : t('admin.pages.inventory.index.filters.showFilters')}
            </button>
          </div>
          {summaryOpen && (
            <div className="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                <p className="text-xs uppercase tracking-[0.12em] text-slate-400">{t('admin.pages.inventory.index.cards.totalInventoryCost')}</p>
                <p className="mt-2 text-2xl font-bold text-white">${Number(summary.totalInventoryCost ?? 0).toFixed(2)}</p>
              </article>
              <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                <p className="text-xs uppercase tracking-[0.12em] text-slate-400">{t('admin.pages.inventory.index.cards.lowStockProducts')}</p>
                <p className="mt-2 text-2xl font-bold text-white">{Number(summary.lowStockProductsCount ?? 0)}</p>
              </article>
              <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                <p className="text-xs uppercase tracking-[0.12em] text-slate-400">{t('admin.pages.inventory.index.cards.expiringInMonthItems')}</p>
                <p className="mt-2 text-2xl font-bold text-white">{Number(summary.expiringInMonthItemsCount ?? 0)}</p>
              </article>
              <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                <p className="text-xs uppercase tracking-[0.12em] text-slate-400">{t('admin.pages.inventory.index.cards.expiredItems')}</p>
                <p className="mt-2 text-2xl font-bold text-white">{Number(summary.expiredItemsCount ?? 0)}</p>
                <p className="mt-1 text-sm text-slate-300">
                  {t('admin.pages.inventory.index.cards.expiredItemsTotalCost')}: ${Number(summary.expiredItemsTotalCost ?? 0).toFixed(2)}
                </p>
              </article>
            </div>
          )}
        </section>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
          <div className="flex flex-wrap items-center justify-between gap-2">
            <h2 className="text-sm font-semibold text-white">{t('admin.pages.inventory.index.filters.title')}</h2>
            <button
              type="button"
              onClick={() => setIssueFiltersOpen((prev) => !prev)}
              className="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs text-slate-200 hover:bg-white/10"
            >
              {issueFiltersOpen ? t('admin.pages.inventory.index.filters.hideFilters') : t('admin.pages.inventory.index.filters.showFilters')}
            </button>
          </div>
          {issueFiltersOpen && (
            <div className="mt-3 flex flex-wrap gap-2">
              {[
                ['all', t('admin.pages.inventory.index.filters.all')],
                ['low_stock', t('admin.pages.inventory.index.filters.lowStock')],
                ['expiring_month', t('admin.pages.inventory.index.filters.expiringMonth')],
                ['expired', t('admin.pages.inventory.index.filters.expired')],
              ].map(([key, label]) => (
                <button
                  key={key}
                  type="button"
                  onClick={() => setIssueFilter(key as 'all' | 'low_stock' | 'expiring_month' | 'expired')}
                  className={`rounded-lg border px-3 py-1.5 text-xs ${
                    issueFilter === key
                      ? 'border-cyan-300/40 bg-cyan-400/10 text-cyan-200'
                      : 'border-white/15 bg-white/5 text-slate-200 hover:bg-white/10'
                  }`}
                >
                  {label}
                </button>
              ))}
            </div>
          )}
        </section>

        <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {filteredRows.length === 0 ? (
            <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-sm text-slate-400">{t('admin.pages.inventory.index.empty')}</div>
          ) : (
            filteredRows.map((product) => (
              <article key={product.id} className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                <h3 className="font-semibold text-white">{product.name_en || '-'}</h3>
                <p className="mt-0.5 text-sm text-slate-300">{product.name_ar || '-'}</p>
                <p className="mt-1 text-sm text-slate-300">{t('admin.pages.inventory.index.stock')}: {stockAmount(product)}</p>
                <Link href={`/admin/inventory/${product.id}/batches`} className="mt-3 inline-flex rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs text-cyan-200 hover:bg-cyan-400/20">{t('admin.pages.inventory.index.viewDetails')}</Link>
              </article>
            ))
          )}
        </section>

        {products?.links && (
          <div className="flex flex-wrap gap-2">
            {products.links.map((link: any, idx: number) => (
              <Link
                key={`${link.label}-${idx}`}
                href={link.url || '#'}
                preserveScroll
                className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-cyan-400 text-slate-950' : link.url ? 'bg-white/5 text-slate-200 hover:bg-white/10' : 'cursor-not-allowed bg-white/5 text-slate-500'}`}
                dangerouslySetInnerHTML={{ __html: link.label }}
              />
            ))}
          </div>
        )}
      </div>
    </AdminLayout>
  );
}



