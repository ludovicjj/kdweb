/**
 * Set cursor at th end of an editable element.
 * @param {HTMLElement} element
 */
export default function setCursorAtEnd(element)
{
    const range = document.createRange();
    const selection = document.getSelection();
    range.setStart(element, 1);
    range.collapse(true);
    selection.removeAllRanges();
    selection.addRange(range);
}