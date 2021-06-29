import ConfirmIdentity from "./modules/confirm-identity.js";

new ConfirmIdentity({
    url: document.querySelector('button[id="add_user_ip_btn"]').getAttribute("data-url"),
    element_to_listen: document.querySelector('button[id="add_user_ip_btn"]'),
    fetch_options: {
        headers: {
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        method: "GET"
    },
    event_type: "click"
})