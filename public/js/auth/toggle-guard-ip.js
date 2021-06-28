import ConfirmIdentity from "./modules/confirm-identity.js";

new ConfirmIdentity({
    url: document.querySelector('input[id="check_user_ip_checkbox"]').getAttribute("data-url"),
    element_to_listen: document.querySelector('input[id="check_user_ip_checkbox"]'),
    fetch_options: {
        body: null,
        headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "Toggle-Guard-Checking-IP": "true"
        },
        method: "POST"
    },
    event_type: "change"
})