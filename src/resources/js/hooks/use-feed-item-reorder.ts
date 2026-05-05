import { useState } from 'react';

export function useFeedItemReorder<T extends { sequence: number }>(items: T[], onReorder: (items: T[]) => void) {
    const [draggedIndex, setDraggedIndex] = useState<number | null>(null);

    const handleDragStart = (index: number) => {
        setDraggedIndex(index);
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
    };

    const handleDrop = (e: React.DragEvent, dropIndex: number) => {
        e.preventDefault();
        if (draggedIndex === null) return;

        const draggedItem = items[draggedIndex];
        const newItems = [...items];
        newItems.splice(draggedIndex, 1);
        newItems.splice(dropIndex, 0, draggedItem);

        onReorder(newItems.map((item, i) => ({ ...item, sequence: i })));
        setDraggedIndex(null);
    };

    return { handleDragStart, handleDragOver, handleDrop };
}
