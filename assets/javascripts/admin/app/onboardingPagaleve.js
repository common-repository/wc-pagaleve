(() => {
    const onboardingPagaleve = {
        init() {
            if(this.checkUrlParams()) {
                this.createPopup();
            }
        },

        createPopup() {
            const background = document.createElement('div');
            background.setAttribute('id', 'wc-pagaleve-onboarding');
            background.classList.add('wc-pagaleve-onboarding-back');

            const div = document.createElement('div');
            div.classList.add('wc-pagaleve-onboarding');
            background.appendChild(div);

            const imgDiv = document.createElement('div');
            imgDiv.classList.add('wc-pagaleve-onboarding-img');
            div.appendChild(imgDiv);

            const logo = document.createElement('img');
            logo.src = `${window.location.origin}/wp-content/plugins/wc-pagaleve/assets/images/icons/pagaleve.png`;
            imgDiv.appendChild(logo);

            const textDiv = document.createElement('div');
            textDiv.classList.add('wc-pagaleve-onboarding-text');
            div.appendChild(textDiv);

            const label = document.createElement('label');
            label.innerText = "Seja bem-vindo!\nDeseja criar uma conta PagaLeve?";
            textDiv.appendChild(label);

            const buttonDiv = document.createElement('div');
            buttonDiv.classList.add('wc-pagaleve-onboarding-buttons');
            div.appendChild(buttonDiv);

            const cancelButton = document.createElement('input');
            cancelButton.setAttribute('id', 'wc-pagaleve-onboarding-no');
            cancelButton.setAttribute('type', 'button');
            cancelButton.value = 'Não';
            buttonDiv.appendChild(cancelButton);

            const confirmButton = document.createElement('input');
            confirmButton.setAttribute('id', 'wc-pagaleve-onboarding-yes');
            confirmButton.setAttribute('type', 'button');
            confirmButton.value = 'Sim';
            buttonDiv.appendChild(confirmButton);

            const body = document.querySelector('body');

            body.appendChild(background);

            this.closePopupButton();
            this.confirmPopupButton();
        },

        closePopupButton() {
            const button = document.querySelector("#wc-pagaleve-onboarding-no");
            if (button) {
                button.addEventListener('click', () => {
                    this.removePopup();
                });
            }
        },

        confirmPopupButton() {
            const button = document.querySelector("#wc-pagaleve-onboarding-yes");
            if (button) {
                button.addEventListener('click', () => {
                    this.showLoaderSpinner();
                    this.createOnboardingUrl();
                });
            }
        },

        createOnboardingUrl() {
            const body = new FormData();
            body.append("action", "create_onboarding_url");
        
        
            fetch(`${window.location.origin}/wp-admin/admin-ajax.php`, {
              method: "POST",
              body: body,
            })
            .then((response) => response.json())
            .then((data) => {
        
              console.log(data);
              if (data.content.onboarding_url) {
                window.location = data.content.onboarding_url;
              } else {
                const message = document.querySelector("#wc-pagaleve-onboarding-span");
                message.innerHTML = "Ops! Algo deu errado.";

                setTimeout(() => {
                    const popup = document.querySelector("#wc-pagaleve-onboarding");
                    popup.remove();
                }, 2000);
              }
            });
        },

        removePopup() {
            const popup = document.querySelector("#wc-pagaleve-onboarding");
            if (popup) {
                popup.remove();
            }
        },

        showLoaderSpinner() {
            this.removePopupContent();

            const popup = document.querySelector("#wc-pagaleve-onboarding");

            const div = document.createElement('div');
            div.classList.add('wc-pagaleve-onboarding-loading');

            const spanDiv = document.createElement('div');
            spanDiv.classList.add('wc-pagaleve-onboarding-loading-span');
            div.appendChild(spanDiv);

            const span = document.createElement('span');
            span.setAttribute('id','wc-pagaleve-onboarding-span');
            span.innerText = "Aguarde!\nVocê redirecionado em breve.";
            spanDiv.appendChild(span);

            const spinnerDiv = document.createElement('div');
            spinnerDiv.classList.add('wc-pagaleve-onboarding-loader');
            spinnerDiv.setAttribute('id','wc-pagaleve-onboarding-loader');
            div.appendChild(spinnerDiv);

            const spinner = document.createElement('div');
            spinner.classList.add('wc-pagaleve-onboarding-spinner');
            spinnerDiv.appendChild(spinner);

            popup.appendChild(div);

        },

        removePopupContent() {
            const content = document.querySelector("#wc-pagaleve-onboarding > div");
            if (content) {
                content.remove();
            }
        },

        checkUrlParams() {
            const params = (new URL(window.location.href)).searchParams;
            const onboarding = params.get('wc-pagaleve-onboarding');

            if (onboarding && onboarding == 'true') {
                return true;
            }

            return false;
        }
    }

	onboardingPagaleve.init();
})();