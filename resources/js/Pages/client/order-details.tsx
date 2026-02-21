import React from 'react';
import GenericPage, { type GenericPageProps } from '../Shared/GenericPage';

export default function Page(props: GenericPageProps) {
    return <GenericPage viewName="client/order-details" propsData={props} />;
}



