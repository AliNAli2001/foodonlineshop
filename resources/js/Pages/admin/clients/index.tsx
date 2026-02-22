import React, { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

export default function ClientsIndex() {
  const { t } = useI18n();
  const page = usePage<any>();
  const clients = page.props.clients;
  const filters = page.props.filters ?? {};
  const rows = Array.isArray(clients?.data) ? clients.data : [];
  const [search, setSearch] = useState<string>(filters.search ?? '');

  const submitSearch = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    router.get('/admin/clients', { search }, { preserveState: true, preserveScroll: true });
  };

  const clearSearch = () => {
    setSearch('');
    router.get('/admin/clients', {}, { preserveState: true, preserveScroll: true });
  };

  const removeClient = (id: number) => {
    if (!window.confirm(t('admin.pages.clients.index.deleteConfirm'))) return;
    router.delete(`/admin/clients/${id}`);
  };

  const toggleSuspend = (client: any) => {
    if (client.suspended_at) {
      router.patch(`/admin/clients/${client.id}/activate`);
      return;
    }

    if (!window.confirm(t('admin.pages.clients.index.suspendConfirm'))) return;
    router.patch(`/admin/clients/${client.id}/suspend`);
  };

  return (
    <AdminLayout title={t('admin.pages.clients.index.title')}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div className="mb-4">
            <h1 className="text-2xl font-bold text-white">{t('admin.pages.clients.index.heading')}</h1>
            <p className="text-sm text-slate-300">{t('admin.pages.clients.index.subtitle')}</p>
          </div>

          <form onSubmit={submitSearch} className="flex flex-wrap items-center gap-2">
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder={t('admin.pages.clients.index.searchPlaceholder')}
              className="min-w-[260px] flex-1 rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-400"
            />
            <button type="submit" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">
              {t('common.search')}
            </button>
            <button type="button" onClick={clearSearch} className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
              {t('admin.pages.clients.index.clear')}
            </button>
          </form>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]">
                <tr>
                  {[
                    t('admin.pages.clients.index.columns.id'),
                    t('admin.pages.clients.index.columns.name'),
                    t('admin.pages.clients.index.columns.email'),
                    t('admin.pages.clients.index.columns.phone'),
                    t('admin.pages.clients.index.columns.orders'),
                    t('common.status'),
                    t('common.actions'),
                  ].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">
                      {h}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="px-4 py-8 text-center text-sm text-slate-400">
                      {t('admin.pages.clients.index.empty')}
                    </td>
                  </tr>
                ) : (
                  rows.map((client: any) => (
                    <tr key={client.id} className="border-t border-white/10">
                      <td className="px-4 py-3 text-sm text-slate-200">{client.id}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{client.first_name} {client.last_name}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{client.email || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{client.phone || '-'}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{client.orders_count ?? 0}</td>
                      <td className="px-4 py-3 text-sm">
                        {client.suspended_at ? (
                          <span className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2 py-1 text-xs text-rose-200">{t('admin.pages.clients.index.status.suspended')}</span>
                        ) : (
                          <span className="rounded-lg border border-emerald-300/30 bg-emerald-500/10 px-2 py-1 text-xs text-emerald-200">{t('admin.pages.clients.index.status.active')}</span>
                        )}
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex flex-wrap gap-2">
                          <Link href={`/admin/clients/${client.id}`} className="rounded-lg  border border-cyan-300/40 bg-cyan-50 px-2.5 py-1 text-xs text-cyan-700 hover:bg-cyan-200 dark:border-cyan-400/30 dark:bg-cyan-400/10 dark:text-cyan-200 dark:hover:bg-cyan-400/20">
                            {t('common.view')}
                          </Link>
                          <button
                            onClick={() => toggleSuspend(client)}
                            className={`rounded-lg px-2.5 py-1 text-xs ${
                              client.suspended_at
                                ? 'border border-emerald-300/30 bg-emerald-500/10 text-emerald-200 hover:bg-emerald-500/20'
                                : 'border border-amber-300/30 bg-amber-500/10 text-amber-200 hover:bg-amber-500/20'
                            }`}
                          >
                            {client.suspended_at ? t('admin.pages.clients.index.actions.activate') : t('admin.pages.clients.index.actions.suspend')}
                          </button>
                          <button onClick={() => removeClient(client.id)} className="rounded-lg border border-rose-300/40 bg-rose-50 px-2.5 py-1 text-xs text-rose-700 hover:bg-rose-100 dark:border-rose-400/30 dark:bg-rose-500/10 dark:text-rose-200 dark:hover:bg-rose-500/20">
                            {t('common.delete')}
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {clients?.links && (
            <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">
              {clients.links.map((link: any, i: number) => (
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
