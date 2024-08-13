export default (commentBox, selector) => {
 const userId = selector.value;
 const name = selector.options[selector.selectedIndex].text;
 commentBox.innerHTML = commentBox.innerHTML.replace(/@([^@]*)$/, `$1<span contenteditable="false" class="user-mention bg-teal-50 p-1" id="user_${userId}" data-id="${userId}">
  ${name}</span>&nbsp;`);
}
