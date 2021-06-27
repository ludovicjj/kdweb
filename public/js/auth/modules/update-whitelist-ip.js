/**
 * Update whitelist of user IP.
 * @param {string|null} user_ip
 */
export default function updateWhitelistIp(user_ip) {
    const whitelist_ip = document.querySelector('p[id="user-ip-addresses"]');

    if (whitelist_ip.textContent === "") {
        whitelist_ip.textContent = user_ip;
    }

    if (!whitelist_ip.textContent.includes(user_ip)) {
        whitelist_ip.textContent += ` | ${user_ip}`;
    }
}