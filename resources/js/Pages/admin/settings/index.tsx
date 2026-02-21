import React from 'react';
import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { useI18n } from '../../../i18n';

type Settings = {
    dollar_exchange_rate?: string | number;
    general_minimum_alert_quantity?: string | number;
    max_order_items?: string | number;
};

type PageProps = {
    settings?: Settings;
};

function Field({ id, label, hint, error, children }: { id: string; label: string; hint?: string; error?: string; children: React.ReactNode }) {
    return (
        <div className="space-y-1.5">
            <label htmlFor={id} className="block text-sm font-medium text-slate-200">
                {label}
            </label>
            {children}
            {hint ? <p className="text-xs text-slate-400">{hint}</p> : null}
            {error ? <p className="text-xs font-medium text-rose-300">{error}</p> : null}
        </div>
    );
}

export default function SettingsPage() {
    const { t } = useI18n();
    const { settings } = usePage<PageProps>().props;

    const { data, setData, post, processing, errors } = useForm({
        dollar_exchange_rate: settings?.dollar_exchange_rate ?? '',
        general_minimum_alert_quantity: settings?.general_minimum_alert_quantity ?? '',
        max_order_items: settings?.max_order_items ?? '',
    });

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post('/admin/settings');
    };

    return (
        <AdminLayout title={t('admin.pages.settings.index.title')}>
            <div className="mx-auto max-w-4xl space-y-6">
                <section className="rounded-3xl border border-white/10 bg-gradient-to-br from-slate-900/70 via-slate-900/40 to-cyan-950/30 p-6 md:p-8">
                    <p className="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-200">{t('admin.pages.settings.index.administration')}</p>
                    <h1 className="mt-2 text-3xl font-bold tracking-tight text-white">{t('admin.pages.settings.index.heading')}</h1>
                    <p className="mt-2 text-sm text-slate-300">
                        {t('admin.pages.settings.index.subtitle')}
                    </p>
                </section>

                <form onSubmit={submit} className="rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-xl md:p-6">
                    <div className="grid gap-5 md:grid-cols-2">
                        <Field
                            id="dollar_exchange_rate"
                            label={t('admin.pages.settings.index.fields.dollarExchangeRate.label')}
                            hint={t('admin.pages.settings.index.fields.dollarExchangeRate.hint')}
                            error={errors.dollar_exchange_rate}
                        >
                            <input
                                id="dollar_exchange_rate"
                                type="number"
                                step="0.0001"
                                min="0.0001"
                                value={data.dollar_exchange_rate}
                                onChange={(e) => setData('dollar_exchange_rate', e.target.value)}
                                className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-sm text-slate-100 outline-none ring-cyan-300/30 placeholder:text-slate-500 focus:border-cyan-300/50 focus:ring"
                                required
                            />
                        </Field>

                        <Field
                            id="general_minimum_alert_quantity"
                            label={t('admin.pages.settings.index.fields.generalMinimumAlertQuantity.label')}
                            hint={t('admin.pages.settings.index.fields.generalMinimumAlertQuantity.hint')}
                            error={errors.general_minimum_alert_quantity}
                        >
                            <input
                                id="general_minimum_alert_quantity"
                                type="number"
                                min="0"
                                value={data.general_minimum_alert_quantity}
                                onChange={(e) => setData('general_minimum_alert_quantity', e.target.value)}
                                className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-sm text-slate-100 outline-none ring-cyan-300/30 placeholder:text-slate-500 focus:border-cyan-300/50 focus:ring"
                                required
                            />
                        </Field>

                        <Field
                            id="max_order_items"
                            label={t('admin.pages.settings.index.fields.maxOrderItems.label')}
                            hint={t('admin.pages.settings.index.fields.maxOrderItems.hint')}
                            error={errors.max_order_items}
                        >
                            <input
                                id="max_order_items"
                                type="number"
                                min="1"
                                value={data.max_order_items}
                                onChange={(e) => setData('max_order_items', e.target.value)}
                                className="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-sm text-slate-100 outline-none ring-cyan-300/30 placeholder:text-slate-500 focus:border-cyan-300/50 focus:ring"
                                required
                            />
                        </Field>
                    </div>

                    <div className="mt-6 flex flex-wrap items-center gap-3 border-t border-white/10 pt-5">
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            {processing ? t('admin.pages.settings.index.saving') : t('admin.pages.settings.index.saveSettings')}
                        </button>
                        <p className="text-xs text-slate-400">{t('admin.pages.settings.index.applyHint')}</p>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
