import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { ProcessingStatusHelper } from '@/lib/processing-status';
import { type LibraryItem } from '@/types';

interface StatusBadgeProps {
    item: LibraryItem;
    isComplete: boolean;
    isActive: boolean;
    isFailed: boolean;
}

export default function StatusBadge({ item, isComplete, isActive, isFailed }: StatusBadgeProps) {
    const status = ProcessingStatusHelper.from(item.processing_status);

    if (isComplete) {
        return <span className="text-xs text-green-600 dark:text-green-400">{status.getIcon()}</span>;
    }

    if (isActive) {
        return (
            <span className="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400">
                {status.getIcon()}
                {status.getDisplayName()}
            </span>
        );
    }

    if (isFailed) {
        return (
            <Tooltip>
                <TooltipTrigger asChild>
                    <span className="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                        {status.getIcon()}
                        Failed
                    </span>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{item.processing_error || 'Processing failed.'}</p>
                </TooltipContent>
            </Tooltip>
        );
    }

    return null;
}
