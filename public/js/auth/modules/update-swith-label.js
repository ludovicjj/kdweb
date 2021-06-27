/**
 * Update switch value and it's label depending the server response.
 *
 * @param {boolean} is_guard_checking_ip
 */
export default function updateSwitchAndLabel(is_guard_checking_ip) {
    document.querySelector('label[for="check_user_ip_checkbox"]').textContent = is_guard_checking_ip ? "Active" : "Inactive";
    document.querySelector('input[id="check_user_ip_checkbox"]').checked = is_guard_checking_ip;

}
