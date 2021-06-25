const checkbox = document.querySelector('input[id="check_user_ip_checkbox"]');
checkbox.addEventListener('change', toggleCheckingIp);

document
    .querySelector('button[id="add_current_user_ip_to_whitelist_button"]')
    .addEventListener('click', addCurrentUserIpToWhitelist);

/**
 * Enable or disable validation of user's ip address
 */
function toggleCheckingIp()
{
    const label = document.querySelector('label[for="check_user_ip_checkbox"]')
    const url = this.getAttribute('data-url');
    const options = {
        body: JSON.stringify(this.checked),
        method: "POST",
        headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        }
    }
    fetch(url, options)
        .then(response => response.json())
        .then(data => {
            let isGuardCheckingIp;
            ({isGuardCheckingIp} = data)
            label.textContent = isGuardCheckingIp ? "Active" : "Inactive"
        })
        .catch(error => console.error(error))
}

/**
 * Add the current user's IP to the whitelist
 */
function addCurrentUserIpToWhitelist()
{
    const target = document.querySelector('p[id="user-ip-addresses"]');
    const url = this.getAttribute('data-url');
    const options = {
        method: "GET",
        headers: {
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        }
    }
    this.disabled = true;

    fetch(url, options)
        .then(response => response.json())
        .then(data => {
            let user_ip;
            ({user_ip} = data)
            if (target.textContent === "") {
                target.textContent = user_ip;
            } else {
                if (!target.textContent.includes(user_ip)) {
                    target.textContent += ` | ${user_ip}`;
                }
            }
            this.disabled = false;
        })
        .catch(errors => console.error(errors))
}