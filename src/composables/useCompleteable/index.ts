import { Ref, ref, UnwrapRef } from 'vue';

interface ICompletable {
	setCompleted(isCompleted: boolean, cascade: boolean, doEmit: boolean);
}

/**
 * Composable for adding functionality of something that can be completed. Make sure to add the 'update-completed' event
 * in your `defineEmits(...)` call.
 * @param {Ref<UnwrapRef<unknown[] | null>>} completableChildren List of children which can also be completed.
 * @param emit The const returned by `defineEmits(...)`.
 * @template EE
 * @example
 * ```
 * const children = ref(null);
 * const emit = defineEmits(['update-completed']);
 * const { isCompleted, setCompleted, toggleCompleted } = useCompletable(emit, children);
 * ```
 */
export default function useCompletable(
	emit,
	completableChildren: Ref<UnwrapRef<unknown[] | null>> = ref([]),
): {
	toggleCompleted: (evt?: Event) => void;
	setCompleted: (isCompleted?: boolean) => void;
	isCompleted: Ref<UnwrapRef<boolean>>;
} {
	/**
	 * If this step/instruction/... has been marked as completed.
	 * @type {import('vue').Ref<boolean>}
	 */
	const isCompletedLocal: Ref<UnwrapRef<boolean>> = ref(false);

	/**
	 * Sets the completed state manually.
	 * @param {boolean} isCompleted If true the state is complete, if false state is not completed.
	 * @param {boolean} cascade If true, the value is set on child elements as well.
	 * @param {boolean} shouldEmit If true the updated value is emitted.
	 */
	const setCompleted = (
		isCompleted: boolean = true,
		cascade: boolean = true,
		shouldEmit: boolean = true,
	): void => {
		isCompletedLocal.value = isCompleted;
		if (cascade) {
			if (completableChildren.value) {
				for (const child of completableChildren.value) {
					if (
						typeof (child as ICompletable).setCompleted !==
						'undefined'
					) {
						(child as ICompletable).setCompleted(
							isCompleted,
							cascade,
							false,
						);
					}
				}
			}
		}
		if (shouldEmit) {
			emit('update-completed', isCompletedLocal.value);
		}
	};

	/**
	 * Toggles the completed state and emits 'update-completed' event.
	 * @param {Event} evt The event triggering the change in completed state.
	 */
	const toggleCompleted = (evt: Event): void => {
		evt.stopPropagation();
		setCompleted(!isCompletedLocal.value, true, true);
	};

	return {
		isCompleted: isCompletedLocal,
		setCompleted,
		toggleCompleted,
	};
}
