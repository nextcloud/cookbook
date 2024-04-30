// eslint-disable-next-line import/prefer-default-export
export function findParentByClass(
	element: HTMLElement | null,
	className: string,
) {
	if (element === null) return null;
	if (element && element === document.body) {
		return null; // Return null if no parent with the specified class is found
	}
	if (element.classList.contains(className)) {
		return element;
	}

	return findParentByClass(element.parentNode as HTMLElement, className);
}
