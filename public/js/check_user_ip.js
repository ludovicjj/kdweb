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
        headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        }
    }
    console.log(options);
}

/**
 * Add the current user's IP to the whitelist
 */
function addCurrentUserIpToWhitelist()
{

}