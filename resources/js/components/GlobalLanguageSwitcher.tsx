import React from 'react';
import { useI18n } from '../i18n';

export default function GlobalLanguageSwitcher() {
  const { locale, setLocale, t } = useI18n();

  return (
    <div className="fixed bottom-4 left-4 z-[70] rounded-xl border border-white/20 bg-slate-900/85 p-1.5 backdrop-blur">
      <div className="flex items-center gap-1 text-xs">
        <button
          type="button"
          onClick={() => setLocale('en')}
          className={`rounded-lg px-2.5 py-1.5 transition ${locale === 'en' ? 'bg-cyan-400 text-slate-950' : 'text-slate-100 hover:bg-white/10'}`}
        >
          {t('common.english')}
        </button>
        <button
          type="button"
          onClick={() => setLocale('ar')}
          className={`rounded-lg px-2.5 py-1.5 transition ${locale === 'ar' ? 'bg-cyan-400 text-slate-950' : 'text-slate-100 hover:bg-white/10'}`}
        >
          {t('common.arabic')}
        </button>
      </div>
    </div>
  );
}
