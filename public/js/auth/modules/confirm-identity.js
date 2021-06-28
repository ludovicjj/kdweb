import checkIpEntered from "./check-ip-entered.js";
import updateSwitchAndLabel from "./update-swith-label.js";
import updateWhitelistIp from "./update-whitelist-ip.js";

export default class ConfirmIdentity {

    /**
     * Display a modal window to confirm the identity of user by asking to retype his password.
     *
     * @param {string} url
     * @param {HTMLElement} element_to_listen
     * @param {Object} fetch_options
     * @param {string} event_type
     */
    constructor({url, element_to_listen, fetch_options, event_type}) {
        this.modal = document.querySelector('div[id="confirm_password_modal"]');
        this.modal_body = document.querySelector('div[id="confirm_password_modal-body"]');
        this.display_modal_button = document.querySelector('button[data-target="#confirm_password_modal"]');
        this.close_modal_button = document.querySelector('button[id="confirm_password_modal-button-close"]');

        this.url = url;
        this.element_to_listen = element_to_listen;
        this.fetch_options = fetch_options;
        this.event_type = event_type;

        this.init();
    }

    init() {
        this.element_to_listen.addEventListener(this.event_type, (event) => this.callServerToDisplayConfirmModal(event))
    }

    async callServerToDisplayConfirmModal(event)
    {
        // toggle
        if (this.url === "/user/account/profile/toggle-checking-ip") {
            this.fetch_options.body = document.querySelector('input[id="check_user_ip_checkbox"]').checked;
        }

        // edit
        if (this.url === "/user/account/profile/edit-user-ip") {
            const user_ip_entered_array = checkIpEntered(event);

            if (!user_ip_entered_array) {
                return;
            }

            this.fetch_options.body = user_ip_entered_array;
        }

        // fetch
        try {
            const response = await fetch(this.url, this.fetch_options);
            const {is_password_confirmed} = await response.json();
            !is_password_confirmed ? this.displayConfirmModal() : null;
        } catch (error) {
            console.error(error);
        }
    }

    displayConfirmModal()
    {
        this.createConfirmModalForm();
        this.display_modal_button.click();
        this.modal.addEventListener("shown.bs.modal", () => {
            this.modal_form_input_password.focus();
        });

        this.modal_form.addEventListener('submit', (event) => this.confirmPassword(event));

        this.modal.addEventListener("hidden.bs.modal", () => {
            const checkbox_label = document.querySelector('label[for="check_user_ip_checkbox"]').textContent;
            document.querySelector('input[id="check_user_ip_checkbox"]').checked = checkbox_label === 'Active';
        });
    }

    async confirmPassword(event)
    {
        event.preventDefault();
        const password = this.modal_form_input_password.value;
        const options = {
            body: JSON.stringify({password}),
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "Confirm-Identity-With-Password": "true"
            },
            method: "POST"
        }

        try {
            const response = await fetch(this.url, options);
            const {is_guard_checking_ip, is_password_confirmed, login_url, status_code, user_ip} = await response.json();

            this.resetPasswordInput();

            // Redirect to login if when user entered invalid password 3 times
            if (status_code === 302) {
                window.location.href = login_url;
            }

            if (is_password_confirmed) {
                this.passwordIsValid(this.url, is_guard_checking_ip, user_ip);
            } else {
                this.passwordIsInvalid();
            }
        } catch (error) {
            console.error(error);
        }
    }

    createConfirmModalForm()
    {
        if (document.querySelector('form[id="confirm-modal-form"]')) {
            document.querySelector('form[id="confirm-modal-form"]').remove();
        }

        const form_element = document.createElement('form');
        form_element.id = "confirm-modal-form";
        form_element.method = "POST";

        const fieldset_element = document.createElement('fieldset');

        const label_element = document.createElement('label');
        label_element.htmlFor = "confirm-modal-password-input";
        label_element.textContent = "Confirmer votre mot de passe";

        const input_element = document.createElement('input');
        input_element.type = "password";
        input_element.class = "form-control";
        input_element.name = "confirm-modal-password";
        input_element.id = "confirm-modal-password-input";

        const button_element = document.createElement('button');
        button_element.type = "submit";
        button_element.className = "btn btn-success mt-3";
        button_element.textContent = "Confirmer";

        const paragraph_element = document.createElement('p');
        paragraph_element.id = "invalid-password-entered";
        paragraph_element.className = "text-danger d-none mt-3";
        paragraph_element.textContent = "Le mot de passe est invalide."

        fieldset_element.append(label_element, input_element, paragraph_element);
        form_element.append(fieldset_element, button_element);
        this.modal_body.append(form_element);

        this.modal_form = form_element;
        this.modal_form_input_password = input_element;
        this.modal_form_error = paragraph_element;
    }

    resetPasswordInput()
    {
        this.modal_form_input_password.value = "";
        this.modal_form_input_password.focus();
    }

    passwordIsValid(url, is_guard_checking_ip, user_ip)
    {
        switch (url) {
            case "/user/account/profile/add-current-ip": updateWhitelistIp(user_ip);
            break;
            case "/user/account/profile/edit-user-ip": updateWhitelistIp(user_ip);
            break;
            case "/user/account/profile/toggle-checking-ip": updateSwitchAndLabel(is_guard_checking_ip);
            break;
        }
        this.close_modal_button.click();
    }

    passwordIsInvalid()
    {
        this.modal_form_error.classList.remove("d-none");
        setTimeout(() => this.modal_form_error.classList.add("d-none"), 3000);
    }
}