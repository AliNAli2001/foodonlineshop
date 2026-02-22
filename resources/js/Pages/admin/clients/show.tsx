import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function ClientsShow() {
  const { client, orders } = usePage<any>().props;
  const rows = Array.isArray(orders?.data) ? orders.data : [];

  const resetPassword = () => {
    if (!window.confirm('Reset this client password? A new random password will be generated.')) return;
    router.post(`/admin/clients/${client.id}/reset-password`);
  };

  const suspendClient = () => {
    if (!window.confirm('Suspend this account? Active sessions will be revoked.')) return;
    router.patch(`/admin/clients/${client.id}/suspend`);
  };

  const activateClient = () => {
    router.patch(`/admin/clients/${client.id}/activate`);
  };

  const removeClient = () => {
    if (!window.confirm('Delete this client account permanently?')) return;
    router.delete(`/admin/clients/${client.id}`);
  };

  return (
    <AdminLayout title={`Client #${client.id}`}>
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div>
            <h1 className="text-2xl font-bold text-white">{client.first_name} {client.last_name}</h1>
            <p className="text-sm text-slate-300">Client account details and actions.</p>
          </div>
          <div className="flex flex-wrap gap-2">
            <button onClick={resetPassword} className="rounded-xl border border-cyan-300/40 bg-cyan-50 px-4 py-2 text-sm text-cyan-700 hover:bg-cyan-100 dark:border-cyan-400/30 dark:bg-cyan-400/10 dark:text-cyan-200 dark:hover:bg-cyan-400/20">
              Reset Password
            </button>
            {client.suspended_at ? (
              <button onClick={activateClient} className="rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-2 text-sm text-emerald-200 hover:bg-emerald-500/20">
                Activate
              </button>
            ) : (
              <button onClick={suspendClient} className="rounded-xl border border-amber-300/30 bg-amber-500/10 px-4 py-2 text-sm text-amber-200 hover:bg-amber-500/20">
                Suspend
              </button>
            )}
            <button onClick={removeClient} className="rounded-xl border border-rose-300/30 bg-rose-500/10 px-4 py-2 text-sm text-rose-200 hover:bg-rose-500/20">
              Delete
            </button>
            <Link href="/admin/clients" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
              Back
            </Link>
          </div>
        </section>

        <section className="grid gap-6 lg:grid-cols-2">
          <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
            <h2 className="mb-3 text-lg font-semibold text-white">Profile</h2>
            <div className="space-y-2 text-sm text-slate-200">
              <p><span className="text-slate-400">ID:</span> {client.id}</p>
              <p><span className="text-slate-400">Email:</span> {client.email || '-'}</p>
              <p><span className="text-slate-400">Phone:</span> {client.phone || '-'}</p>
              <p><span className="text-slate-400">Language:</span> {client.language_preference || '-'}</p>
              <p><span className="text-slate-400">Email Verified:</span> {client.email_verified ? 'Yes' : 'No'}</p>
              <p><span className="text-slate-400">Phone Verified:</span> {client.phone_verified ? 'Yes' : 'No'}</p>
              <p><span className="text-slate-400">Promo Consent:</span> {client.promo_consent ? 'Yes' : 'No'}</p>
              <p><span className="text-slate-400">Status:</span> {client.suspended_at ? 'Suspended' : 'Active'}</p>
              <p><span className="text-slate-400">Registered:</span> {client.created_at ? new Date(client.created_at).toLocaleString() : '-'}</p>
            </div>
          </article>

          <article className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
            <h2 className="mb-3 text-lg font-semibold text-white">Address</h2>
            <p className="text-sm text-slate-200 whitespace-pre-wrap">{client.address_details || 'No saved address details.'}</p>
          </article>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="border-b border-white/10 px-4 py-3">
            <h2 className="text-lg font-semibold text-white">Recent Orders ({orders?.total || rows.length})</h2>
          </div>
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]">
                <tr>
                  {['Order', 'Status', 'Total', 'Date', 'Actions'].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">
                      {h}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-4 py-8 text-center text-sm text-slate-400">
                      No orders for this client.
                    </td>
                  </tr>
                ) : (
                  rows.map((order: any) => (
                    <tr key={order.id} className="border-t border-white/10">
                      <td className="px-4 py-3 text-sm text-slate-200">#{order.id}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">{order.status}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">${Number(order.total_amount ?? 0).toFixed(2)}</td>
                      <td className="px-4 py-3 text-sm text-slate-200">
                        {order.order_date ? new Date(order.order_date).toLocaleString() : (order.created_at ? new Date(order.created_at).toLocaleString() : '-')}
                      </td>
                      <td className="px-4 py-3">
                        <Link href={`/admin/orders/${order.id}`} className="rounded-lg  border border-cyan-300/40 bg-cyan-50 px-2.5 py-1 text-xs text-cyan-700 hover:bg-cyan-200 dark:border-cyan-400/30 dark:bg-cyan-400/10 dark:text-cyan-200 dark:hover:bg-cyan-400/20">
                          View
                        </Link>
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
