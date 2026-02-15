import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function TagsIndex() {
  const page = usePage<any>();
  const tags = page.props.tags;
  const rows = Array.isArray(tags?.data) ? tags.data : Array.isArray(tags) ? tags : [];

  const removeTag = (id: number) => {
    if (!window.confirm('Delete this tag?')) return;
    router.delete(`/admin/tags/${id}`);
  };

  return (
    <AdminLayout title="Tags">
      <div className="mx-auto max-w-6xl space-y-6">
        <section className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <div><h1 className="text-2xl font-bold text-white">Tags</h1><p className="text-sm text-slate-300">Manage product tags.</p></div>
          <Link href="/admin/tags/create" className="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-300">+ Add Tag</Link>
        </section>

        <section className="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04]">
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-white/[0.03]"><tr>{['ID', 'English Name', 'Arabic Name', 'Actions'].map((h) => <th key={h} className="px-4 py-3 text-left text-xs uppercase tracking-[0.12em] text-slate-400">{h}</th>)}</tr></thead>
              <tbody>
                {rows.length === 0 ? <tr><td colSpan={4} className="px-4 py-8 text-center text-sm text-slate-400">No tags found.</td></tr> : rows.map((tag: any) => (
                  <tr key={tag.id} className="border-t border-white/10">
                    <td className="px-4 py-3 text-sm text-slate-200">{tag.id}</td>
                    <td className="px-4 py-3 text-sm text-slate-200">{tag.name_en}</td>
                    <td className="px-4 py-3 text-sm text-slate-200">{tag.name_ar}</td>
                    <td className="px-4 py-3"><div className="flex flex-wrap gap-2"><Link href={`/admin/tags/${tag.id}`} className="rounded-lg border border-cyan-300/30 bg-cyan-400/10 px-2.5 py-1 text-xs text-cyan-200 hover:bg-cyan-400/20">View</Link><Link href={`/admin/tags/${tag.id}/edit`} className="rounded-lg border border-amber-300/30 bg-amber-400/10 px-2.5 py-1 text-xs text-amber-200 hover:bg-amber-400/20">Edit</Link><button onClick={() => removeTag(tag.id)} className="rounded-lg border border-rose-300/30 bg-rose-500/10 px-2.5 py-1 text-xs text-rose-200 hover:bg-rose-500/20">Delete</button></div></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {tags?.links && <div className="flex flex-wrap gap-2 border-t border-white/10 p-4">{tags.links.map((link: any, i: number) => <Link key={`${link.label}-${i}`} href={link.url || '#'} preserveScroll className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-cyan-400 text-slate-950' : link.url ? 'bg-white/5 text-slate-200 hover:bg-white/10' : 'cursor-not-allowed bg-white/5 text-slate-500'}`} dangerouslySetInnerHTML={{ __html: link.label }} />)}</div>}
        </section>
      </div>
    </AdminLayout>
  );
}
