import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type Product = {
  id: number;
  name_en?: string;
  name_ar?: string;
  stock_available_quantity?: number;
  stock?: { available_quantity?: number };
  inventoryBatches?: Array<{ available_quantity?: number }>;
};

type PageProps = {
  products: any;
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

export default function InventoryIndex() {
  const { t } = useI18n();
  const page = usePage<PageProps>();
  const products = page.props.products;
  const url = page.url;
  const rows = getRows(products);
  const isLowStockRoute = (url || '').includes('/low-stock');

  return (
    <AdminLayout title={t('admin.pages.inventory.index.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.inventory.index.heading')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.inventory.index.subtitle')}</p>
          </div>
          <div className="flex flex-wrap gap-2">
            {isLowStockRoute ? (
              <Link href="/admin/inventory" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">{t('admin.pages.inventory.index.allProducts')}</Link>
            ) : (
              <Link href="/admin/inventory/low-stock" className="rounded-xl border border-amber-300/30 bg-amber-400/10 px-4 py-2 text-sm font-medium text-amber-200 hover:bg-amber-400/20">{t('admin.pages.inventory.index.lowStockProducts')}</Link>
            )}
            <Link href="/admin/dashboard" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">{t('admin.nav.dashboard')}</Link>
          </div>
        </section>

        <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {rows.length === 0 ? (
            <div className="rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-sm text-slate-400">{t('admin.pages.inventory.index.empty')}</div>
          ) : (
            rows.map((product) => (
              <article key={product.id} className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                <h3 className="font-semibold text-white">{product.name_en || product.name_ar || `${t('admin.pages.inventory.index.product')} #${product.id}`}</h3>
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



