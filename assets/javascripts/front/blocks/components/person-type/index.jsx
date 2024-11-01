import { __ } from '@wordpress/i18n';
import { useState } from "react";
import { IMaskInput } from 'react-imask';

export function PersonType() {
  const [showCpf, setShowCpf] = useState(true);
  const [showCnpj, setShowCnpj] = useState(false);
  const [cnpj, setCnpj] = useState('');
  const [cpf, setCpf] = useState('');
  const [personDocument, setPersonDocument] = useState('');
  const [oldDocument, setOldnDocument] = useState('');
  const [personType, setPersontype] = useState('1');

  const wc_koin_documents_fields_hide = {
    display: "none"
  };
  const wc_koin_documents_fields_show = {
    display: "block"
  };

  const setDocumentData = (value, type) => {
    if (value !== personDocument) {
      setPersonDocument(value);
    }

    if (type === 'cpf') {
      setCpf(value);
    } else {
      setCnpj(value);
    }
  }

  const checkPersonData = () => {
    let valid = true;
    const cpf = /^\d{3}\.\d{3}\.\d{3}-\d{2}$/;
    const cnpj = /^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/;

    if (!cnpj.test(personDocument) && !cpf.test(personDocument)) {
      valid = false;
    }

    if (personType !== '1' && personType !== '2') {
      valid = false;
    }

    return valid;
  }

  const sendPersonData = () => {
    if (personDocument === oldDocument) return;
    setOldnDocument(personDocument);

    let data = {
      type: 'document-data',
      fields: {
        personType: '',
        document: ''
      }
    }
    
    if (checkPersonData()) {
      data = {
        type: 'document-data',
        fields: {
          personType: personType,
          document: personDocument
        }
      }
    }

  
    fetch(`${window.location.origin}/index.php/wp-json/wc-pagaleve/checkout-fields`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(response) {
        if (!response.ok) {
          throw new Error('Erro sending request!');
        }
    })
    .catch(function(error) {
        console.error('Erro:', error);
    });
  }

  const handlerDocumentField = (value) => {
    setPersonDocument('');
    setPersontype(value)

    if (value === '1') {
        setShowCpf(true);
        setShowCnpj(false);
        setCnpj('');
    } else {
        setShowCnpj(true);
        setShowCpf(false);
        setCpf('');
    }
  }

  return (
    <div className='wc-pagaleve-block-content wc-pagaleve-person-type'>
        <div>
            <label>{__("Tipo de Pessoa", "wc-pagaleve-official")}</label>
            <select id="wc-pagaleve-billing_persontype" onChange={e => handlerDocumentField(e.target.value)} value={personType} required>
                <option value={1}>{__("Pessoa Física", "wc-pagaleve-official")}</option>
                <option value={2}>{__("Pessoa Jurídica", "wc-pagaleve-official")}</option>
            </select>
            <input type='hidden' id='wc-pagaleve-billing_document' value={personDocument} onChange={sendPersonData()}/>
        </div>
        <div className='wc-pagaleve-documents-fields'>  
            <div style={showCpf ? wc_koin_documents_fields_show : wc_koin_documents_fields_hide}>
                <label>CPF</label>
                <div>
                  <IMaskInput mask={"000.000.000-00"} placeholder={"000.000.000-00"} type="text" id="wc-pagaleve-billing_cpf" value={cpf} required={showCpf} onChange={e => setDocumentData(e.target.value, 'cpf')}/>
                </div>
            </div>
            <div style={showCnpj ? wc_koin_documents_fields_show : wc_koin_documents_fields_hide}>
                <label>CNPJ</label>
                <div>
                  <IMaskInput mask={"00.000.000/0000-00"} placeholder={"00.000.000/0000-00"} type="text" id="wc-pagaleve-billing_cnpj" value={cnpj} required={showCnpj} onChange={e => setDocumentData(e.target.value, 'cnpj')}/>
                </div>
            </div>
        </div>
    </div>
  );
}
