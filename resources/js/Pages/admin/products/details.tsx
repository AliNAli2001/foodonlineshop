import React from 'react';
import GenericPage from '../../Shared/GenericPage';
import { useI18n } from '../../../i18n';

export default function Page(props) {
  const { t } = useI18n();
    return <GenericPage viewName={t('admin.pages.products.details.title')} propsData={props} />;
}



