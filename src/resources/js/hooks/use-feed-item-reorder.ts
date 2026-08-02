import { useRef } from 'react';

export function useFeedItemReorder<T extends { sequence: number }>(items: T[], onReorder: (items: T[]) => void) {
    const draggedIndex = useRef<number | null>(null);

    const handleDragStart = (index: number) => {
        draggedIndex.current = index;
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
    };

    const handleDrop = (e: React.DragEvent, dropIndex: number) => {
        e.preventDefault();
        reorder(dropIndex);
    };

    const reorder = (dropIndex: number) => {
        if (draggedIndex.current === null) return;

        const draggedItem = items[draggedIndex.current];
        const newItems = [...items];
        newItems.splice(draggedIndex.current, 1);
        newItems.splice(dropIndex, 0, draggedItem);

        onReorder(newItems.map((item, i) => ({ ...item, sequence: i })));
        draggedIndex.current = null;
    };

    const handleTouchEnd = (e: React.TouchEvent) => {
        const touch = e.changedTouches[0];
        const target = document.elementFromPoint(touch.clientX, touch.clientY)?.closest<HTMLElement>('[data-feed-item-index]');
        const dropIndex = Number(target?.dataset.feedItemIndex);

        if (Number.isInteger(dropIndex)) {
            reorder(dropIndex);
        } else {
            draggedIndex.current = null;
        }
    };

    return { handleDragStart, handleDragOver, handleDrop, handleTouchEnd };
}
