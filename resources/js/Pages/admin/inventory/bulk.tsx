import React, { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type BulkItem = {
  product_id: string;
  search: string;
  product_label: string;
  batch_number: string;
  available_quantity: string;
  expiry_date: string;
  cost_price: string;
  reason: string;
};

type SearchResult = { id: number; name_en?: string; name_ar?: string; label?: string };

type PageProps = {
  errors?: Record<string, string>;
};

const emptyItem = (batchNumber = ''): BulkItem => ({
  product_id: '',
  search: '',
  product_label: '',
  batch_number: batchNumber,
  available_quantity: '',
  expiry_date: '',
  cost_price: '',
  reason: '',
});

export default function InventoryBulkCreate() {
  const { t } = useI18n();
  const page = usePage<PageProps>();
  const errors = page.props.errors || {};

  const [rowsCount, setRowsCount] = useState(1);
  const [defaultBatchNumber, setDefaultBatchNumber] = useState('');
  const [processing, setProcessing] = useState(false);
  const [items, setItems] = useState<BulkItem[]>([emptyItem()]);
  const [searchResults, setSearchResults] = useState<Record<number, SearchResult[]>>({});

  const setItem = (index: number, key: keyof BulkItem, value: string) => {
    setItems((prev) => prev.map((item, i) => (i === index ? { ...item, [key]: value } : item)));
  };

  const applyRowsCount = () => {
    const count = Math.max(1, Math.min(50, Number(rowsCount) || 1));
    setItems((prev) => {
      const next = [...prev];
      while (next.length < count) {
        next.push(emptyItem(defaultBatchNumber));
      }
      return next.slice(0, count);
    });
  };

  const searchProducts = async (index: number, query: string) => {
    setItem(index, 'search', query);
    if (query.trim().length < 1) {
      setSearchResults((prev) => ({ ...prev, [index]: [] }));
      return;
    }
    try {
      const response = await fetch(`/admin/inventory/autocomplete/products?q=${encodeURIComponent(query)}`, { headers: { Accept: 'application/json' } });
      const payload = await response.json();
      setSearchResults((prev) => ({ ...prev, [index]: payload?.results || [] }));
    } catch {
      setSearchResults((prev) => ({ ...prev, [index]: [] }));
    }
  };

  const selectProduct = (index: number, product: SearchResult) => {
    setItems((prev) =>
      prev.map((item, i) =>
        i === index
          ? {
              ...item,
              product_id: String(product.id),
              product_label: product.label || `${product.name_en || ''} / ${product.name_ar || ''}`.trim(),
              search: '',
            }
          : item,
      ),
    );
    setSearchResults((prev) => ({ ...prev, [index]: [] }));
  };

  const submitBulkCreate = (e: React.FormEvent) => {
    e.preventDefault();
    const payload = {
      items: items.map((item) => ({
        product_id: item.product_id,
        batch_number: item.batch_number,
        available_quantity: item.available_quantity,
        expiry_date: item.expiry_date || null,
        cost_price: item.cost_price,
        reason: item.reason || null,
      })),
    };

    setProcessing(true);
    router.post('/admin/inventory/bulk', payload, {
      preserveScroll: true,
      onFinish: () => setProcessing(false),
    });
  };

  return (
    <AdminLayout title={t('admin.pages.inventory.index.bulk.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.inventory.index.bulk.title')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.inventory.index.subtitle')}</p>
          </div>
          <div className="flex flex-wrap gap-2">
            <Link href="/admin/inventory" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
              {t('admin.pages.inventory.index.allProducts')}
            </Link>
          </div>
        </section>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
          <form onSubmit={submitBulkCreate} className="space-y-3">
            <div className="flex flex-wrap items-end gap-2">
              <label className="text-xs text-slate-300">
                <span className="mb-1 block">{t('admin.pages.inventory.index.bulk.defaultBatchNumber', 'Default Batch Number')}</span>
                <input
                  value={defaultBatchNumber}
                  onChange={(e) => setDefaultBatchNumber(e.target.value)}
                  className="w-56 rounded-lg border border-white/15 bg-slate-900/70 px-2.5 py-1.5 text-sm text-white"
                />
              </label>
              <label className="text-xs text-slate-300">
                <span className="mb-1 block">{t('admin.pages.inventory.index.bulk.rowsCount')}</span>
                <input
                  type="number"
                  min={1}
                  max={50}
                  value={rowsCount}
                  onChange={(e) => setRowsCount(Number(e.target.value))}
                  className="w-32 rounded-lg border border-white/15 bg-slate-900/70 px-2.5 py-1.5 text-sm text-white"
                />
              </label>
              <button type="button" onClick={applyRowsCount} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs text-cyan-200 hover:bg-cyan-400/20">
                {t('admin.pages.inventory.index.bulk.applyRows')}
              </button>
            </div>

            {items.map((item, index) => (
              <div key={index} className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                <p className="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                  {t('admin.pages.inventory.index.bulk.item')} #{index + 1}
                </p>
                <div className="grid gap-2 md:grid-cols-2 xl:grid-cols-6">
                  <div className="xl:col-span-2">
                    <label className="mb-1 block text-xs text-slate-300">{t('admin.pages.inventory.index.bulk.productSearch')}</label>
                    <input
                      value={item.search}
                      onChange={(e) => searchProducts(index, e.target.value)}
                      placeholder={t('admin.pages.inventory.index.bulk.productSearchPlaceholder')}
                      className="w-full rounded-lg border border-white/15 bg-slate-900/70 px-2.5 py-1.5 text-sm text-white"
                    />
                    {item.product_label && <p className="mt-1 text-xs text-cyan-200">{item.product_label}</p>}
                    {(searchResults[index] || []).length > 0 && (
                      <div className="mt-1 max-h-36 overflow-auto rounded-lg border border-white/10 bg-slate-900/95">
                        {(searchResults[index] || []).map((result) => (
                          <button
                            key={result.id}
                            type="button"
                            onClick={() => selectProduct(index, result)}
                            className="block w-full border-b border-white/10 px-2.5 py-1.5 text-left text-xs text-slate-200 hover:bg-white/10"
                          >
                            {result.label || `${result.name_en || ''} / ${result.name_ar || ''}`.trim()}
                          </button>
                        ))}
                      </div>
                    )}
                  </div>

                  <div>
                    <label className="mb-1 block text-xs text-slate-300">{t('admin.pages.inventory.index.bulk.batchNumber')}</label>
                    <input value={item.batch_number} onChange={(e) => setItem(index, 'batch_number', e.target.value)} className="w-full rounded-lg border border-white/15 bg-slate-900/70 px-2.5 py-1.5 text-sm text-white" />
                  </div>
                  <div>
                    <label className="mb-1 block text-xs text-slate-300">{t('admin.pages.inventory.index.bulk.quantity')}</label>
                    <input type="number" min={1} value={item.available_quantity} onChange={(e) => setItem(index, 'available_quantity', e.target.value)} className="w-full rounded-lg border border-white/15 bg-slate-900/70 px-2.5 py-1.5 text-sm text-white" />
                  </div>
                  <div>
                    <label className="mb-1 block text-xs text-slate-300">{t('admin.pages.inventory.index.bulk.expiryDate')}</label>
                    <input type="date" value={item.expiry_date} onChange={(e) => setItem(index, 'expiry_date', e.target.value)} className="w-full rounded-lg border border-white/15 bg-slate-900/70 px-2.5 py-1.5 text-sm text-white" />
                  </div>
                  <div>
                    <label className="mb-1 block text-xs text-slate-300">{t('admin.pages.inventory.index.bulk.costPrice')}</label>
                    <input type="number" min={0.001} step="0.001" value={item.cost_price} onChange={(e) => setItem(index, 'cost_price', e.target.value)} className="w-full rounded-lg border border-white/15 bg-slate-900/70 px-2.5 py-1.5 text-sm text-white" />
                  </div>
                </div>
              </div>
            ))}

            {errors.items && <p className="text-xs text-rose-300">{errors.items}</p>}
            <button
              type="submit"
              disabled={processing}
              className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300 disabled:opacity-60"
            >
              {processing ? t('admin.pages.inventory.form.saving') : t('admin.pages.inventory.index.bulk.submit')}
            </button>
          </form>
        </section>
      </div>
    </AdminLayout>
  );
}

