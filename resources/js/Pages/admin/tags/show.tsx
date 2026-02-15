import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function TagsShow() {
  const { tag, products } = usePage<any>().props;
  const rows = Array.isArray(products?.data) ? products.data : [];

  return (
    <AdminLayout title="Tag Details">
      <div className="mx-auto max-w-7xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div><h1 className="text-2xl font-bold text-white">{tag.name_en}</h1><p className="text-sm text-slate-300">Tag details and linked products.</p></div>
          <div className="flex gap-2"><Link href={`/admin/tags/${tag.id}/edit`} className="rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">Edit</Link><Link href="/admin/tags" className="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Back</Link></div>
        </section>

        <section className="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <h2 className="mb-3 text-lg font-semibold text-white">Tag Information</h2>
          <p className="text-sm text-slate-200"><span className="text-slate-400">Arabic Name: </span>{tag.name_ar}</p>
          <p className="text-sm text-slate-200"><span className="text-slate-400">English Name: </span>{tag.name_en}</p>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="flex items-center justify-between border-b border-white/10 px-4 py-3"><h2 className="text-lg font-semibold text-white">Products ({products?.total || rows.length})</h2><Link href="/admin/products/create" className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs text-cyan-200 hover:bg-cyan-400/20">+ Add Product</Link></div>
          <div className="overflow-x-auto"><table className="min-w-full"><thead className="bg-white/[0.03]"><tr>{['Name', 'Price', 'Stock', 'Featured', 'Actions'].map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead><tbody>{rows.length === 0 ? <tr><td colSpan={5} className="px-4 py-8 text-center text-sm text-slate-400">No products for this tag.</td></tr> : rows.map((p: any) => <tr key={p.id} className="border-t border-white/10"><td className="px-4 py-3 text-sm text-slate-200">{p.name_en || p.name_ar}</td><td className="px-4 py-3 text-sm text-slate-200">${Number(p.selling_price ?? p.price ?? 0).toFixed(2)}</td><td className="px-4 py-3 text-sm text-slate-200">{p.stock_available_quantity ?? p.inventory?.stock_quantity ?? 0}</td><td className="px-4 py-3 text-sm text-slate-200">{p.featured ? 'Yes' : 'No'}</td><td className="px-4 py-3"><div className="flex gap-2"><Link href={`/admin/products/${p.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">View</Link><Link href={`/admin/products/${p.id}/edit`} className="rounded-lg border border-amber-300/30 bg-amber-400/10 px-2.5 py-1 text-xs text-amber-200 hover:bg-amber-400/20">Edit</Link></div></td></tr>)}</tbody></table></div>
          {products?.links && <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">{products.links.map((link: any, i: number) => <Link key={`${link.label}-${i}`} href={link.url || '#'} preserveScroll className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-cyan-400 text-slate-950' : link.url ? 'bg-white/5 text-slate-200 hover:bg-white/10' : 'cursor-not-allowed bg-white/5 text-slate-500'}`} dangerouslySetInnerHTML={{ __html: link.label }} />)}</div>}
        </section>
      </div>
    </AdminLayout>
  );
}
