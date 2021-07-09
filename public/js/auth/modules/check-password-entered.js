/**
 *
 * @param {Event} event
 */
export default function checkPasswordEntered(event)
{
    const {currentTarget} = event;
    const first_password_input = currentTarget.children[0].lastElementChild;
    const second_password_input = currentTarget.children[1].lastElementChild;
    const first_password_entered = first_password_input.value;
    const second_password_entered = second_password_input.value;

    const password_regex = new RegExp(first_password_input.getAttribute('pattern'));

    first_password_input.value = "";
    second_password_input.value = "";

    if (
        !first_password_entered ||
        !second_password_entered ||
        first_password_entered !== second_password_entered ||
        !password_regex.test(first_password_entered)
    ) {
        alert('Veuillez saisir deux mot de pass identiques et valides.');
        first_password_input.focus();
        throw new Error('Veuillez saisir deux mot de pass identiques et valides.');
    }

    return first_password_entered;
}