(() => {
	const selectEnvironment = {
		init() {
			this.selectEnvironmentField = document.querySelector('#wc_pagaleve_settings_environment')

			if (!this.selectEnvironmentField) {
				return
			}

			this.settingsUserProduction = document.querySelector('#wc_pagaleve_settings_user_production')
			this.settingsPasswordProduction = document.querySelector('#wc_pagaleve_settings_password_production')
			this.settingsPasswordProduction.type = 'password';

			this.settingsPasswordProduction.addEventListener('focusin',() => {
				this.settingsPasswordProduction.type = 'text'
			});

			this.settingsPasswordProduction.addEventListener('focusout',() => {
				this.settingsPasswordProduction.type = 'password'
			});

			this.settingsUserSandbox = document.querySelector('#wc_pagaleve_settings_user_sandbox')
			this.settingsPasswordSandbox = document.querySelector('#wc_pagaleve_settings_password_sandbox')
			this.settingsPasswordSandbox.type = 'password'

			this.settingsPasswordSandbox.addEventListener('focusin',() => {
				this.settingsPasswordSandbox.type = 'text'
			});

			this.settingsPasswordSandbox.addEventListener('focusout',() => {
				this.settingsPasswordSandbox.type = 'password'
			});

			this.HandleEnvFieldsVisibility(this.selectEnvironmentField.value)

			this.selectEnvironmentField.addEventListener('click',() => {
				this.HandleEnvFieldsVisibility(this.selectEnvironmentField.value)
			});
		},

		HandleEnvFieldsVisibility(value) {
			const settingsUserProduction = this.settingsUserProduction.closest('tr')
			const settingsPasswordProduction = this.settingsPasswordProduction.closest('tr')

			const settingsUserSandbox = this.settingsUserSandbox.closest('tr')
			const settingsPasswordSandbox = this.settingsPasswordSandbox.closest('tr')

			if (value === 'sandbox') {
				settingsUserProduction.style.display = 'none'
				settingsPasswordProduction.style.display = 'none'

				settingsUserSandbox.style.display = 'revert'
				settingsPasswordSandbox.style.display = 'revert'
				return;
			}

			settingsUserProduction.style.display = 'revert'
			settingsPasswordProduction.style.display = 'revert'

			settingsUserSandbox.style.display = 'none'
			settingsPasswordSandbox.style.display = 'none'
		  },
	}

    document.addEventListener('DOMContentLoaded', () => {
        selectEnvironment.init();
    })
})();
