import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { type LibraryItem } from '@/types';
import { ArrowDownToLine, Pencil, RefreshCw, Trash2 } from 'lucide-react';

interface ItemActionsProps {
    item: LibraryItem;
    isComplete: boolean;
    isFailed: boolean;
    onEdit: (item: LibraryItem) => void;
    onDelete: (id: number) => void;
    onRetry: (id: number) => void;
    onRedownload: (id: number) => void;
}

export default function ItemActions({ item, isComplete, isFailed, onEdit, onDelete, onRetry, onRedownload }: ItemActionsProps) {
    if (isComplete) {
        return (
            <>
                {item.media_file?.source_url && (
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => onRedownload(item.id)}>
                                <ArrowDownToLine className="h-4 w-4" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Redownload from source</TooltipContent>
                    </Tooltip>
                )}
                <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => onEdit(item)}>
                    <Pencil className="h-4 w-4" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8 text-destructive hover:text-destructive"
                    onClick={() => onDelete(item.id)}
                >
                    <Trash2 className="h-4 w-4" />
                </Button>
            </>
        );
    }

    if (isFailed) {
        return (
            <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => onRetry(item.id)}>
                <RefreshCw className="h-4 w-4" />
            </Button>
        );
    }

    return null;
}
