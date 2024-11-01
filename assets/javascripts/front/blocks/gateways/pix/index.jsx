import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';
import { PersonType } from '../../components/person-type';

import "./styles.scss";

const name = 'pagaleve-pix';
const settings = getSetting(`${name}_data`, {});
const defaultLabel = 'Pix 4x sem juros';
const label = decodeEntities(settings.title) || defaultLabel;
const enviroment = decodeEntities(settings.enviroment) || false;

const Content = () => {
    return (
        <>
            {decodeEntities(settings.description || '')}
            <PersonType/>
            <div className="wc-pagaleve-container">
                <hr/>
				<div className="clear"></div>
                <div className="wc-pagaleve-env" style={{
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                }}>{enviroment ? 'MODO DE TESTE HABILITADO!' : ''}</div>
				<img id="wc-pagaleve-cash-background" src="https://assets.pagaleve.com.br/checkout/split.png"/>
			</div>
        </>
    );

};

const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components;
    return <PaymentMethodLabel text={ label } />;
};


const Pix = {
    name: name,
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: () => true,
    ariaLabel: label,
	supports: {
		features: settings.supports,
	}
};

registerPaymentMethod(Pix);
