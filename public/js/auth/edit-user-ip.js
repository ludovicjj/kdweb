import ConfirmIdentity from "./modules/confirm-identity.js";

new ConfirmIdentity({
    url: document.querySelector('p[id="user_ip_addresses"]').getAttribute("data-url"),
    element_to_listen: document.querySelector('p[id="user_ip_addresses"]'),
    fetch_options: {
        body: null,
        headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "Edit-User-IP": "true"
        },
        method: "POST"
    },
    event_type: "keydown"
})